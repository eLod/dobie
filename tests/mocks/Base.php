<?php

namespace dobie\tests\mocks;

class Base extends \dobie\Base {
    protected $config = array('testKey' => 'testValue', 'testMergeKey' => array('keep' => 'keep', 'override' => 'default'), 'output' => null, 'error' => null);
    protected $extendConfig = array('testExtendKey' => 'testExtendValue');
    protected $mergeConfig = array('testMergeKey');

    public function config() {
	return $this->config;
    }

    public function pout($output = null, $options = array()) {
	return $this->out($output, $options);
    }

    public function perror($output = null, $options = array()) {
	return $this->error($output, $options);
    }
}

?>
