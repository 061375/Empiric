<?php 
/** 
 *  
 * 
 * 
 * */

$GLOBALS['verbose'] = false;
$GLOBALS['testing'] = false;

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once('vendor/autoload.php');
require_once('src/core.php');

\Empiric\Core::autoload();
\Empiric\Core::run();