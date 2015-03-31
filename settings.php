<?php
require_once dirname(__FILE__).'/config.core.php';
include_once MODX_CORE_PATH . 'model/modx/modx.class.php';

$modx= new modX();
$modx->initialize('mgr');
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget(XPDO_CLI_MODE ? 'ECHO' : 'HTML');


/*== Settings ==*/

$settingsAdd = array(
	// Включаем pdoParser из pdoTools
	'parser_class' => array(
		'namespace' => 'pdotools',
		'area' => 'pdotools_main',
		'xtype' => 'textfield',
		'value' => 'pdoParser',
		'key' => 'parser_class'
	),
	'parser_class_path' => array(
		'namespace' => 'pdotools',
		'area' => 'pdotools_main',
		'xtype' => 'textfield',
		'value' => '{core_path}components/pdotools/model/pdotools/',
		'key' => 'parser_class_path'
	)
);

$settingsUpdate = array(

	/* Ace */
	'ace.show_invisibles' => 1,
	'ace.word_wrap' => 1,
	/* /Ace */
	/* Core */
	// Панель управления
	'richtext_default' => 0,

	// Дружественные URL
	'container_suffix' => '',
	'friendly_urls_strict' => 1,
	'friendly_urls' => 1,
	'use_alias_path' => 1,
	'friendly_alias_translit' => 'russian',

	// Шлюз
	'request_method_strict' => 1
	/* /Core */
);

foreach ($settingsAdd as $k => $v) {
	$sa = $modx->newObject('modSystemSetting');
	$sa->fromArray($v,'',true,true);
	$sa->save();
}

foreach ($settingsUpdate as $k => $v) {
	$su = $modx->getObject('modSystemSetting', array('key' => $k));
	$su->set('value', $v);
	$su->save();
}


/*== Content Type ==*/

$ct = $modx->getObject('modContentType',array('name'=>'HTML'));
$ct->set('file_extensions', '');
$ct->save();


/*== Resources ==*/

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
