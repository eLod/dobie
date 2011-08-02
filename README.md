# Dobie

Dobie is a PHP 5.3 REPL or console.

**Please note:** this code is experimental and is in a very early stage, do not use it in production.

## Features

* fatal error handling (fatal errors do not terminate console),
* readline support (line editing with history and completion) with fallback to basic input,
* configurable and extendable, thus easy to integrate into other projects.

## Usage

    Console running PHP5.3.6 (Darwin)
    > $a = 2+2
    => 4
    > echo "{$a}2\n"
    42
    => null
    > $foo = function ($bar) {
    echo "yo, {$bar}!\n";
    }
    => closure
    > $bar = 3/0
    
    Warning: Division by zero in /private/tmp/external.0.70422200_1312259440.php on line 4
    
    Call Stack:
    0.0002     630200   1. {main}() /private/tmp/external.main.php:0
    10.5285     655288   2. include('/private/tmp/external.0.70422200_1312259440.php') /private/tmp/external.main.php:7
    
    => false
    > $foo("mama")
    yo, mama!
    => null
    > nonexistent()
    
    Fatal error: Call to undefined function nonexistent() in /private/tmp/external.0.22401900_1312259476.php on line 4
    
    Call Stack:
    0.0002     630200   1. {main}() /private/tmp/external.main.php:0
    46.0483     656680   2. include('/private/tmp/external.0.22401900_1312259476.php') /private/tmp/external.main.php:7
    
    
    > quit
    Exiting.

## Credits

Thanks to [nateabele](https://github.com/nateabele), [daschl](https://github.com/daschl), [masom](https://github.com/masom) and the lively [li3](http://lithify.me) community.

## Copyright

See LICENSE.txt for details.
