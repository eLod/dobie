<?php

namespace dobie\tests\cases\executor;

use dobie\tests\mocks\executor\ExternalExecutorNoSpawn;

class ExternalExecutorNoSpawnTest extends \PHPUnit_Framework_TestCase {
    public function proxyOut($output, $options) {
	$this->output[] = $output;
    }

    public function proxyError($output, $options) {
	$this->error[] = $output;
    }

    public function setUp() {
	$this->output = $this->error = array();
	$this->executor = new ExternalExecutorNoSpawn(array(
	    'output' => array($this, 'proxyOut'),
	    'error' => array($this, 'proxyError'),
	    'resources' => DOBIE_RESOURCE_PATH
	));
	$this->assertEquals(array(), $this->output);
	$this->assertEquals(array(), $this->error);
    }

    public function tearDown() {
	$this->executor->stop();
    }

    public function testShowsErrorIfCannotSpawn() {
	$this->executor->execute(array('42'));
	$this->assertEquals(array(), $this->output);
	$this->assertEquals(array("[ERROR] failed spawning process, cannot execute"), $this->error);
	$this->executor->execute(array('42'));
	$this->assertEquals(array(), $this->output);
	$this->assertEquals(array("[ERROR] failed spawning process, cannot execute", "[ERROR] failed spawning process, cannot execute"), $this->error);
    }
}

?>
