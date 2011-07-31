<?php

namespace dobie\tests;

spl_autoload_register(function ($class) {
    $class = ltrim($class, "\\");
    if (strpos($class, __NAMESPACE__ . "\\") === 0) {
	$file = str_replace("\\", DIRECTORY_SEPARATOR, substr($class, strlen(__NAMESPACE__ . "\\"))) . '.php';
	require $file;
    }
});
require_once dirname(__DIR__) . '/lib/Autoloader.php';
\dobie\Autoloader::register();
define('DOBIE_FIXTURE_PATH', __DIR__ . '/fixtures');
define('DOBIE_RESOURCE_PATH', __DIR__ . '/tmp');

?>
