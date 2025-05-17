<?php

$wgContentTranslationEnableSuggestions;
$wgContentTranslationTargetNamespace;
$wgContentTranslationContentImportForSectionTranslation;
$wgContentTranslationDomainCodeMapping;
$wgContentTranslationCXServerAuth;
$wgContentTranslationEnableAnonSectionTranslation;
$wgContentTranslationExcludedNamespaces;
$wgContentTranslationCluster;
$wgContentTranslationTranslateInTarget;
$wgContentTranslationDevMode;
$wgRecommendToolAPIURL;
$wgDraftMaxAge;
$AutomaticTranslationLanguageSearcherEntrypointEnabledLanguages;
$wgContentTranslationEnableUnifiedDashboard;
$wgSectionTranslationTargetLanguages;

$wgContentTranslationVersion;
$wgContentTranslationDatabase;



// already in LocalSettings.php
$wgContentTranslationAsBetaFeature = false;
$wgContentTranslationUnmodifiedMTThresholdForPublish = 100;
$wgContentTranslationEnableSectionTranslation = false;
$wgContentTranslationEnableMT = true;
$wgContentTranslationPublishRequirements = [
    "userGroups" => [
        "*"
    ]
];

$wgContentTranslationCampaigns = [
    "Main" => true,
    "COVID" => true,
    "Hearing" => true,
    "Occupational Health" => true,
    "Essential Medicines" => true,
    "Women Health" => true,
    "Epilepsy" => true
];


$wgContentTranslationSiteTemplates["cx"] = "https://cxserver.wikimedia.org/v1";

$wgContentTranslationSiteTemplates = [
    "view" => "//$1.wikipedia.org/wiki/$2",
    "action" => "//$1.wikipedia.org/w/index.php?title=$2",
    "api" => "//$1.wikipedia.org/w/api.php",
    "cx" => "https://cxserver.wikimedia.org/v1",
    "cookieDomain" => null,
    "unlinkedrestbase" => "//$1.wikipedia.org/api/rest_v1"
];

$wgContentTranslationSiteTemplates_mdwiki = [
    "view" => "//mdwiki.org/wiki/$2",
    "action" => "//mdwiki.org/w/index.php?title=$2",
    "api" => "//medwiki.toolforge.org/mdwiki_api.php",
    "cx" => "https://cxserver.wikimedia.org/v1",
    "cookieDomain" => null,
    "unlinkedrestbase" => "//mdwiki.org/api/rest_v1"
];


$wgResourceModules["mw.cx.init"]["scripts"][] = "mw.cx.TranslationMdwiki.js";

$wgResourceModules['mw.cx.SiteMapper']['packageFiles'][1]['config']['SiteTemplates_mdwiki'] = 'ContentTranslationSiteTemplates_mdwiki';


/*
"mw.cx.SiteMapper": {
			"packageFiles": [
				"base/mw.cx.SiteMapper.js",
				{
					"name": "config.json",
					"config": {
						"DomainCodeMapping": "ContentTranslationDomainCodeMapping",
						"SiteTemplates": "ContentTranslationSiteTemplates",
						"SiteTemplates_mdwiki": "ContentTranslationSiteTemplates_mdwiki",
						"TranslateInTarget": "ContentTranslationTranslateInTarget"
					}
				}
			]
*/
// $wgResourceModules["mw.cx.SiteMapper"]["packageFiles"]


// not yet in LocalSettings.php
