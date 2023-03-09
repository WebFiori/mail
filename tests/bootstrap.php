<?php

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);
$testsDirName = 'tests';
$rootDir = substr(__DIR__, 0, strlen(__DIR__) - strlen($testsDirName));
$DS = DIRECTORY_SEPARATOR;
$rootDirTrimmed = trim($rootDir,'/\\');
//echo 'Include Path: \''.get_include_path().'\''."\n";

if (explode($DS, $rootDirTrimmed)[0] == 'home') {
    //linux.
    $rootDir = $DS.$rootDirTrimmed.$DS;
} else {
    $rootDir = $rootDirTrimmed.$DS;
}
define('ROOT_DIR', $rootDir);
define('DS', DIRECTORY_SEPARATOR);
//echo 'Root Directory: \''.$rootDir.'\'.'."\n";
$jsonLibPath = $rootDir.'vendor'.DS.'webfiori'.DS.'jsonx'.DS.'webfiori'.DS.'json';
require_once $jsonLibPath.DS.'JsonI.php';
require_once $jsonLibPath.DS.'Json.php';
require_once $jsonLibPath.DS.'JsonConverter.php';
require_once $jsonLibPath.DS.'CaseConverter.php';
require_once $jsonLibPath.DS.'JsonTypes.php';
require_once $jsonLibPath.DS.'Property.php';

$fileLibPath = $rootDir.'vendor'.DS.'webfiori'.DS.'file'.DS.'webfiori'.DS.'file'.DS;

require_once $fileLibPath.'File.php';
require_once $fileLibPath.'MIME.php';
require_once $fileLibPath.'FileUploader.php';
require_once $fileLibPath.'UploadedFile.php';
require_once $fileLibPath.'UploaderConst.php';
require_once $fileLibPath.'MIME.php';
require_once $fileLibPath.'exceptions'.DS.'FileException.php';

$collectionsLibPath = $rootDir.'vendor'.$DS.'webfiori'.$DS.'collections'.$DS.'webfiori'.$DS.'collections'.$DS;
require_once $collectionsLibPath.'Node.php';
require_once $collectionsLibPath.'AbstractCollection.php';
require_once $collectionsLibPath.'LinkedList.php';
require_once $collectionsLibPath.'Stack.php';
require_once $collectionsLibPath.'Queue.php';
require_once $collectionsLibPath.'Comparable.php';

$uiLibPath = $rootDir.'vendor'.DS.'webfiori'.DS.'ui'.DS.'webfiori'.DS.'ui'.DS;
require_once $uiLibPath.'HTMLNode.php';
require_once $uiLibPath.'HTMLDoc.php';
require_once $uiLibPath.'HeadNode.php';

$libDir = $rootDir.'webfiori'.DS.'email'.DS;

require_once $libDir.'SMTPAccount.php';
require_once $libDir.'SMTPServer.php';
require_once $libDir.'EmailMessage.php';
require_once $libDir.'exceptions'.DS.'SMTPException.php';

