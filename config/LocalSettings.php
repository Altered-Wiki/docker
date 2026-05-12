<?php
# altered.wiki — MediaWiki LocalSettings.php

# --- Core -------------------------------------------------------------------

$wgSitename   = 'altered.wiki';
$wgMetaNamespace = 'Altered';

$wgServer      = getenv( 'MW_SERVER' ) ?: 'http://localhost:3000';
$wgScriptPath  = '';
$wgArticlePath = '/$1';

$wgLogos   = [ 'svg' => '/logo.svg', '1x' => '/logo.svg' ];
$wgFavicon = '/logo.svg';

$wgSecretKey  = getenv( 'MW_SECRET_KEY' ) ?: '';
$wgUpgradeKey = getenv( 'MW_UPGRADE_KEY' ) ?: '';

# --- Database ---------------------------------------------------------------

$wgDBtype     = 'mysql';
$wgDBserver   = getenv( 'MEDIAWIKI_DB_HOST' ) ?: 'mariadb';
$wgDBname     = getenv( 'MEDIAWIKI_DB_NAME' ) ?: 'mediawiki';
$wgDBuser     = getenv( 'MEDIAWIKI_DB_USER' ) ?: 'mediawiki';
$wgDBpassword = getenv( 'MEDIAWIKI_DB_PASSWORD' ) ?: '';
$wgDBprefix   = '';

# --- Cache (Redis) ----------------------------------------------------------

$wgObjectCaches['redis'] = [
	'class'   => 'RedisBagOStuff',
	'servers' => [ 'redis:6379' ],
];
$wgMainCacheType    = 'redis';
$wgSessionCacheType = 'redis';
$wgMessageCacheType = 'redis';

# --- Skin -------------------------------------------------------------------

wfLoadSkin( 'Citizen' );
$wgDefaultSkin = 'citizen';

$wgCitizenEnableCollapsibleSections = true;
$wgCitizenThemeDefault = 'auto'; // respects OS dark/light preference

# --- Bundled extensions (enabled, need no install) --------------------------

wfLoadExtension( 'ParserFunctions' );
$wgPFEnableStringFunctions = true;

wfLoadExtension( 'WikiEditor' );
wfLoadExtension( 'CodeEditor' );
wfLoadExtension( 'TemplateData' );

wfLoadExtension( 'Scribunto' );
$wgScribuntoDefaultEngine = 'luasandbox';

wfLoadExtension( 'VisualEditor' );
$wgVisualEditorAvailableNamespaces = [ NS_MAIN => true, NS_TALK => true ];
$wgDefaultUserOptions['visualeditor-enable'] = 1;
# Parsoid is bundled in MW 1.41+ — no separate container needed.
# Point it at localhost so it doesn't try to reach $wgServer externally from inside the container.
$wgParserEnableLegacyMediaDOM = false;
$wgVirtualRestConfig['modules']['parsoid'] = [
	'url' => 'http://localhost/rest.php',
	'domain' => 'localhost',
	'prefix' => 'localhost',
];

wfLoadExtension( 'Cite' );
wfLoadExtension( 'Math' );
$wgMathDefaultMode = 'mathml';
wfLoadExtension( 'Echo' );
wfLoadExtension( 'Thanks' );
wfLoadExtension( 'AbuseFilter' );
wfLoadExtension( 'TitleBlacklist' );

# --- ConfirmEdit + Altcha ---------------------------------------------------

wfLoadExtension( 'ConfirmEdit' );
wfLoadExtension( 'AltchaCaptcha' );

$wgCaptchaClass      = 'AltchaCaptcha';
$wgAltchaHmacKey     = getenv( 'ALTCHA_HMAC_KEY' ) ?: '';
$wgAltchaComplexityMin = 40000;
$wgAltchaComplexityMax = 200000;

# Trigger CAPTCHA on account creation and external link addition
$wgCaptchaTriggers['edit']          = false;
$wgCaptchaTriggers['create']        = false;
$wgCaptchaTriggers['createtalk']    = false;
$wgCaptchaTriggers['addurl']        = true;
$wgCaptchaTriggers['createaccount'] = true;
$wgCaptchaTriggers['badlogin']      = true;

# --- Semantic MediaWiki -----------------------------------------------------

wfLoadExtension( 'SemanticMediaWiki' );
enableSemantics( 'altered.wiki' );

wfLoadExtension( 'SemanticResultFormats' );
$srfgFormats[] = 'timeline';
$srfgFormats[] = 'datatables';
$srfgFormats[] = 'filtered';

wfLoadExtension( 'PageForms' );
wfLoadExtension( 'SemanticDrilldown' );

# --- Maps -------------------------------------------------------------------

wfLoadExtension( 'Maps' );
# Uses Leaflet + OpenStreetMap by default — no API key required

# --- Search (CirrusSearch + Elasticsearch) ----------------------------------

wfLoadExtension( 'Elastica' );
wfLoadExtension( 'CirrusSearch' );

