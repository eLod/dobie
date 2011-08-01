<?php

namespace dobie;

abstract class Base {
    protected $config = array();
    protected $extendConfig = array();
    protected $mergeConfig = array();

    public function __construct(array $config = array()) {
	$defaults = (array) $this->extendConfig + (array) $this->config;
	$this->config = array();
	foreach ($defaults as $name => $default) {
	    if (isset($config[$name])) {
		if (in_array($name, $this->mergeConfig)) {
		    $this->config[$name] = $config[$name] + $default;
		} else {
		    $this->config[$name] = $config[$name];
		}
	    } else {
		$this->config[$name] = $default;
	    }
	}
    }

    protected function out($output = null, $options = array('nl' => 1)) {
	return $this->io('output', $output, $options);
    }

    protected function error($output = null, $options = array('nl' => 1)) {
	return $this->io('error', $output, $options);
    }

    protected function io($key, $output = null, $options = array('nl' => 1)) {
	if (is_callable($this->config[$key])) {
	    return call_user_func($this->config[$key], $output, $options);
	} else if (is_resource($this->config[$key])) {
	    if (is_null($output)) {
		return 0;
	    } else {
		$nl = isset($options['nl']) && is_int($options['nl']) ? $options['nl'] : 1;
		return fwrite($this->config[$key], $output . str_repeat(PHP_EOL, $nl));
	    }
	} else {
	    return false;
	}
    }
}

?>