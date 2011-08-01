<?php

namespace dobie\tests\mocks;

class Autoloader extends \dobie\Autoloader {
    public static $loaded = array();

    static public function load($class) {
	static::$loaded[] = $class;
    }
}

?>