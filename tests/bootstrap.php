<?php

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

$testsDirName = 'tests';
$rootDir = substr(__DIR__, 0, strlen(__DIR__) - strlen($testsDirName));
$DS = DIRECTORY_SEPARATOR;
$rootDirTrimmed = trim($rootDir,'/\\');

if (explode($DS, $rootDirTrimmed)[0] == 'home') {
    //linux.
    $rootDir = $DS.$rootDirTrimmed.$DS;
} else {
    $rootDir = $rootDirTrimmed.$DS;
}
define('ROOT_DIR', $rootDir);
define('DS', DIRECTORY_SEPARATOR);
define('EMAIL_STORAGE_DIR', $rootDir.DS.'mailStorage');
require_once __DIR__.$DS.'..'.$DS.'vendor'.$DS.'autoload.php';


