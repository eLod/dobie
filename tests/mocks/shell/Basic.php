<?php

namespace dobie\tests\mocks\shell;

class Basic extends \dobie\shell\Basic {
    public function preadline($p) {
	return $this->readline($p);
    }
}

?>