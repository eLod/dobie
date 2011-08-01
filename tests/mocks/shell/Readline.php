<?php

namespace dobie\shell;

function readline($prompt) {
    $_readline_proxy = readline_proxy();
    $_readline_proxy->proxyOut($prompt);
    return $_readline_proxy->proxyReadline();
}

function readline_read_history($path) {
    $_readline_proxy = readline_proxy();
    $_readline_proxy->proxyHistory('read_history');
    return true;
}

function readline_add_history($line) {
    $_readline_proxy = readline_proxy();
    $_readline_proxy->proxyHistory('add_history');
    $_readline_proxy->proxyCmdHistory($line);
    return true;
}

function readline_write_history($path) {
    $_readline_proxy = readline_proxy();
    $_readline_proxy->proxyHistory('write_history');
    return true;
}

function readline_completion_function($cb) {
}

function readline_proxy($proxy = null) {
    static $_readline_proxy;
    if ($proxy) {
	$_readline_proxy = $proxy;
    }
    return $_readline_proxy;
}

namespace dobie\tests\mocks\shell;

class Readline extends \dobie\shell\Readline {
    public $history = array();
    public $cmdhistory = array();
    public $stopped = false;
    public $input;

    public function __construct(array $config = array()) {
	\dobie\shell\readline_proxy($this);
	parent::__construct($config);
    }

    public function stop() {
	parent::stop();
	$this->stopped = true;
    }

    public function historyFile() {
	return $this->config['history_file'];
    }

    public function completions() {
	return $this->completions;
    }

    public function proxyReadline() {
	return fgets($this->input);
    }

    public function proxyOut($output) {
	$this->out($output);
    }

    public function proxyHistory($line) {
	$this->history[] = $line;
    }

    public function proxyCmdHistory($line) {
	$this->cmdhistory[] = $line;
    }

    public function preadline($p) {
	return $this->readline($p);
    }

    public function pcomplete($input, $index) {
	return $this->complete($input, $index);
    }
}

?>