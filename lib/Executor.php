<?php

namespace dobie;

abstract class Executor extends \dobie\Base {
    protected $config = array(
	'bootstrap' => true,
	'uses' => array(),
	'resources' => null,
	'result_prompt' => '=> ',
	'formatter' => '\dobie\Formatter',
	'output' => STDOUT,
	'error' => STDERR
    );

    abstract public function execute(array $code);

    public function stop() {
    }

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
	    $code = array_merge(array_map(function ($class) {
		return "use {$class};";
	    }, (array) $this->config['uses']), array(""), $code);
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

    protected function resource($name) {
	return $this->config['resources'] . $name;
    }
}

?>