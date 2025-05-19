<?php
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

$wgContentTranslationSiteTemplates_mdwiki = [
    "view" => "//mdwiki.org/wiki/$2",
    "action" => "//mdwiki.org/w/index.php?title=$2",
    "api" => "//medwiki.toolforge.org/mdwiki_api.php",
    "cx" => "https://cxserver.wikimedia.org/v1",
    "cookieDomain" => null,
    "restbase" => "//mdwiki.org/api/rest_v1"
];

$wgContentTranslationSiteTemplates["cx"] = "https://cxserver.wikimedia.org/v1";

$wgResourceModules["mw.cx.init"]["scripts"][] = "mw.cx.TranslationMdwiki.js";
$wgResourceModules["mw.cx.SiteMapper"]["packageFiles"][1]["config"]["SiteTemplates_mdwiki"] = "ContentTranslationSiteTemplates_mdwiki";

// $wgContentTranslationContentImportForSectionTranslation = ;
// $wgContentTranslationEnableUnifiedDashboard = ;
// $wgContentTranslationTranslateInTarget = true;



// not yet

$wgContentTranslationEnableSuggestions;
$wgContentTranslationTargetNamespace;
$wgContentTranslationDomainCodeMapping;
$wgContentTranslationCXServerAuth;
$wgContentTranslationEnableAnonSectionTranslation;
$wgContentTranslationExcludedNamespaces;
$wgContentTranslationCluster;
$wgContentTranslationDevMode;
$wgRecommendToolAPIURL;
$wgDraftMaxAge;
$AutomaticTranslationLanguageSearcherEntrypointEnabledLanguages;
$wgSectionTranslationTargetLanguages;

$wgContentTranslationVersion;
$wgContentTranslationDatabase;


/*
$wgAllowCrossOrigin
$wgAllowUserCss
$wgAllowUserCssPrefs
$wgAllowUserJs
$wgArticlePath
$wgAuthenticationTokenVersion
$wgCacheDirectory
$wgContentTranslationAsBetaFeature
$wgContentTranslationCXServerAuth
$wgContentTranslationCampaigns
$wgContentTranslationCluster
$wgContentTranslationContentImportForSectionTranslation
$wgContentTranslationDatabase
$wgContentTranslationDevMode
$wgContentTranslationDomainCodeMapping
$wgContentTranslationEnableAnonSectionTranslation
$wgContentTranslationEnableMT
$wgContentTranslationEnableSectionTranslation
$wgContentTranslationEnableSuggestions
$wgContentTranslationEnableUnifiedDashboard
$wgContentTranslationExcludedNamespaces
$wgContentTranslationPublishRequirements
$wgContentTranslationSiteTemplates
$wgContentTranslationSiteTemplates_mdwiki
$wgContentTranslationTargetNamespace
$wgContentTranslationTranslateInTarget
$wgContentTranslationUnmodifiedMTThresholdForPublish
$wgContentTranslationVersion
$wgCookiePrefix
$wgCrossSiteAJAXdomains
$wgDBTableOptions
$wgDBname
$wgDBpassword
$wgDBprefix
$wgDBserver
$wgDBssl
$wgDBtype
$wgDBuser
$wgDefaultSkin
$wgDefaultUserOptions
$wgDiff3
$wgDisableOutputCompression
$wgDraftMaxAge
$wgEmailAuthentication
$wgEmergencyContact
$wgEnableEmail
$wgEnableJavaScriptTest
$wgEnableUploads
$wgEnableUserEmail
$wgEnotifUserTalk
$wgEnotifWatchlist
$wgExtensionAssetsPath
$wgExtraNamespaces
$wgGroupPermissions
$wgImageMagickConvertCommand
$wgImportSources
$wgInterwikiCentralDB
$wgLanguageCode
$wgLocaltimezone
$wgLogos
$wgMainCacheType
$wgMemCachedServers
$wgMetaNamespace
$wgNamespaceProtection
$wgPasswordSender
$wgPingback
$wgPluggableAuth_Config
$wgPluggableAuth_EnableAutoLogin
$wgPluggableAuth_EnableLocalLogin
$wgRecommendToolAPIURL
$wgRememberMe
$wgResourceBasePath
$wgResourceModules
$wgRightsIcon
$wgRightsPage
$wgRightsText
$wgRightsUrl
$wgScribuntoDefaultEngine
$wgScribuntoEngineConf
$wgScribuntoUseCodeEditor
$wgScribuntoUseGeSHi
$wgScriptPath
$wgSecretKey
$wgSectionTranslationTargetLanguages
$wgServer
$wgSharedDB
$wgSharedTables
$wgShowDBErrorBacktrace
$wgShowExceptionDetails
$wgShowSQLErrors
$wgSitename
$wgSuspiciousIpExpiry
$wgUnlinkedWikibaseBaseQueryEndpoint
$wgUnlinkedWikibaseBaseUrl
$wgUnlinkedWikibaseEntityTTL
$wgUnlinkedWikibaseSitelinkSkippedLangs
$wgUnlinkedWikibaseSitelinkSuffix
$wgUnlinkedWikibaseStatementsParserFunc
$wgUpgradeKey
$wgUseImageMagick
$wgUseInstantCommons
$wgUsePathInfo
*/

$wgExtraLanguageNames['mdwiki'] = 'mdwiki';
