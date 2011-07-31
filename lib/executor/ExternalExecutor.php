<?php

namespace dobie\executor;

use lithium\core\Environment;
use lithium\util\String;

class ExternalExecutor extends \dobie\Executor {
    protected $external;
    protected $pipes;
    protected $return_values = array();
    protected $external_code = array(
'while(true) {',
'    $file = trim(fgets(STDIN));',
'    $return = include($file);',
'    fwrite(STDOUT, "{:result_prompt}" . {:formatter_class}::format($return) . PHP_EOL);',
'    fwrite(STDOUT, "_EVALUATION_SUCCESSFULLY_ENDED_" . PHP_EOL);',
'    if (feof(STDIN)) {',
'	   exit;',
'    }',
'}',
    );

    public function __construct(array $config = array()) {
	parent::__construct($config);
	$strings = array(
	    'result_prompt' => $this->config['result_prompt'],
	    'formatter_class' => $this->config['formatter']
	);
	$this->external_code = array_map(function($line) use ($strings) {
	    return str_replace(array_map(function($key) { return "{:{$key}}"; }, array_keys($strings)), array_values($strings), $line);
	}, $this->external_code);
    }

    public function execute(array $code) {
	if (!is_resource($this->external)) {
	    $this->spawn_external();
	}
	if (!is_resource($this->external)) {
	    $this->error("[ERROR] failed spawning process, cannot execute");
	    return;
	}
	$path = $this->resource("external.".str_replace(" ", "_", microtime()).".php");
	file_put_contents($path, $this->codeString($code, array("enclose_php" => true)));
	fwrite($this->pipes[0], $path . PHP_EOL);
	$finished_success = $this->read_external();
	if (!$finished_success) {
	    $this->kill_external();
	}
    }

    public function stop() {
	parent::stop();
	$this->kill_external();
	foreach (glob($this->resource("external.*.php")) as $path) {
	    unlink($path);
	}
    }

    protected function spawn_external () {
	$descriptorspec = array(
	    array("pipe", "r"),
	    array("pipe", "w"),
	    array("pipe", "w")
	);
	$path = $this->resource("external.main.php");
	file_put_contents($path, $this->codeString($this->external_code, array("enclose_php" => true, "add_uses" => false, "add_return" => false, "add_bootstrap" => true)));
	$this->external = proc_open(PHP_BINDIR . DIRECTORY_SEPARATOR . "php {$path}", $descriptorspec, $this->pipes);
    }

    protected function kill_external () {
	if (is_resource($this->external)) {
	    foreach ($this->pipes as $pipe) {
		fclose($pipe);
	    }
	    $this->return_values[] = proc_close($this->external);
	}
    }

    protected function read_external () {
	while (!feof($this->pipes[1])) {
	    $line = trim(fgets($this->pipes[1]));
	    if ($line == "_EVALUATION_SUCCESSFULLY_ENDED_") {
		return true;
	    }
	    $this->out($line);
	}
	return false;
    }
}

?>
