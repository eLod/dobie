<?php

namespace dobie\shell;

class Readline extends \dobie\Shell {
    protected $extendConfig = array(
	'history' => true,
	'history_file' => null,
	'completion' => true,
    );
    protected $completions;

    public function __construct(array $config = array()) {
	parent::__construct($config);
	if ($this->config['history'] && is_file($this->config['history_file'])) {
	    readline_read_history($this->config['history_file']);
	}
	if ($this->config['completion']) {
	    if (is_array($this->config['completion'])) {
		$this->completions = $this->config['completion'];
	    } else {
		$phpList = get_defined_functions();
		$this->completions = array_merge(
		    $phpList['internal'],
		    array_keys(get_defined_constants()),
		    get_declared_classes(),
		    get_declared_interfaces()
		); //todo maybe we should add new items periodically after executions
	    }
	    $this->config['completion'] = true;
	    readline_completion_function(array($this, 'complete'));
	}
    }

    public function read() {
	$lines = parent::read();
	if ($this->config['history'] && count($lines) > 0 && $lines[0] != "") {
	    readline_add_history(join(" ", $lines));
	}
	return $lines;
    }

    public function stop() {
	if ($this->config['history']) {
	    readline_write_history($this->config['history_file']);
	}
	parent::stop();
    }

    protected function readline($prompt_type) {
	return readline($this->config['prompts'][$prompt_type]);
    }

    protected function complete($input, $index) {
	return $this->completions;
    }
}

?>
