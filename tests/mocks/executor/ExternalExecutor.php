<?php

namespace dobie\tests\mocks\executor;

class ExternalExecutor extends \dobie\executor\ExternalExecutor {
    public $stopped = false;

    public function stop() {
	if ($this->stopped) {
	    return;
	}
	parent::stop();
	$this->stopped = true;
    }

    public function setOutput($cb) {
	$this->config['output'] = $cb;
    }

    public function setError($cb) {
	$this->config['error'] = $cb;
    }

    public function presource($name) {
	return $this->resource($name);
    }
}

?>