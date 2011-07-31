<?php

namespace dobie\tests\cases\shell;

use dobie\tests\mocks\shell\Readline;

class ReadlineTest extends  \PHPUnit_Framework_TestCase {
    public function proxyOut($output, $options) {
	$this->output[] = $output;
    }

    public function setUp() {
	$this->input = fopen("php://memory", "r+");
	$this->output = array();
	$this->shell = new Readline(array('prompts' => array('test' => 'testprompt> '), 'output' => array($this, 'proxyOut'), 'history_file' => __FILE__));
	$this->shell->input = $this->input;
    }

    public function tearDown() {
	if (! $this->shell->stopped) {
	    $this->shell->stop();
	}
	fclose($this->input);
    }

    public function testReadline() {
	fputs($this->input, "test input line\n");
	rewind($this->input);
	$line = $this->shell->preadline('test');
	$this->assertEquals(array('testprompt> '), $this->output);
	$this->assertEquals("test input line\n", $line);
    }

    public function testUsesHistory() {
	$this->assertEquals(__FILE__, $this->shell->historyFile());
	$this->assertEquals(array('read_history'), $this->shell->history);
	$this->shell->stop();
	$this->assertEquals(array('read_history', 'write_history'), $this->shell->history);
    }

    public function testAddsToHistory() {
	fputs($this->input, "test input line\n");
	rewind($this->shell->input);
	$this->shell->read();
	$this->assertEquals(array('read_history', 'add_history'), $this->shell->history);
	$this->assertEquals(array("test input line"), $this->shell->cmdhistory);
    }

    public function testCompletion() {
	$completions = $this->shell->completions();
	$this->assertContains('array_merge', $completions);
	$this->assertContains('STDIN', $completions);
	$this->assertContains('stdClass', $completions);
    }

    public function testStoreCompletions() {
	$this->shell->stop();
	$completion = array('test', 'values');
	$this->shell = new Readline(compact('completion'));
	$this->assertEquals($completion, $this->shell->pcomplete("", 0));
    }
}

?>
