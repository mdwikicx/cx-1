<?php

$wgContentTranslationEnableSuggestions;
$wgContentTranslationTargetNamespace;
$wgContentTranslationContentImportForSectionTranslation;
$wgContentTranslationDomainCodeMapping;
$wgContentTranslationCXServerAuth;
$wgContentTranslationEnableAnonSectionTranslation;
$wgContentTranslationExcludedNamespaces;
$wgContentTranslationCluster;
$wgContentTranslationAsBetaFeature;
$wgContentTranslationTranslateInTarget;
$wgContentTranslationDevMode;
$wgRecommendToolAPIURL;
$wgDraftMaxAge;
$AutomaticTranslationLanguageSearcherEntrypointEnabledLanguages;
$wgContentTranslationEnableUnifiedDashboard;
$wgSectionTranslationTargetLanguages;

$wgContentTranslationUnmodifiedMTThresholdForPublish;
$wgContentTranslationVersion;
$wgContentTranslationCampaigns;
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


// not yet in LocalSettings.php

$wgContentTranslationSiteTemplates = [
    "view" => "//$1.wikipedia.org/wiki/$2",
    "action" => "//$1.wikipedia.org/w/index.php?title=$2",
    "api" => "//$1.wikipedia.org/w/api.php",
    "cx" => "https://cxserver.wikimedia.org/v1",
    "cookieDomain" => null,
    "unlinkedrestbase" => "//$1.wikipedia.org/api/rest_v1"
];

$ContentTranslationSiteTemplates_mdwiki = [
    "view" => "//mdwiki.org/wiki/$2",
    "action" => "//mdwiki.org/w/index.php?title=$2",
    "api" => "//medwiki.toolforge.org/mdwiki_api.php",
    "cx" => "https://cxserver.wikimedia.org/v1",
    "cookieDomain" => null,
    "unlinkedrestbase" => "//mdwiki.org/api/rest_v1"
];
