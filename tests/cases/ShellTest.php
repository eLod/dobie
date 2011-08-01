<?php

namespace dobie\tests\cases;

use dobie\tests\mocks\Shell;

class ShellTest extends \PHPUnit_Framework_TestCase {
    public function setUp() {
	$this->shell = new Shell();
    }

    public function tearDown() {
	$this->shell->stop();
    }

    public function testReadExit() {
	$this->shell->readLines = array(false);
	$this->assertEquals(array(), $this->shell->history);
	$this->shell->read();
	$this->assertEquals(array(array('readline', 'main')), $this->shell->history);
    }

    public function testReadMultipleLines() {
	$this->shell->readLines = array('function() {', '}');
	$this->assertEquals(array(), $this->shell->history);
	$this->shell->read();
	$this->assertEquals(array(
	    array('readline', 'main'),
	    array('readline', 'sub')
	), $this->shell->history);
    }

    public function testGetNesting() {
	$cases = array(
	    '$a = 3' => 0,
	    '$a = 3; $b = 4' => 0,
	    'function() {}' => 0,
	    '($a = 3' => 1,
	    '$a = 3)' => -1,
	    'function() { $a = function() { }' => 1,
	    'function() {} }' => -1
	);
	foreach ($cases as $case => $expected) {
	    $this->assertEquals($expected, $this->shell->pgetNesting($case));
	}
    }
}

?>