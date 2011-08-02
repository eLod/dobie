<?php

namespace dobie\tests\cases;

use dobie\Formatter;

class FormatterTest extends \PHPUnit_Framework_TestCase {
    protected function assertFormatted($formatted, $obj, $msg = false) {
	$this->assertEquals($formatted, Formatter::format($obj), $msg);
    }

    public function testFormatArray() {
	$data = array(
	    "foo" => "bar",
	    "int" => 1,
	    "float" => 3.4,
	    "array" => array(1,2,"string")
	);
	$this->assertFormatted(json_encode($data), $data);
    }

    public function testFormatClosure() {
	$closure = function() {};
	$this->assertFormatted("closure", $closure);
    }

    public function testFormatBasicTypes() {
	$this->assertFormatted("true", true);
	$this->assertFormatted("false", false);
	$this->assertFormatted("null", null);
	$string = "abcdefg1234'\"+!%/=(){}";
	$this->assertFormatted(var_export($string, true), $string);
    }

    public function testFormatObject() {
	$obj = new \stdClass();
	$obj->foo = "bar";
	$this->assertFormatted(var_export($obj, true), $obj);
    }
}

?>