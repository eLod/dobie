<?php

namespace dobie\tests\cases;

use dobie\tests\mocks\Console;
use dobie\tests\mocks\ConsoleWithConfigurableReadline;

class ConsoleTest extends \PHPUnit_Framework_TestCase {
    public function proxyOut($output, $options) {
	$this->output[] = $output;
    }

    public function proxyError($output, $options) {
	$this->errors[] = $output;
    }

    public function setUp() {
	$this->input = fopen("php://memory", "r+");
	$this->output = $this->errors = array();
	$this->console = new Console(array('shell' => array('input' => $this->input), 'output' => array($this, 'proxyOut'), 'error' => array($this, 'proxyError')));
    }

    public function tearDown() {
	$this->console->stop();
	fclose($this->input);
    }

    public function testRunAndQuit() {
	$this->console->readCodes = array(array('quit'));
	$this->assertEquals(array(), $this->console->history);
	$this->console->run();
	$this->assertEquals(array('readCode', 'stop'), $this->console->history);
	$this->assertEquals('Exiting', array_pop($this->output));
    }

    public function testRunEmptyAndQuit() {
	$this->console->readCodes = array(array(''), array('quit'));
	$this->console->run();
	$this->assertEquals(array('readCode', 'readCode', 'stop'), $this->console->history);
    }

    public function testRun() {
	$this->console->readCodes = array(array('$a = 3+4'), array('quit'));
	$this->console->run();
	$this->assertEquals(array('readCode', 'execute', 'readCode', 'stop'), $this->console->history);
	$this->assertEquals('Exiting', array_pop($this->output));
	$this->assertEquals('=> test', array_pop($this->output));
    }

    public function testDoesntRunWithoutShell() {
	$this->console->killShell();
	$this->assertFalse($this->console->run());
    }

    public function testDoesntRunWithoutExecutor() {
	$this->console->killExecutor();
	$this->assertFalse($this->console->run());
    }

    public function testResourcesNotWritable() {
	$console = new Console(array('resources' => '/invalid/path/hopefully', 'error' => array($this, 'proxyError'), 'output' => false, 'no_override' => true));
	$this->assertFalse($console->run());
	$this->assertRegExp('/^\[ERROR\] resources directory not writable/', array_pop($this->errors));
	$console->stop();
    }

    public function testUsesReadlineIfPresented() {
	ConsoleWithConfigurableReadline::$overrideReadline = true;
	$this->errors = array();
	$console = new ConsoleWithConfigurableReadline(array('shell' => array('completion' => false, 'history' => false), 'error' => array($this, 'proxyOut'), 'output' => false));
	$this->assertTrue($console->shell() instanceof \dobie\shell\Readline);
	$this->assertEquals(array(), $this->errors);
	$console->stop();
    }

    public function testShowsWarningIfReadlineNotPresented() {
	ConsoleWithConfigurableReadline::$overrideReadline = false;
	$console = new ConsoleWithConfigurableReadline(array('error' => array($this, 'proxyError'), 'output' => false));
	$this->assertFalse($console->shell() instanceof \dobie\shell\Readline);
	$this->assertRegExp('/^\[WARN\] Readline is not supported/', array_pop($this->errors));
	$console->stop();
    }
}

?>
