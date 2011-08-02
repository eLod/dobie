<?php

namespace dobie;

/**
 * The executor is responsible for evaluating code and outputting the result.
 */
abstract class Executor extends \dobie\Base {
    /**
     * Configuration options to use. Available options:
     * - `'bootstrap'` _boolean|string|array_: controls bootstrap for evaluating,
     *   see `codeString()`,
     * - `'uses'` _array_: list of classes to `use` when evaluating, see `codeString()`,
     * - `'resources'` _string_: path to resources directory, see `resources()`
     * - `'result_prompt'` _string_: prompt to use when displaying result,
     * - `'formatter'` _string_: the formatter (class) to use,
     * - `'output'` _closure|resource_: output to use,
     * - `'error'` _closure|resource_: error to use.
     *
     * @var array
     */
    protected $config = array(
	'bootstrap' => true,
	'uses' => array(),
	'resources' => null,
	'result_prompt' => '=> ',
	'formatter' => '\dobie\Formatter',
	'output' => STDOUT,
	'error' => STDERR
    );

    /**
     * Abstract function for subclasses to override.
     *
     * @param array $code Code lines to evaluate.
     * @return void
     */
    abstract public function execute(array $code);

    /**
     * Stop the executor. Called by `Console::stop()`,
     * subclasses may extend it, the default is a noop.
     *
     * @see Console::stop()
     * @return void
     */
    public function stop() {
    }

    /**
     * Creates a code string from code lines suitable for evaluation.
     * If `'add_bootstrap'` is `true` the configuration value for
     * `'boostrap'` is taken and if it is
     * - true it injects a line requiring the formatter path (determined
     *   by `Autoloader::path` using configuration value for `'formatter'`)
     * - string or array it injects it as line(s)
     * at the beginning of the returned string.
     *
     * @param array $code Code lines to transform.
     * @param array $options Configuration options to use. Available options are:
     *              - `'add_return'` _boolean_: try to inject a `return` statement
     *              wrapping the last expression, default `true`
     *              - `'add_uses'` _boolean_: add classes with `use`, default `true`
     *              - `'enclose_php'` _boolean_: enclose with php tags, default `false`
     *              - `'glue'` _string_: to glue for joining lines, default `PHP_EOL`
     *              - `'add_bootstrap'` _boolean_: add bootstrap line(s), default `false`
     * @return string The transformed code string.
     */
    protected function codeString(array $code, array $options = array()) {
	$defaults = array(
	    "add_return" => true,
	    "add_uses" => true,
	    "enclose_php" => false,
	    "glue" => PHP_EOL,
	    "add_bootstrap" => false
	);
	$options += $defaults;
	if ($options["add_return"]) {
	    $level = array_reduce($code, function($level, $line) {
		$increase = strlen(preg_replace('/[^{(]/', "", $line));
		$decrease = strlen(preg_replace('/[^})]/', "", $line));
		return $level + $increase - $decrease;
	    }, 0);
	    if ($level == 0) {
		$line = count($code) - 1;
		$code[$line] = preg_replace('/;*$/', "", $code[$line]);
		for (;$line >= 0;$line--) {
		    $parts = explode(";", $code[$line]);
		    for ($i = count($parts) - 1;$i >= 0;$i--) {
			$increase = strlen(preg_replace('/[^{(]/', "", $parts[$i]));
			$decrease = strlen(preg_replace('/[^})]/', "", $parts[$i]));
			$level += $increase - $decrease;
			if ($level == 0) { //should be reached eventually
			    $foundpos = $i == 0 ? -1 : strlen(join(";", array_slice($parts, 0, $i)));
			    break;
			}
		    }
		    if ($level == 0) {
			break;
		    }
		}
		$returning = trim(substr($code[$line], $foundpos + 1));
		if (!preg_match('/^(echo|return|unset)/', $returning)) {
		    $code[$line] = substr($code[$line], 0, $foundpos + 1) . " return (" . $returning;
		    $code[count($code) - 1] .= ");";
		} else {
		    $code[] = ";return null;";
		}
	    } else {
		$code[] = ";return null;";
	    }
	}
	if ($options["add_uses"]) {
	    $create_use = function ($class) { return "use {$class};"; };
	    $uses = array_map($create_use, (array) $this->config['uses']);
	    $code = array_merge($uses, array(""), $code);
	}
	if ($options["add_bootstrap"] && $this->config['bootstrap']) {
	    if ($this->config['bootstrap'] === true) {
		$formatter_path = Autoloader::path($this->config['formatter'], true);
		$code = array_merge(array("require '{$formatter_path}';", ""), $code);
	    } else {
		$code = array_merge((array) $this->config['bootstrap'], array(""), $code);
	    }
	}
	if ($options["enclose_php"]) {
	    $code = array_merge(array("<?php", ""), $code, array("", "?>"));
	}
	return join($options["glue"], $code);
    }

    /**
     * Get path where a named resource can be stored.
     *
     * @param string $name The resource's name.
     * @return string Path.
     */
    protected function resource($name) {
	return $this->config['resources'] . $name;
    }
}

?>