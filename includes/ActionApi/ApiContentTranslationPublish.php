<?php

/**
 * Saving a wiki page created using ContentTranslation.
 * The following special things happen when the page is created:
 * - HTML from the translation editor's contenteditable is converted to wiki syntax using Parsoid.
 * - A change tag is added. CX2 uses an additional tag.
 * - The edit summary shows a link to the revision from which the translation was made.
 * - Optionally, a template is added if the article appears to have a lot of machine translation.
 * - Categories are hidden in <nowiki> if the page is published to the user namespace.
 * - Information about the translated page is saved to the central ContentTranslation database.
 * - When relevant, values of MediaWiki CAPTCHA can be sent.
 * - When relevant, Echo notifications about publishing milestones will be sent.
 * This borrows heavily from ApiVisualEditorEdit.
 *
 * @copyright See AUTHORS.txt
 * @license GPL-2.0-or-later
 */

namespace ContentTranslation\ActionApi;

use ApiBase;
use ApiMain;
use ChangeTags;
use ContentTranslation\Notification;
use ContentTranslation\ParsoidClient;
use ContentTranslation\ParsoidClientFactory;
use ContentTranslation\Service\TranslationTargetUrlCreator;
use ContentTranslation\SiteMapper;
use ContentTranslation\Store\TranslationStore;
use ContentTranslation\Translation;
use ContentTranslation\Translator;
use Deflate;
use Exception;
use ExtensionRegistry;
use IBufferingStatsdDataFactory;
use MediaWiki\Deferred\DeferredUpdates;
use MediaWiki\Languages\LanguageFactory;
use MediaWiki\Languages\LanguageNameUtils;
use MediaWiki\Request\DerivativeRequest;
use MediaWiki\Title\Title;
use Wikimedia\ParamValidator\ParamValidator;
use Wikimedia\ParamValidator\TypeDef\IntegerDef;

function post_to_target($params)
{
	// $url = 'https://mdwiki.toolforge.org/Translation_Dashboard/publish/main.php';
	$url = 'https://mdwiki.toolforge.org/publish/main.php';
	$ch = curl_init();

	// if ($ch === false) {
	// 	throw new \RuntimeException('curl_init() failed');
	// }

	$usr_agent = "WikiProjectMed Translation Dashboard/1.0 (https://mdwiki.toolforge.org/; tools.mdwiki@toolforge.org)";

	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	// wikipedia_result: {"response":"Requests must have a user agent"}
	curl_setopt($ch, CURLOPT_USERAGENT, $usr_agent);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
	curl_setopt($ch, CURLOPT_TIMEOUT, 15);

	$response = curl_exec($ch);

	$curlErr   = curl_error($ch);
	$status    = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
	curl_close($ch);

	if ($response === false) {
		return ['error' => $curlErr ?: 'Unknown cURL error', 'response' => $response];
	}

	if ($status !== 200) {
		return ['error' => "Unexpected HTTP status $status", 'response' => $response];
	}

	$js = json_decode($response, true);

	if ($js === null) {
		return ['error' => 'Invalid JSON', 'response' => $response];
	}

	return $js ?? ['response' => $response];
}

class ApiContentTranslationPublish extends ApiBase
{

	protected ParsoidClientFactory $parsoidClientFactory;
	protected ?Translation $translation;
	private LanguageFactory $languageFactory;
	private IBufferingStatsdDataFactory $statsdDataFactory;
	private LanguageNameUtils $languageNameUtils;
	private TranslationStore $translationStore;
	private TranslationTargetUrlCreator $targetUrlCreator;

	public function __construct(
		ApiMain $main,
		$name,
		ParsoidClientFactory $parsoidClientFactory,
		LanguageFactory $languageFactory,
		IBufferingStatsdDataFactory $statsdDataFactory,
		LanguageNameUtils $languageNameUtils,
		TranslationStore $translationStore,
		TranslationTargetUrlCreator $targetUrlCreator
	) {
		parent::__construct($main, $name);
		$this->parsoidClientFactory = $parsoidClientFactory;
		$this->languageFactory = $languageFactory;
		$this->statsdDataFactory = $statsdDataFactory;
		$this->languageNameUtils = $languageNameUtils;
		$this->translationStore = $translationStore;
		$this->targetUrlCreator = $targetUrlCreator;
		$this->published_to = "local";
	}

