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
