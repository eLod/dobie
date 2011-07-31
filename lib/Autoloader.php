<?php

namespace dobie;

class Autoloader {
    static public function load($class) {
	if ($path = static::path($class)) {
	    require $path;
	}
    }

    static public function path($class, $absolute = false) {
	$class = ltrim($class, "\\");
	if (strpos($class, __NAMESPACE__ . "\\") === 0) {
	    $file = str_replace("\\", DIRECTORY_SEPARATOR, substr($class, strlen(__NAMESPACE__ . "\\"))) . '.php';
	    return ($absolute ? __DIR__ . DIRECTORY_SEPARATOR : '') . $file;
	} else {
	    return false;
	}
    }

    static public function register() {
	spl_autoload_register(array(__CLASS__, 'load'));
    }
}

?>
