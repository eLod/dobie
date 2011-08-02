<?php

namespace dobie;

use dobie\shell\Readline as ReadlineShell;
use dobie\shell\Basic as BasicShell;
use dobie\executor\ExternalExecutor;

/**
 * The console aggregates the executor and the shell, creating the REPL.
 */
class Console extends \dobie\Base {
    /**
     * Default configuration values.
     *
     * @var array
     */
    protected $config = array(
	'exit_commands' => array('quit', 'exit', 'q'),
	'resources' => '/tmp',
	'shell' => array(),
	'greet' => true,
	'output' => STDOUT,
	'error' => STDERR
    );

    /**
     * An instance of an `Executor` object.
     *
     * @see Executor
     * @var Executor
     */
    protected $executor;

    /**
     * An instance of a `Shell` object.
     *
     * @see Shell
     * @var Shell
     */
    protected $shell;

    /**
     * Initializes a console. Creates an ExternalExecutor, checks for readline
     * and if supported created a Readline shell, otherwise falls back to a Basic
     * shell.
     *
     * @see Executor
     * @see executor\ExternalExecutor
     * @see Shell
     * @see shell\Basic
     * @see shell\Readline
     * @see $config
     * @param array $config Configuration options to use, accepted options are:
     *              - `'exit_commands'` _array_: list of commands for exiting the shell,
     *              - `'resources'` _string_: directory path to put resources in (must be writable),
     *              - `'shell'` _array_: extra options for the shell,
     *              - `'output'` _closure|resource_: output to use,
     *              - `'error'` _closure|resource_: error to use,
     *              - `'greet'` _boolean|closure_: controls greeting (see `greet()`).
     *              For defaults see `$config`.
     */
    public function __construct(array $config = array()) {
	parent::__construct($config);
	if (!is_dir($this->config['resources']) || !is_writable($this->config['resources'])) {
	    $this->error("[ERROR] resources directory not writable ({$this->config['resources']}).");
	    return;
	}
	$trailing_slash = substr($this->config['resources'], -1) != DIRECTORY_SEPARATOR;
	$this->config['resources'] .= $trailing_slash ? DIRECTORY_SEPARATOR : '';
	$this->config['exit_commands'] = (array) $this->config['exit_commands'];
	extract($this->config);
	$shell_config = compact('output') + array('exit_command' => $exit_commands[0]) + (array) $shell;
	if (static::supportsReadline()) {
	    $this->shell = new ReadlineShell(array(
		'history_file' => $resources . "history"
	    ) + $shell_config);
	} else {
	    $this->error("[WARN] Readline is not supported," .
		" falling back to basic shell (no edit mode, history, completion, etc.).");
	    $this->shell = new BasicShell($shell_config);
	}
	$this->executor = new ExternalExecutor(compact('resources', 'output', 'error'));
    }

    /**
     * Runs the console. Displays PHP information (see `greet()`) and reads codes
     * from shell passing it to the executor. If there were problems
     * with initializing the shell or the executor this method returns false.
     *
     * @see $executor
     * @see $shell
     * @return void|false
     */
    public function run() {
	if (!$this->shell || !$this->executor) {
	    return false;
	}
	$this->greet();
	while (true) {
	    $code = $this->shell->read();
	    if (count ($code) == 1 && in_array ($code[0], $this->config['exit_commands'])) {
		$this->stop();
		break;
	    } else if (count ($code) == 0 || (count($code) == 1 && $code[0] == "")) {
		continue;
	    }
	    $this->executor->execute($code);
	}
    }

    /**
     * Greets the user with PHP information.
     * When the configuration value `'greet'` is true
     * it displays a greeting on output with php version
     * and OS, if the value is callable
     * it calls it (without arguments).
     *
     * @return void
     */
    public function greet() {
	if (is_callable($this->config['greet'])) {
	    call_user_func($this->config['greet']);
	} else if ($this->config['greet'] == true) {
	    $this->out("Console running PHP" . phpversion() . " (" . PHP_OS . ")");
	}
    }

    /**
     * Stops the console. Stops the shell and executor and displays message.
     *
     * @see $executor
     * @see $shell
     * @param integer $status Controls message destination (`'output'` when `0`,
     *                `'error'` otherwise).
     * @param string $message The message to display, if any.
     * @return void
     */
    public function stop($status = 0, $message = "Exiting.") {
	if ($this->shell) {
	    $this->shell->stop();
	}
	if ($this->executor) {
	    $this->executor->stop();
	}
	if ($message) {
	    ($status == 0) ? $this->out($message) : $this->error($message);
	}
    }

    /**
     * Checks readline support.
     *
     * @return boolean If readline is supported.
     */
    public static function supportsReadline() {
	return is_callable('readline');
    }
}

?>