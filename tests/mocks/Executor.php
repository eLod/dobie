<?php

namespace dobie\tests\mocks;

class Executor extends \dobie\Executor {
    public function pcodeString(array $code, array $options = array()) {
	return $this->codeString($code, $options);
    }

    public function presource($name) {
	return $this->resource($name);
    }

    public function execute(array $code) {
    }
}

?>
