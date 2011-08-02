<?php

namespace dobie\shell;

/**
 * Basic shell if readline support is not presented.
 */
class Basic extends \dobie\Shell {
    /**
     * Extra configuration for this shell.
     * Only options is `'input'`, which should
     * be a valid resource, defaults to `STDIN`.
     *
     * @var array
     */
    protected $extendConfig = array(
	'input' => STDIN
    );

    /**
     * Output prompt and read line from input.
     * Simply calls `fgets` on configuration value for `'input'`.
     *
     * @param string $prompt_type Prompt to use.
     * @return string Line read.
     */
    protected function readline($prompt_type = 'main') {
	$this->out($this->config['prompts'][$prompt_type], array('nl' => 0));
	return fgets($this->config['input']);
    }
}

?>