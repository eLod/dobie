<?php

namespace dobie\executor;

/**
 * An executor which spawns an external (long running) process,
 * creates code strings into temporary files and writes their path
 * into the external process' `STDIN` pipe, which in turn includes
 * that path (thus evaluating the codestring). This setup ensures
 * the executor would not terminate even when fatal errors are
 * presented while evaluating the input code.
 */
class ExternalExecutor extends \dobie\Executor {
    /**
     * Holds the external process' resource.
     *
     * @var resource
     */
    protected $external;

    /**
     * The external process' pipes.
     *
     * @var array
     */
    protected $pipes;

    /**
     * Return values from processes
     * (returned by process_close, e.g. exit codes).
     *
     * @var array
     */
    protected $return_values = array();

    /**
     * The code for the external process.
     * {:result_prompt} and {:formatter_class} are replaced in constructor.
     *
     * @var array
     */
    protected $external_code = array(
'while(true) {',
'    $file = trim(fgets(STDIN));',
'    $return = include($file);',
'    fwrite(STDOUT, "{:result_prompt}" . {:formatter_class}::format($return) . PHP_EOL);',
'    fwrite(STDOUT, "_EVALUATION_SUCCESSFULLY_ENDED_" . PHP_EOL);',
'    if (feof(STDIN)) {',
'	   exit;',
'    }',
'}'
    );

    /**
     * For available configuration options see `Executor::$config`.
     * This method replaces `result_prompt` and `formatter_class`
     * in `$external_code`.
     *
     * @see Executor::$config
     * @see $external_code
     * @param array $config Configuration options, see `Executor::$config`.
     */
    public function __construct(array $config = array()) {
	parent::__construct($config);
	$strings = array(
	    'result_prompt' => $this->config['result_prompt'],
	    'formatter_class' => $this->config['formatter']
	);
	$this->external_code = array_map(function($line) use ($strings) {
	    return str_replace(array_map(function($key) {
		return "{:{$key}}";
	    }, array_keys($strings)), array_values($strings), $line);
	}, $this->external_code);
    }

    /**
     * Execute input code lines. This method ensures that
     * there's an external process running (writes a message
     * to error and returns if can not start one). Then it
     * creates a temporary file with a transformed code
     * string (see `Executor::codeString()`) and write this
     * file's path to the `STDIN` pipe of the external process.
     * Next it tries to read the external process' output
     * (see `read_external()`) and kills the external process
     * if reading did not finished succesfully.
     *
     * @see Executor::codeString()
     * @see read_external()
     * @param array $code Code lines to execute.
     * @return void
     */
    public function execute(array $code) {
	if (!is_resource($this->external)) {
	    $this->spawn_external();
	}
	if (!is_resource($this->external)) {
	    $this->error("[ERROR] failed spawning process, cannot execute");
	    return;
	}
	$path = $this->resource("external." . str_replace(" ", "_", microtime()) . ".php");
	file_put_contents($path, $this->codeString($code, array("enclose_php" => true)));
	fwrite($this->pipes[0], $path . PHP_EOL);
	$finished_success = $this->read_external();
	if (!$finished_success) {
	    $this->kill_external();
	}
    }

    /**
     * Stops the external executor, cleaning the resource files created.
     *
     * @return void
     */
    public function stop() {
	parent::stop();
	$this->kill_external();
	foreach (glob($this->resource("external.*.php")) as $path) {
	    unlink($path);
	}
    }

    /**
     * Spawns an external process.
     *
     * @return void
     */
    protected function spawn_external () {
	$descriptorspec = array(
	    array("pipe", "r"),
	    array("pipe", "w"),
	    array("pipe", "w")
	);
	$path = $this->resource("external.main.php");
	file_put_contents($path, $this->codeString($this->external_code, array(
	    "enclose_php" => true,
	    "add_uses" => false,
	    "add_return" => false,
	    "add_bootstrap" => true
	)));
	$php_bin = substr(PHP_OS, 0, 3) == 'WIN' ? 'php.exe' : 'php';
	$php_bin = PHP_BINDIR . DIRECTORY_SEPARATOR . $php_bin;
	$this->external = proc_open("{$php_bin} {$path}", $descriptorspec, $this->pipes);
    }

    /**
     * Kills the current external process (if valid).
     *
     * @return void
     */
    protected function kill_external () {
	if (is_resource($this->external)) {
	    foreach ($this->pipes as $pipe) {
		fclose($pipe);
	    }
	    $this->return_values[] = proc_close($this->external);
	}
    }

    /**
     * Read external process' output.
     * Output lines until evaluation ended or EOF reached on
     * external process' `STDOUT` pipe (process ended abnormally).
     *
     * @return boolean True if evaluation ended successfully.
     */
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