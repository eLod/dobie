<?php

namespace dobie\tests\cases;

use dobie\tests\mocks\Base;

class BaseTest extends \PHPUnit_Framework_TestCase {
    public function testConfigDefaults() {
	$base = new Base();
	$config = $base->config();
	$this->assertTrue(isset($config['testKey']));
	$this->assertEquals('testValue', $config['testKey']);
	$this->assertTrue(isset($config['testExtendKey']));
	$this->assertEquals('testExtendValue', $config['testExtendKey']);
    }

    public function testConfigOverridesDefaults() {
	$base = new Base(array('testKey' => 'overrideTestValue'));
	$config = $base->config();
	$this->assertTrue(isset($config['testKey']));
	$this->assertEquals('overrideTestValue', $config['testKey']);
    }

    public function testConfigMerges() {
	$base = new Base(array('testMergeKey' => array('override' => 'override')));
	$config = $base->config();
	$this->assertTrue(isset($config['testMergeKey']));
	$this->assertEquals(array('keep' => 'keep', 'override' => 'override'), $config['testMergeKey']);
    }

    public function testIO() {
	$output = $error = array();
	$base = new Base(array(
	    'output' => function ($msg, $options) use (&$output) { $output[] = $msg; },
	    'error' => function ($msg, $options) use (&$error) { $error[] = $msg; }
	));
	$base->pout("testout");
	$this->assertEquals(array("testout"), $output);
	$base->perror("testerror");
	$this->assertEquals(array("testerror"), $error);
    }

    public function testIOReturnsFalse() {
	$base = new Base(array('output' => null));
	$this->assertFalse($base->pout());
    }

    public function testIOWritesResource() {
	$res = fopen("php://memory", "r+");
	$base = new Base(array('output' => $res));
	$string = "test";
	$this->assertEquals(strlen($string . "\n"), $base->pout($string));
	rewind($res);
	$stream = stream_get_contents($res);
	$this->assertEquals($string . "\n", $stream);
	$this->assertEquals(0, $base->pout(null));
	fclose($res);
    }
}

?>
