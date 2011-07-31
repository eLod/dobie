<?php

namespace dobie\tests\mocks;

class Callback {
    protected $callbacks = array();

    public function __construct(array $callbacks = array()) {
	$this->callbacks = $callbacks;
    }

    public function __call($name, $arguments) {
	if (array_key_exists($name, $this->callbacks)) {
	    return call_user_func_array($this->callbacks[$name], $arguments);
	}
    }
}

?>
