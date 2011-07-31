<?php

namespace dobie;

use dobie\shell\Readline as ReadlineShell;
use dobie\shell\Basic as BasicShell;
use dobie\executor\ExternalExecutor;

class Console extends \dobie\Base {
    protected $config = array(
	'exit_commands' => array('quit', 'exit', 'q'),
	'resources' => '/tmp',
	'shell' => array(),
	'output' => STDOUT,
	'error' => STDERR,
    );
    protected $executor;
    protected $shell;

    public function __construct(array $config = array()) {
	parent::__construct($config);
	if (!is_dir($this->config['resources']) || !is_writable($this->config['resources'])) {
	    $this->error("[ERROR] resources directory not writable ({$this->config['resources']}).");
	    return;
	}
	$this->config['resources'] .= (substr($this->config['resources'], -1) != DIRECTORY_SEPARATOR) ? DIRECTORY_SEPARATOR : '';
	$this->config['exit_commands'] = (array) $this->config['exit_commands'];
	extract($this->config);
	$shell_config = compact('output') + array('exit_command' => $exit_commands[0]) + (array) $shell;
	if (static::supportsReadline()) {
	    $this->shell = new ReadlineShell(array('history_file' => $resources . "history") + $shell_config);
	} else {
	    $this->error("[WARN] Readline is not supported, falling back to basic shell (no edit mode, history, completion, etc.).");
	    $this->shell = new BasicShell($shell_config);
	}
	$this->executor = new ExternalExecutor(compact('resources', 'output', 'error'));
    }

    public function run() {
	if (!$this->shell || !$this->executor) {
	    return false;
	}
	$this->out("Console running PHP".phpversion()." (". PHP_OS .")");
	while(true) {
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

    public static function supportsReadline() {
	return is_callable('readline');
    }
}

?>
