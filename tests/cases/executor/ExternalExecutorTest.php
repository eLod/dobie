<?php

namespace dobie\tests\cases\executor;

use dobie\tests\mocks\executor\ExternalExecutor;

class ExternalExecutorTest extends \PHPUnit_Framework_TestCase {
    public function setUp() {
	$output = $error = array();
	$this->executor = new ExternalExecutor(array(
	    'output' => function ($msg, $opts) use (&$output) { $output[] = $msg; },
	    'error' => function ($msg, $opts) use (&$error) { $error[] = $msg; },
	    'resources' => DOBIE_RESOURCE_PATH
	));
	$this->assertEquals(array(), $output);
	$this->assertEquals(array(), $error);
    }

    public function tearDown() {
	$this->executor->stop();
    }

    protected function countTmpFiles() {
	return count(glob($this->executor->presource("*.php")));
    }

    protected function setOutput(&$output) {
	$this->executor->setOutput(function ($msg, $options) use (&$output) { $output[] = $msg; });
    }

    protected function checkRun($code, $expected_out = array()) {
	$output = array();
	$this->setOutput(&$output);
	$this->executor->execute($code);
	$this->assertEquals($expected_out, $output);
    }

    public function testClean() {
	$this->assertEquals(0, $this->countTmpFiles());
	$this->executor->execute(array('$a = 3'));
	$this->assertEquals(2, $this->countTmpFiles());
	$this->executor->execute(array('$a = 3'));
	$this->assertEquals(3, $this->countTmpFiles());
	$this->executor->stop();
	$this->assertEquals(0, $this->countTmpFiles());
    }

    public function testExecute() {
	$path = DOBIE_FIXTURE_PATH . '/external_executor/';
	$fixtures = array_map(function ($fixture_path) use ($path) {
	    return substr($fixture_path, strlen($path), strlen('.php') * -1);
	}, glob($path . '*.php'));
	$this->assertTrue(count($fixtures) > 0, "no fixtures found in {$path}");
	foreach ($fixtures as $fixture) {
	    $this->assertTrue(is_file($path . $fixture . ".php"), "{$path}{$fixture}.php not found");
	    $this->assertTrue(is_file($path . $fixture . ".out"), "{$path}{$fixture}.out not found");
	    $code = explode("\n", file_get_contents($path . $fixture . ".php"));
	    $expected_out = explode("\n", file_get_contents($path . $fixture . ".out"));
	    $this->checkRun($code, $expected_out);
	}
    }

    public function testRememberVariable() {
	$this->checkRun(array('$a = 42'), array("=> 42"));
	$this->checkRun(array('echo $a."\n"'), array("42", "=> null"));
    }

    public function testRememberVariableOnNonFatalError() {
	$this->checkRun(array('$a = 42'), array("=> 42"));
	$output = array();
	$this->setOutput(&$output);
	$this->executor->execute(array('$b = 3/0'));
	$err = join("\n", $output);
	$this->assertRegExp('/Warning: Division by zero in/', $err, $err);
	$this->checkRun(array('echo $a."\n"'), array("42", "=> null"));
    }

    public function testSurvivesFatalError() {
	$output = array();
	$this->setOutput(&$output);
	$this->executor->execute(array('nonexistent()'));
	$err = join("\n", $output);
	$this->assertRegExp('/Fatal error: Call to undefined function nonexistent\(\) in/', $err, $err);
	$this->checkRun(array('$a = 42'), array("=> 42"));
    }
}

?>