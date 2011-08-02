<?php

namespace dobie\shell;

/**
 * Shell with readline support with history and completion features.
 */
class Readline extends \dobie\Shell {
    /**
     * Extra configuration for this shell.
     * Available options are:
     * - `'history'` _boolean_: if use history, default `true`,
     * - `'history_file'` _string_: path to history file,
     * - `'completion'` _boolean|array_: if use completion, default `true`.
     *
     * @var array
     */
    protected $extendConfig = array(
	'history' => true,
	'history_file' => null,
	'completion' => true
    );

    /**
     * Stores completions.
     *
     * @param array
     */
    protected $completions;

    /**
     * For available configuration options see `Shell::$config` and
     * `$extendConfig`. This method initializes readline's history
     * (if enabled) and sets completions (if enabled). Completions
     * are determined automatically (if `'completions'` is true)
     * from defined functions, constans, declared classes and
     * interfaces; used as provided if is an array.
     *
     * @see Shell::$config
     * @see $extendConfig
     * @param array $config Configuration options, see `Shell::$config` and `$extendConfig`.
     */
    public function __construct(array $config = array()) {
	parent::__construct($config);
	if ($this->config['history'] && is_file($this->config['history_file'])) {
	    readline_read_history($this->config['history_file']);
	}
	if ($this->config['completion']) {
	    if (is_array($this->config['completion'])) {
		$this->completions = $this->config['completion'];
	    } else {
		$phpList = get_defined_functions();
		$this->completions = array_merge(
		    $phpList['internal'],
		    array_keys(get_defined_constants()),
		    get_declared_classes(),
		    get_declared_interfaces()
		); //todo maybe we should add new items periodically after executions
	    }
	    $this->config['completion'] = true;
	    readline_completion_function(array($this, 'complete'));
	}
    }

    /**
     * Read code (lines) from input. Calls `Shell::read` and
     * stores the input in readline's history.
     *
     * @see Shell::read()
     * @return array Array of code lines read from input.
     */
    public function read() {
	$lines = parent::read();
	if ($this->config['history'] && count($lines) > 0 && $lines[0] != "") {
	    readline_add_history(join(" ", $lines));
	}
	return $lines;
    }

    /**
     * Stops the shell and writes history file if enabled.
     *
     * @return void
     */
    public function stop() {
	if ($this->config['history']) {
	    readline_write_history($this->config['history_file']);
	}
	parent::stop();
    }

    /**
     * Reads a line with readline and returns it.
     *
     * @param string $prompt_type Prompt to output.
     * @return string Line read.
     */
    protected function readline($prompt_type) {
	return readline($this->config['prompts'][$prompt_type]);
    }

    /**
     * Returns available completions.
     * As readline filters these it simply returns all completions every time.
     *
     * @param string $input The input to search completions for.
     * @param integer $index Cursor index.
     * @return array All completions.
     */
    protected function complete($input, $index) {
	return $this->completions;
    }
}

?>