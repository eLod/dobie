<?php

namespace dobie;

abstract class Shell extends \dobie\Base {
    protected $config = array(
	'prompts' => array('main' => '> ', 'sub' => ''),
	'exit_command' => 'quit',
	'output' => STDOUT,
	'error' => STDERR
    );
    protected $mergeConfig = array('prompts');

    public function read() {
	$lines = array ();
	$level = 0;
	while (true) {
	    $line = $this->readline(count($lines) > 0 ? 'sub' : 'main');
	    if ($line === false) { //CTRL-D is pressed
		$this->out();
		$lines = array($this->config['exit_command']);
		break;
	    }
	    $line = trim($line);
	    $level += $this->getNesting($line);
	    $lines[] = $line;
	    if ($level == 0 && substr($line, -1) != ";") {
		break;
	    }
	}
	return $lines;
    }

    public function stop() {
    }

    abstract protected function readline($prompt_type);

    protected function getNesting($line) {
	return strlen(preg_replace('/[^{(]/', "", $line)) - strlen(preg_replace('/[^})]/', "", $line));
    }
}

?>