	protected function getParsoidClient(): ParsoidClient
	{
		return $this->parsoidClientFactory->createParsoidClient();
	}
	protected function publishToMdwiki($title, $wikitext, $params, $sourceRevisionId, $summary, $user_name)
	{
		$t_Params = [
			'title' => $title->getPrefixedDBkey(),
			'revid' => $sourceRevisionId,
			'text' => $wikitext,
			'user' => $user_name,
			'summary' => $summary,
			'target' => $params['to'],
			'campaign' => $params['campaign'] ?? '',
			'sourcetitle' => $params['sourcetitle'],
		];

		// wpCaptchaId, wpCaptchaWord
		if (isset($params['wpCaptchaId'])) {
			$t_Params['wpCaptchaId'] = $params['wpCaptchaId'];
			$t_Params['wpCaptchaWord'] = $params['wpCaptchaWord'];
		}

		$wikipedia_result = post_to_target($t_Params);
		return $wikipedia_result;
	}
	protected function saveWikitext($title, $wikitext, $params)
	{
		$categories = $this->getCategories($params);
		if (count($categories)) {
			$categoryText = "\n[[" . implode("]]\n[[", $categories) . ']]';
			// If publishing to User namespace, wrap categories in <nowiki>
			// to avoid blocks by abuse filter. See T88007.
			if ($title->inNamespace(NS_USER)) {
				$categoryText = "\n<nowiki>$categoryText</nowiki>";
			}
			$wikitext .= $categoryText;
		}

		$wikitext = trim($wikitext);

		$sourceRevisionId = $this->translation->translation['sourceRevisionId'];

		$sourceLink = '[[:' . SiteMapper::getDomainCode($params['from'])
			. ':Special:Redirect/revision/'
			. $sourceRevisionId
			. '|' . $params['sourcetitle'] . ']] to:' . $params['to'] . " #mdwikicx";

		$summary = $this->msg(
			'cx-publish-summary',
			$sourceLink
		)->inContentLanguage()->text();

		$user_name = $this->getUser()->getName();

		if (isset($params['user']) && $params['user'] != '') {
			$user_name = $params['user'];
		}

		$wikipedia_result = false;

		if ($params['from'] === "mdwiki") { #$wikipedia_result

			$wikipedia_result = $this->publishToMdwiki($title, $wikitext, $params, $sourceRevisionId, $summary, $user_name);
			$this->published_to = "mdwiki";
			// return $Result;

			$wikitext = "<pre>$wikitext</pre>";
			$wikitext .= "\n{{tr|" . $params['to'] . '|' . $params['sourcetitle'] . '|' . $user_name . '}}';
		}

		$apiParams = [
			'action' => 'edit',
			'title' => ($params['from'] === 'mdwiki') ? $params['to'] . "/" . $params['sourcetitle'] : $title->getPrefixedDBkey(),
			'text' => $wikitext,
			'summary' => $summary,
		];

		$request = $this->getRequest();

		$api = new ApiMain(
			new DerivativeRequest(
				$request,
				$apiParams + $request->getValues(),
				true // was posted
			),
			true // enable write
		);

		$api->execute();
		$result = $api->getResult()->getResultData();
		// if ( $params['from'] === "mdwiki") return $wikipedia_result;

		return [
			'local_result' => $result,
			'wikipedia_result' => $wikipedia_result,
		];
	}

	protected function getTags(array $params)
	{
		$tags = $params['publishtags'];
		$tags[] = 'contenttranslation';
		if ($params['cxversion'] === 2) {
			$tags[] = 'contenttranslation-v2'; // Tag for CX2: contenttranslation-v2
		}
		// Remove any tags that are not registered.
		return array_intersect($tags, ChangeTags::listSoftwareActivatedTags());
	}

