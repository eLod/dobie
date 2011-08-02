<?php

namespace dobie;

/**
 * The shell is responsible for reading code (lines) from input.
 */
abstract class Shell extends \dobie\Base {
    /**
     * Configuration options to use. Available options:
     * - `'prompts'` _array_: list of prompts,
     * - `'exit_command'` _string_: exit command to return upon CTRL-D, see `read()`,
     * - `'output'` _closure|resource_: output to use,
     * - `'error'` _closure|resource_: error to use.
     *
     * @var array
     */
    protected $config = array(
	'prompts' => array('main' => '> ', 'sub' => ''),
	'exit_command' => 'quit',
	'output' => STDOUT,
	'error' => STDERR
    );

    /**
     * Merged configuration keys.
     *
     * @var array
     */
    protected $mergeConfig = array('prompts');

    /**
     * Read code (lines) from input. Calls `readline` in loop, until
     * - a returned line is `false` (CTRL-D) or
     * - input code end (nesting is 0 and line does not end with `;`)
     * and returns array of code lines to interpret (if CTRL-D is pressed
     * it returns an array with a single element that is the configuration
     * option for key `'exit_command'`).
     *
     * @see readline()
     * @see getNesting()
     * @return array Array of code lines read from input.
     */
    public function read() {
	$lines = array ();
	$level = 0;
	while (true) {
	    $line = $this->readline(count($lines) > 0 ? 'sub' : 'main');
	    if ($line === false) { //CTRL-D is pressed
		$this->out();
		$lines = array($this->config['exit_command']);
		break;
	    }
	    $line = trim($line);
	    $level += $this->getNesting($line);
	    $lines[] = $line;
	    if ($level == 0 && substr($line, -1) != ";") {
		break;
	    }
	}
	return $lines;
    }

    /**
     * Stop the shell. Called by `Console::stop()`,
     * subclasses may extend it, the default is a noop.
     *
     * @see Console::stop()
     * @return void
     */
    public function stop() {
    }

    /**
     * Abstract function for subclasses to override.
     *
     * @param string $prompt_type The prompt type to output.
     * @return string The line read from input.
     */
    abstract protected function readline($prompt_type);

    /**
     * Get the nesting level of a code line by calculating
     * open and closing curly braces and parantheses.
     *
     * @param string $line The code line.
     * @return integer The level difference found in $line.
     */
    protected function getNesting($line) {
	return strlen(preg_replace('/[^{(]/', "", $line)) - strlen(preg_replace('/[^})]/', "", $line));
    }
}

?>