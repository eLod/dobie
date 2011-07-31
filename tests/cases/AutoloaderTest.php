<?php

namespace dobie\tests\cases;

use dobie\tests\mocks\Autoloader as MockAutoloader;

class AutoloaderTest extends \PHPUnit_Framework_TestCase {
    public function testPath() {
	$path1 = MockAutoloader::path('dobie\Autoloader');
	$path2 = MockAutoloader::path('\dobie\Autoloader');
	$this->assertTrue($path1 !== false);
	$this->assertEquals($path1, $path2);
    }

    public function testAbsolutePath() {
	$path = MockAutoloader::path('dobie\Autoloader', true);
	$this->assertEquals("/", $path[0]);
	$this->assertTrue(file_exists($path));
    }

    public function testPathUnknown() {
	$this->assertFalse(MockAutoloader::path('unknown\Class'));
    }

    public function testRegister() {
	MockAutoloader::register();
	MockAutoloader::load("test");
	$this->assertEquals(array("test"), MockAutoloader::$loaded);
    }
}

?>