	protected function getCategories(array $params)
	{
		$trackingCategoryMsg = 'cx-unreviewed-translation-category';
		$categories = [];

		if ($params['categories']) {
			$categories = explode('|', $params['categories']);
		}

		$trackingCategoryKey = array_search($trackingCategoryMsg, $categories);
		if ($trackingCategoryKey !== false) {
			$cat = $this->msg($trackingCategoryMsg)->inContentLanguage()->plain();
			$containerCategory = Title::makeTitleSafe(NS_CATEGORY, $cat);
			if ($cat !== '-' && $containerCategory) {
				// Title without namespace prefix
				$categories[$trackingCategoryKey] = $containerCategory->getText();
				// Record using Graphite that the published translation is marked for review
				$this->statsdDataFactory->increment('cx.publish.highmt.' . $params['to']);
			} else {
				wfDebug(__METHOD__ . ": [[MediaWiki:$trackingCategoryMsg]] is not a valid title!\n");
				unset($categories[$trackingCategoryKey]);
			}
		}

		// Validate and normalize all categories.
		foreach ($categories as $index => $category) {
			$category = $this->removeApiCategoryNamespacePrefix($category, $params['to']);
			// Also remove the namespace in English, if any. May be from T264490
			$category = $this->removeApiCategoryNamespacePrefix($category, 'en');
			$title = Title::makeTitleSafe(NS_CATEGORY, $category);
			if ($title !== null) {
				$categories[$index] = $title->getPrefixedText();
			} else {
				unset($categories[$index]);
			}
		}

		// Guard against duplicates, if any.
		$categories = array_unique($categories);

		return $categories;
	}

	/**
	 * Removes category namespace prefix for a given category received
	 * from API, if existing, otherwise returns category as is
	 * @param string $category
	 * @param string $targetLanguage
	 * @return string
	 */
	private function removeApiCategoryNamespacePrefix($category, $targetLanguage)
	{
		$targetLanguage = $this->languageFactory->getLanguage($targetLanguage);
		$targetLanguageCategoryPrefix = $targetLanguage->getNsText(NS_CATEGORY) . ":";
		if (substr($category, 0, strlen($targetLanguageCategoryPrefix)) === $targetLanguageCategoryPrefix) {
			return substr($category, strlen($targetLanguageCategoryPrefix));
		}
		return $category;
	}

	public function execute()
	{
		$params = $this->extractRequestParams();

		$block = $this->getUser()->getBlock();
		if ($block && $block->isSitewide()) {
			$this->dieBlocked($block);
		}

		if (!$this->languageNameUtils->isKnownLanguageTag($params['from'])) {
			$this->dieWithError('apierror-cx-invalidsourcelanguage', 'invalidsourcelanguage');
		}

		if (!$this->languageNameUtils->isKnownLanguageTag($params['to'])) {
			$this->dieWithError('apierror-cx-invalidtargetlanguage', 'invalidtargetlanguage');
		}

		if (trim($params['html']) === '') {
			$this->dieWithError(['apierror-paramempty', 'html'], 'invalidhtml');
		}

		$this->publish();
	}