$wgSearchType = 'CirrusSearch';
$wgCirrusSearchServers = [ 'elasticsearch' ];
$wgCirrusSearchUseCompletionSuggester = 'yes'; // powers Citizen's instant search

# --- Domain extensions ------------------------------------------------------

wfLoadExtension( 'PDFEmbed' );
wfLoadExtension( 'DynamicPageList4' );

# --- Community --------------------------------------------------------------

# DPL4 settings ($wgDplSettings was deprecated in 4.0.5)
$wgDPLmaxResultCount = 500;
$wgDPLallowUnlimitedResults = false;

# --- Uploads ----------------------------------------------------------------

$wgEnableUploads  = true;
$wgUploadPath     = '/images';
$wgUploadDirectory = '/var/www/html/images';

$wgFileExtensions = array_merge( $wgFileExtensions, [
	'svg', 'webp', 'pdf',
] );

# --- Namespaces -------------------------------------------------------------

# Custom namespaces for structured content
define( 'NS_SUBSTANCE',       3000 );
define( 'NS_SUBSTANCE_TALK',  3001 );
define( 'NS_RESEARCH',        3002 );
define( 'NS_RESEARCH_TALK',   3003 );
define( 'NS_EXPERIENCE',      3004 );
define( 'NS_EXPERIENCE_TALK', 3005 );

$wgExtraNamespaces[NS_SUBSTANCE]       = 'Substance';
$wgExtraNamespaces[NS_SUBSTANCE_TALK]  = 'Substance_talk';
$wgExtraNamespaces[NS_RESEARCH]        = 'Research';
$wgExtraNamespaces[NS_RESEARCH_TALK]   = 'Research_talk';
$wgExtraNamespaces[NS_EXPERIENCE]      = 'Experience';
$wgExtraNamespaces[NS_EXPERIENCE_TALK] = 'Experience_talk';

# SMW should index these namespaces
$smwgNamespacesWithSemanticLinks[NS_SUBSTANCE] = true;
$smwgNamespacesWithSemanticLinks[NS_RESEARCH]  = true;

# Include custom namespaces in default search profile and treat as content
$wgNamespacesToBeSearchedDefault[NS_SUBSTANCE]  = true;
$wgNamespacesToBeSearchedDefault[NS_RESEARCH]   = true;
$wgNamespacesToBeSearchedDefault[NS_EXPERIENCE] = true;

$wgContentNamespaces[] = NS_SUBSTANCE;
$wgContentNamespaces[] = NS_RESEARCH;
$wgContentNamespaces[] = NS_EXPERIENCE;

# --- Rights & permissions ---------------------------------------------------

# Public read, account required to edit
$wgGroupPermissions['*']['read']          = true;
$wgGroupPermissions['*']['edit']          = false;
$wgGroupPermissions['*']['createaccount'] = true;
$wgGroupPermissions['user']['edit']       = true;
$wgGroupPermissions['user']['createpage'] = true;

# --- Footer -----------------------------------------------------------------

# Order and content of footer places links (disclaimer, privacy, then our Legal Notice)
$wgFooterLinks['places'] = [ 'disclaimer', 'privacy' ];

$wgHooks['SkinAddFooterLinks'][] = static function ( Skin $skin, string $key, array &$footerlinks ) {
	if ( $key === 'places' ) {
		$legalUrl = htmlspecialchars( \MediaWiki\Title\Title::newFromText( 'Legal Notice' )->getLocalURL(), ENT_QUOTES );
		$footerlinks['legal-notice'] = "<a href=\"$legalUrl\">Legal Notice</a>";
	}
};

# --- License ----------------------------------------------------------------

$wgRightsText = 'Creative Commons Attribution-ShareAlike 4.0 International';
$wgRightsUrl  = 'https://creativecommons.org/licenses/by-sa/4.0/';
$wgRightsIcon = "$wgServer/cc-by-sa.png";

# --- Misc -------------------------------------------------------------------

$wgAllowExternalImages = false;
$wgAllowImageTag       = false;

$wgRawHtml = false; # never enable on a public wiki

$wgShowExceptionDetails = getenv( 'MW_DEBUG' ) === 'true';

$wgEnableEmail          = true;
$wgEnableUserEmail      = true;
$wgEmailAuthentication  = true;
$wgEmailConfirmToEdit   = getenv( 'MW_EMAIL_CONFIRM_TO_EDIT' ) !== 'false';

$wgSMTP = [
	'host'      => getenv( 'SMTP_HOST' ) ?: 'localhost',
	'port'      => (int)( getenv( 'SMTP_PORT' ) ?: 587 ),
	'auth'      => true,
	'username'  => getenv( 'SMTP_USER' ) ?: '',
	'password'  => getenv( 'SMTP_PASSWORD' ) ?: '',
];

$wgPasswordSender = getenv( 'SMTP_FROM' ) ?: 'wiki@altered.wiki';
