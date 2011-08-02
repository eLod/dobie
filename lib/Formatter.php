<?php

namespace dobie;

/**
 * Formatting values for output.
 */
class Formatter {
    /**
     * Format variable for output.
     *
     * @see Executor
     * @see executor\ExternalExecutor
     * @param $obj Variable to format.
     * @return string String representation suitable for output.
     */
    public static function format($obj) {
	if (is_array($obj)) {
	    return json_encode($obj);
	} else if ($obj instanceof \Closure) {
	    return "closure";
	} else if (is_bool($obj)) {
	    return $obj ? "true" : "false";
	} else if (is_null($obj)) {
	    return "null";
	} else if (is_string($obj)) {
	    return $obj;
	} else {
	    return var_export($obj, true);
	}
    }
}

?>