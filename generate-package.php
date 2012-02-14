#!/usr/bin/env php
<?php
error_reporting(E_ALL & ~(E_STRICT|E_NOTICE|E_DEPRECATED));

ini_set('date.timezone', 'Europe/Berlin');

require_once 'PEAR/PackageFileManager2.php';
PEAR::setErrorHandling(PEAR_ERROR_DIE);

$api_version     = '0.1.0';
$api_state       = 'alpha';

$release_version = '0.1.0';
$release_state   = 'alpha';
$release_notes   = "Initial release!\n";

$packageName = 'StatsD';
$summary     = 'php-statsd';
$description = "php-statsd is a PHP client for Etsy's StatsD!";

$package = new PEAR_PackageFileManager2();

$package->setOptions(
    array(
        'filelistgenerator' => 'file',
        'simpleoutput'      => true,
        'baseinstalldir'    => '/',
        'packagedirectory'  => './',
        'dir_roles'         => array(
            'library' => 'php',
            'tests'   => 'test',
            'docs'    => 'doc',
        ),
        'exceptions'        => array(
            'README.md' => 'doc',
        ),
        'ignore'            => array(
            '.git*',
            'generate-package.php',
            '*.tgz',
        )
    )
);

$package->setPackage($packageName);
$package->setSummary($summary);
$package->setDescription($description);
$package->setChannel('easybib.github.com/pear');
$package->setPackageType('php');
$package->setLicense(
    'BSD',
    'http://www.opensource.org/licenses/bsd-license.php'
);

$package->setNotes($release_notes);
$package->setReleaseVersion($release_version);
$package->setReleaseStability($release_state);
$package->setAPIVersion($api_version);
$package->setAPIStability($api_state);

$package->addMaintainer(
    'lead',
    'till',
    'Till Klampaeckel',
    'till@lagged.biz'
);

/**
 * Generate the list of files in {@link $GLOBALS['files']}
 *
 * @param string $path
 *
 * @return void
 */
function readDirectory($path) {
    foreach (glob($path . '/*') as $file) {
        if (!is_dir($file)) {
            $GLOBALS['files'][] = $file;
        } else {
            readDirectory($file);
        }
    }
}

$files = array();
readDirectory(__DIR__ . '/library');

/**
 * @desc Strip this from the filename for 'addInstallAs'
 */
$base = __DIR__ . '/';

foreach ($files as $file) {

    $file2 = str_replace($base, '', $file);

    $package->addReplacement(
       $file2,
       'package-info',
       '@package_version@',
       'version'
    );
    $file2 = str_replace($base, '', $file);
    $package->addInstallAs($file2, str_replace('library/', '', $file2));
}

$package->setPhpDep('5.2.0');

$package->setPearInstallerDep('1.4.0a7');
$package->generateContents();

if (   isset($_GET['make'])
    || (isset($_SERVER['argv']) && @$_SERVER['argv'][1] == 'make')
) {
    $package->writePackageFile();
} else {
    $package->debugPackageFile();
}
