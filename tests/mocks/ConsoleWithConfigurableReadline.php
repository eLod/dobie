<?php

namespace dobie\tests\mocks;

class ConsoleWithConfigurableReadline extends \dobie\Console {
    public static $overrideReadline;

    public static function supportsReadline() {
	return static::$overrideReadline;
    }

    public function shell() {
	return $this->shell;
    }
}

?>
