<?php

namespace dobie;

/**
 * Autoloads dobie classes.
 */
class Autoloader {
    /**
     * Register `load` as an autoloader.
     *
     * @see load()
     * @return void
     */
    static public function register() {
	spl_autoload_register(array(__CLASS__, 'load'));
    }

    /**
     * Load a class.
     *
     * @param string $class Fully namespaced classname to load.
     * @return void
     */
    static public function load($class) {
	if ($path = static::path($class)) {
	    require $path;
	}
    }

    /**
     * Get filesystem path for a class (for the same namespace).
     *
     * @param string $class Fully namespaced classname to load.
     * @param boolean $absolute If `true` the returned path will be absolute.
     * @return string|boolean String filesystem path if namespace matches, `false` otherwise.
     */
    static public function path($class, $absolute = false) {
	$class = ltrim($class, "\\");
	if (strpos($class, __NAMESPACE__ . "\\") === 0) {
	    $class = substr($class, strlen(__NAMESPACE__ . "\\"));
	    $file = str_replace("\\", DIRECTORY_SEPARATOR, $class) . '.php';
	    return ($absolute ? __DIR__ . DIRECTORY_SEPARATOR : '') . $file;
	} else {
	    return false;
	}
    }
}

?>