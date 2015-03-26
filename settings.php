<?php
require_once dirname(__FILE__).'/config.core.php';
include_once MODX_CORE_PATH . 'model/modx/modx.class.php';

$modx= new modX();
$modx->initialize('mgr');
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget(XPDO_CLI_MODE ? 'ECHO' : 'HTML');


// Settings
$settings = array(
	'cultureKey' => 'ru'
	,'fe_editor_lang' => 'ru'
	,'publish_default' => 1
//	,'tvs_below_content' => 1
	,'upload_maxsize' => '10485760'
	,'cache_resource' => 0
	, 'topmenu_show_descriptions' => 0
	, 'locale' => 'ru_RU.utf-8'
	, 'manager_lang_attribute' => 'ru'
	, 'manager_language' => 'ru'
	
	//url
	, 'automatic_alias' => 1
	, 'friendly_urls' => 1
	, 'global_duplicate_uri_check' => 1
	,'link_tag_scheme' => 'full'

	/*after packages install */

//	, 'tiny.base_url' => '/'
//	, 'tiny.path_options' => 'rootrelative'
//	, 'friendly_alias_translit' => 'russian'

);
foreach ($settings as $k => $v) {
	$opt = $modx->getObject('modSystemSetting', array('key' => $k));
	$opt->set('value', $v);
	$opt->save();
}


// Resources
$resources = array(
	array('pagetitle' => 'sitemap',
	  		'template' => 0,
	  		'published' => 1,
	  		'hidemenu' => 1,
	  		'alias' => 'sitemap' 
	  		,'content_type' => 2, 
	  		'richtext' => 0, 
	  		'content' =>'[[!pdoSitemap? &checkPermissions=`list`]]'
	  		)
	, array('pagetitle' => 'robots',
			'template' => 0,
			'published' => 1,
			'hidemenu' => 1,
			'alias' => 'robots',
			'content_type' => 3,
			'richtext' => 0, 
			'content' => 'User-agent: * Disallow: /manager/ Disallow: /assets/components/ Allow: /assets/uploads/ Disallow: /core/ Disallow: /connectors/ Disallow: /index.php Disallow: /search Disallow: /profile/ Disallow: *? Host: [[++site_url]] Sitemap: [[++site_url]]sitemap.xml' 
			)
);
foreach ($resources as $attr) {
	$response = $modx->runProcessor('resource/create', $attr);
}

$modx->cacheManager->refresh();