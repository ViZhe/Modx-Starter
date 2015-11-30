<?php

// provider id => array of packages
$listPackagesToInstall = array(
    1 => array( // standart modx provider
        'sdStore',
        'AjaxManager',
        'translit',
        'FormIt',
    ),
    2 => array( // other providers(SimpleDream)
        'pdoTools',
        'Ace',
    )
);

define('MODX_API_MODE', true);

/* this can be used to disable caching in MODX absolutely */
$modx_cache_disabled= false;

header("Content-type: text/plain");

/* include custom core config and define core path */
include(dirname(__FILE__) . '/config.core.php');
if (!defined('MODX_CORE_PATH')) define('MODX_CORE_PATH', dirname(__FILE__) . '/core/');

/* include the modX class */
if (!@include_once (MODX_CORE_PATH . "model/modx/modx.class.php")) {
    $errorMessage = 'Site temporarily unavailable';
    @include(MODX_CORE_PATH . 'error/unavailable.include.php');
    header('HTTP/1.1 503 Service Unavailable');
    echo "<html><title>Error 503: Site temporarily unavailable</title><body><h1>Error 503</h1><p>{$errorMessage}</p></body></html>";
    exit();
}

/* Create an instance of the modX class */
$modx= new modX();
if (!is_object($modx) || !($modx instanceof modX)) {
    @ob_end_flush();
    $errorMessage = '<a href="setup/">MODX not installed. Install now?</a>';
    @include(MODX_CORE_PATH . 'error/unavailable.include.php');
    header('HTTP/1.1 503 Service Unavailable');
    echo "<html><title>Error 503: Site temporarily unavailable</title><body><h1>Error 503</h1><p>{$errorMessage}</p></body></html>";
    exit();
}

$modx->initialize('mgr');
$modx->addPackage('modx.transport',dirname(__FILE__).'/core/model/');

foreach ($listPackagesToInstall as $providerId => $installPackages) {
    //Get Provider
    $provider = $modx->getObject('transport.modTransportProvider', $providerId);
    if (empty($provider)){
        echo 'Could not find provider'."\n";
        return;
    }

    $provider->getClient();
    $modx->getVersionData();
    $productVersion = $modx->version['code_name'].'-'.$modx->version['full_version'];

    foreach($installPackages as $packageName) {
        $response = $provider->request('package','GET',array(
            //  'supports' => $productVersion,
            'query' => $packageName
        ));

        if(!empty($response)) {
            $foundPackages = simplexml_load_string($response->response);
            if($total = $foundPackages['total']>0) {
                foreach($foundPackages as $foundPackage) {
                    if($foundPackage->name == $packageName) {
                        /* define version */
                        $sig = explode('-',$foundPackage->signature);
                        $versionSignature = explode('.',$sig[1]);

                        //download file
                        file_put_contents(
                        $modx->getOption('core_path').'packages/'.$foundPackage->signature.'.transport.zip', file_get_contents($foundPackage->location));

                        /* add in the package as an object so it can be upgraded */
                        /** @var modTransportPackage $package */
                        $package = $modx->newObject('transport.modTransportPackage');
                        $package->set('signature',$foundPackage->signature);
                        $package->fromArray(array(
                            'created' => date('Y-m-d h:i:s'),
                            'updated' => null,
                            'state' => 1,
                            'workspace' => 1,
                            'provider' => $providerId,
                            'source' => $foundPackage->signature.'.transport.zip',
                            'package_name' => $sig[0],
                            'version_major' => $versionSignature[0],
                            'version_minor' => !empty($versionSignature[1]) ? $versionSignature[1] : 0,
                            'version_patch' => !empty($versionSignature[2]) ? $versionSignature[2] : 0,
                        ));

                        if (!empty($sig[2])) {
                            $r = preg_split('/([0-9]+)/',$sig[2],-1,PREG_SPLIT_DELIM_CAPTURE);

                            if (is_array($r) && !empty($r)) {
                                $package->set('release',$r[0]);
                                $package->set('release_index',(isset($r[1]) ? $r[1] : '0'));
                            } else {
                                $package->set('release',$sig[2]);
                            }
                        }

                        $success = $package->save();

                        if($success) {
                            $package->install();
                            echo 'Package '.$foundPackage->name.' installed.'."\n";
                        }
                        else {
                            echo 'Could not save package '.$foundPackage->name."\n";
                        }

                        break;
                    }
                }
            }
            else {
                echo 'Package '.$packageName.' not found'."\n";
            }
        }
    }
}

exit;
