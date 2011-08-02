<?php

namespace dobie\tests\mocks;

use dobie\tests\mocks\Callback;

class Console extends \dobie\Console {
    public $history = array();
    public $readCodes = array();

    public function __construct(array $config = array()) {
	parent::__construct($config);
	if (!isset($config['no_override']) || $config['no_override'] !== true) {
	    $this->shell = new Callback(array('read' => array($this, 'read')));
	    $this->executor = new Callback(array('execute' => array($this, 'execute')));
	}
    }

    public function stop($status = 0, $message = "Exiting") {
	$this->history[] = 'stop';
	parent::stop($status, $message);
    }

    public function read() {
	$this->history[] = 'readCode';
	$act = array_shift($this->readCodes);
	return $act;
    }

    public function execute(array $code = array()) {
	$this->history[] = 'execute';
	$this->out('=> test');
    }

    public function killShell() {
	$this->shell->stop();
	$this->shell = null;
    }

    public function killExecutor() {
	$this->executor->stop();
	$this->executor = null;
    }

    public function greet() {
	if (is_callable($this->config['greet']) || $this->config['greet']) {
	    $this->history[] = 'greet';
	}
	parent::greet();
    }

    public function setGreet($greet) {
	$this->config['greet'] = $greet;
    }
}

?>