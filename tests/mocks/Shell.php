<?php

namespace dobie\tests\mocks;

class Shell extends \dobie\Shell {
    public $history = array();
    public $readLines = array();

    protected function readline($prompt_type) {
	$this->history[] = array('readline', $prompt_type);
	$line = array_shift($this->readLines);
	return $line;
    }

    public function pgetNesting($line) {
	return $this->getNesting($line);
    }
}

?>