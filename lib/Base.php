<?php

namespace dobie;

/**
 * Abstract base superclass with configurability.
 */
abstract class Base {
    /**
     * Stores configuration values. May contain initial default values.
     * See constructor.
     *
     * @see __construct()
     * @var array
     */
    protected $config = array();

    /**
     * Stores extended configuration values (suitable for extending parent's configuration).
     * May contain initial default values. See constructor.
     *
     * @see __construct()
     * @var array
     */
    protected $extendConfig = array();

    /**
     * Stores configuration keys whose values should be merged upon initialization.
     * See constructor.
     *
     * @see __construct()
     * @var array
     */
    protected $mergeConfig = array();

    /**
     * Initializes class configuration (in `$config`). This method iterates over the
     * keys from `$config` (and `$extendConfig`) to automatically assign configuration
     * settings (and merge if configuration key found in `$mergeConfig`).
     *
     * @see $config
     * @see $extendConfig
     * @see $mergeConfig
     * @param array $config The configuration options which override the default values.
     */
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

    /**
     * Write string to output. Calls `io` with `$key` set to `'output'`.
     *
     * @see io()
     * @param string $output Message to write.
     * @param array $options Configuration options to use, accepts one option:
     *              - `'nl'` _integer_: number of new lines to add after `$output`,
     *                defaults to 1.
     * @return mixed The return value depends on configuration.
     */
    protected function out($output = null, $options = array('nl' => 1)) {
	return $this->io('output', $output, $options);
    }

    /**
     * Write string to error. Calls `io` with `$key` set to `'error'`.
     *
     * @see io()
     * @param string $output Message to write.
     * @param array $options Configuration options to use, accepts one option:
     *              - `'nl'` _integer_: number of new lines to add after `$output`,
     *                defaults to 1.
     * @return mixed The return value depends on configuration.
     */
    protected function error($output = null, $options = array('nl' => 1)) {
	return $this->io('error', $output, $options);
    }

    /**
     * Output and error handling. This methods check for configuration value
     * with key `$key` and if the value is
     * - callable: calls it with `$output` and `$options` as arguments, and
     *   returns its result,
     * - resource: fwrites `$output` (with newlines appended) to it returning
     *   its result [if `$output` is not null, returns 0 (skipping fwrite)
     *   if it is],
     * returns false otherwise.
     *
     * @param string $key The configuration key to check.
     * @param string $output Message to write.
     * @param array $options Configuration options to use, accepts one option:
     *              - `'nl'` _integer_: number of new lines to add after `$output`,
     *                defaults to 1.
     * @return mixed The return value depends on configuration.
     */
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