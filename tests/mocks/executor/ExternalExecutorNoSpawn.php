<?php

namespace dobie\tests\mocks\executor;

class ExternalExecutorNoSpawn extends \dobie\executor\ExternalExecutor {
    protected function spawn_external() {
	$this->external = null;
    }
}

?>