	public function publish()
	{
		$params = $this->extractRequestParams();
		$user = $this->getUser();

		$targetTitle = Title::newFromText($params['title']);
		if (!$targetTitle) {
			$this->dieWithError(['apierror-invalidtitle', wfEscapeWikiText($params['title'])]);
		}

		['sourcetitle' => $sourceTitle, 'from' => $sourceLanguage, 'to' => $targetLanguage] = $params;
		$this->translation = $this->translationStore->findTranslationByUser(
			$user,
			$sourceTitle,
			$sourceLanguage,
			$targetLanguage
		);

		if ($this->translation === null) {
			$this->dieWithError('apierror-cx-translationnotfound', 'translationnotfound');
		}

		$html = Deflate::inflate($params['html']);
		if (!$html->isGood()) {
			$this->dieWithError('deflate-invaliddeflate', 'invaliddeflate');
		}
		try {
			$wikitext = $this->getParsoidClient()->convertHtmlToWikitext(
				// @phan-suppress-next-line PhanTypeMismatchArgumentNullable T240141
				$targetTitle,
				$html->getValue()
			)['body'];
		} catch (Exception $e) {
			$this->dieWithError(
				['apierror-cx-docserverexception', wfEscapeWikiText($e->getMessage())],
				'docserver'
			);
		}

		$saveresult_all = $this->saveWikitext($targetTitle, $wikitext, $params);

		$saveresult = $saveresult_all['local_result'] ?? [];

		if ($params['from'] === "mdwiki") {
			$saveresult = $saveresult_all['wikipedia_result'] ?? [];
		};

		$save_edit = $saveresult['edit'] ?? [];
		$editStatus = $saveresult['edit']['result'] ?? [];

		if ($editStatus === 'success' || $editStatus === 'Success') {
			if (isset($save_edit['newrevid'])) {
				$tags = $this->getTags($params);
				// Add the tags post-send, after RC row insertion
				$revId = intval($save_edit['newrevid']);
				DeferredUpdates::addCallableUpdate(static function () use ($revId, $tags) {
					ChangeTags::addTags($tags, null, $revId, null);
				});
			}
			$title2 = $targetTitle->getPrefixedDBkey();

			if ($params['from'] === "mdwiki") {
				$title2 = $params['to'] . "/" . $params['sourcetitle'];
			};

			$targetURL = $this->targetUrlCreator->createTargetUrl($title2, $params['to']);
			$targeturl_wiki = SiteMapper::getPageURL($params['to'], $targetTitle->getPrefixedDBkey());
			$result = [
				'result' => 'success',
				'targeturl' => $targetURL,
				'targeturl_wiki' => $targeturl_wiki,
				'published_to' => $this->published_to
			];

			// if (is_array($saveresult) && isset($saveresult['LinkToWikidata'])) $result['LinkToWikidata'] = $saveresult['LinkToWikidata'];

			$this->translation->translation['status'] = TranslationStore::TRANSLATION_STATUS_PUBLISHED;
			$this->translation->translation['targetURL'] = $targetURL;

			if (isset($save_edit['newrevid'])) {
				$result['newrevid'] = intval($save_edit['newrevid']);
				$this->translation->translation['targetRevisionId'] = $result['newrevid'];
			}

			// Save the translation history.
			$this->translationStore->saveTranslation($this->translation, $user);

			// Notify user about milestones
			$this->notifyTranslator();
		} else {
			$result = [
				'result' => 'error',
				'edit' => $save_edit ?? []
			];
		}

		// $result['save_result_all'] = $saveresult_all;
		$result['wikipedia_result'] = $saveresult_all['wikipedia_result'] ?? [];
		$result['local_result'] = $saveresult_all['local_result'] ?? [];

		// if warnings in $result['local_result'] del it
		unset($result['wikipedia_result']['warnings']);
		unset($result['local_result']['warnings']);

		$this->getResult()->addValue(null, $this->getModuleName(), $result);
	}

	/**
	 * Notify user about milestones.
	 */
	public function notifyTranslator()
	{
		$params = $this->extractRequestParams();

		// Check if Echo is available. If not, skip.
		if (!ExtensionRegistry::getInstance()->isLoaded('Echo')) {
			return;
		}

		$user = $this->getUser();
		$translator = new Translator($user);
		$translationCount = $translator->getTranslationsCount();

		switch ($translationCount) {
			case 1:
				Notification::firstTranslation($user);
				break;
			case 2:
				Notification::suggestionsAvailable($user, $params['sourcetitle']);
				break;
			case 10:
				Notification::tenthTranslation($user);
				break;
			case 100:
				Notification::hundredthTranslation($user);
				break;
		}
	}

	public function getAllowedParams()
	{
		return [
			'title' => [
				ParamValidator::PARAM_REQUIRED => true,
			],
			'html' => [
				ParamValidator::PARAM_REQUIRED => true,
			],
			'from' => [
				ParamValidator::PARAM_REQUIRED => true,
			],
			'to' => [
				ParamValidator::PARAM_REQUIRED => true,
			],
			'sourcetitle' => [
				ParamValidator::PARAM_REQUIRED => true,
			],
			'categories' => null,
			'publishtags' => [
				ParamValidator::PARAM_ISMULTI => true,
			],
			/** @todo These should be renamed to something all-lowercase and lacking a "wp" prefix */
			'campaign' => null,
			'wpCaptchaId' => null,
			'wpCaptchaWord' => null,
			'cxversion' => [
				ParamValidator::PARAM_TYPE => 'integer',
				ParamValidator::PARAM_REQUIRED => true,
				ApiBase::PARAM_RANGE_ENFORCE => true,
				IntegerDef::PARAM_MIN => 1,
				IntegerDef::PARAM_MAX => 2,
			],
		];
	}

	public function needsToken()
	{
		return 'csrf';
	}

	public function isWriteMode()
	{
		return true;
	}

	public function isInternal()
	{
		return true;
	}
}
