<?php
require_once dirname(__FILE__).'/config.core.php';
include_once MODX_CORE_PATH . 'model/modx/modx.class.php';

$modx= new modX();
$modx->initialize('mgr');
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget(XPDO_CLI_MODE ? 'ECHO' : 'HTML');


// Settings
$settings = array(

	'richtext_default' => 0,

	//url
	'friendly_urls' => 1,
	'use_alias_path' => 1,
	'friendly_alias_translit' => 'russian'

);
foreach ($settings as $k => $v) {
	$opt = $modx->getObject('modSystemSetting', array('key' => $k));
	$opt->set('value', $v);
	$opt->save();
}


// Resources
$resources = array(
	array(
		'pagetitle' => 'sitemap',
		'template' => 0,
		'published' => 1,
		'hidemenu' => 1,
		'alias' => 'sitemap',
		'content_type' => 2,
		'content' =>'[[!pdoSitemap? &checkPermissions=`list`]]'
	),
	array(
		'pagetitle' => 'robots',
		'template' => 0,
		'published' => 1,
		'hidemenu' => 1,
		'alias' => 'robots',
		'content_type' => 3,
		'content' => 'User-agent: *
Disallow: /m/
Disallow: /core/
Disallow: /connectors/
Disallow: /assets/components/
Disallow: /index.php
Disallow: /search
Disallow: *?
Host: [[++site_url]]
Sitemap: [[++site_url]]sitemap.xml'
	)
);
foreach ($resources as $attr) {
	$response = $modx->runProcessor('resource/create', $attr);
}

$modx->cacheManager->refresh();
