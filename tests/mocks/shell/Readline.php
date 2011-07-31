<?php

namespace dobie\shell;

function readline($prompt) {
    ReadlineProxy::out($prompt);
    return ReadlineProxy::readline();
}

function readline_read_history($path) {
    ReadlineProxy::history('read_history');
    return true;
}

function readline_add_history($line) {
    ReadlineProxy::history('add_history');
    ReadlineProxy::cmdhistory($line);
    return true;
}

function readline_write_history($path) {
    ReadlineProxy::history('write_history');
    return true;
}

function readline_completion_function($cb) {
}

class ReadlineProxy {
    protected static $_proxy;

    public static function connect($proxy) {
	static::$_proxy = $proxy;
    }

    public static function readline() {
	return static::$_proxy->proxyReadline();
    }

    public static function out($output) {
	static::$_proxy->proxyOut($output);
    }

    public static function history($line) {
	static::$_proxy->proxyHistory($line);
    }

    public static function cmdhistory($line) {
	static::$_proxy->proxyCmdHistory($line);
    }
}

namespace dobie\tests\mocks\shell;

class Readline extends \dobie\shell\Readline {
    public $history = array();
    public $cmdhistory = array();
    public $stopped = false;
    public $input;

    public function __construct(array $config = array()) {
	\dobie\shell\ReadlineProxy::connect($this);
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
