<?php

namespace dobie\shell;

class Basic extends \dobie\Shell {
    protected $extendConfig = array(
	'input' => STDIN,
    );

    protected function readline($prompt_type = 'main') {
	$this->out($this->config['prompts'][$prompt_type], array('nl' => 0));
	return fgets($this->config['input']);
    }
}

?>
