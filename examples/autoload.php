<?php

/*
* NOTE. next code is used to add logger classes to composer autoloading during developement
* On production you don't need to do this. Just install gelembjuk/logger using composer
* 
*/

$THIS_DIR = dirname(__FILE__).'/';

// composer autoload init. set path to composer autoload
$loader = require $THIS_DIR .'../src/vendor/autoload.php';

// add src lib to composer autoloader so our classes are loaded to with composer
$loader->add('Gelembjuk', $THIS_DIR.'/src/');
