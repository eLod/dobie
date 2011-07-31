<?php

namespace dobie\tests\cases\shell;

use dobie\tests\mocks\shell\Basic;

class BasicTest extends  \PHPUnit_Framework_TestCase {
    public function proxyOut($output, $options) {
	$this->output[] = $output;
    }

    public function setUp() {
	$this->input = fopen("php://memory", "r+");
	$this->output = array();
	$this->shell = new Basic(array('prompts' => array('test' => 'testprompt> '), 'input' => $this->input, 'output' => array($this, 'proxyOut')));
    }

    public function tearDown() {
	$this->shell->stop();
	fclose($this->input);
    }

    public function testReadline() {
	fputs($this->input, "test input line\n");
	rewind($this->input);
	$line = $this->shell->preadline('test');
	$this->assertEquals(array('testprompt> '), $this->output);
	$this->assertEquals("test input line\n", $line);
    }
}

?>
