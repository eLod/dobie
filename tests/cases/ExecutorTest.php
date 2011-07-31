<?php

namespace dobie\tests\cases;

use dobie\tests\mocks\Executor;
use dobie\Autoloader;

class ExecutorTest extends \PHPUnit_Framework_TestCase {
    public function testCodeStringGeneration() {
	$path = DOBIE_FIXTURE_PATH . '/executor/';
	$fixtures = array_map(function ($fixture_path) use ($path) {
	    return substr($fixture_path, strlen($path), strlen('.expected.php') * -1);
	}, glob($path . '*.expected.php'));
	$this->assertTrue(count($fixtures) > 0, "no fixtures found in {$path}");
	$executor = new Executor();
	foreach($fixtures as $fixture) {
	    $this->assertTrue(is_file($path.$fixture.".php"), $path.$fixture.".php not found");
	    $this->assertTrue(is_file($path.$fixture.".expected.php"), $path.$fixture.".expected.php not found");
	    if (is_file($path.$fixture.".ini")) {
		$options = @parse_ini_file($path.$fixture.".ini");
	    } else {
		$options = array();
	    }
	    $code = explode("\n", file_get_contents($path.$fixture.".php"));
	    $codeStr = $executor->pcodeString($code, $options);
	    $expected = file_get_contents($path.$fixture.".expected.php");
	    $this->assertEquals($expected, $codeStr, "fixture {$fixture} failed with options ".json_encode($options)."\n");
	}
    }

    public function testSetsResourcePath() {
	$executor = new Executor(array('resources' => 'foo/bar/'));
	$this->assertEquals('foo/bar/name', $executor->presource('name'));
    }

    public function testCodeStringBootstrap() {
	$executor = new Executor();
	$formatter_path = Autoloader::path('\dobie\Formatter', true);
	$str = $executor->pcodeString(array('42'), array('add_bootstrap' => true));
	$this->assertTrue(strpos($str, "require '{$formatter_path}';") !== false, "formatter ({$formatter_path}) not found in\n{$str}");
	$executor = new Executor(array('bootstrap' => "require 'bootstrap.php';"));
	$str = $executor->pcodeString(array('42'), array('add_bootstrap' => true));
	$this->assertTrue(strpos($str, "require 'bootstrap.php';") !== false, "bootstrap not found in\n{$str}");
    }
}

?>
