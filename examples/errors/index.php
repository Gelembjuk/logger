<?php 

/**
 * Example. Usage of ErrorScreen class to catch warning/fatal errors, log them , as well, as uncatched exceptions
 * and display a user frendly error page
 * The example demonstrates how to use the gelembjuk/logger package to work with errors in a web application
 * 
 * This example is part of gelembjuk/logger package by Roman Gelembjuk (@gelembjuk)
 */

// path to your composer autoloader
require ('../vendor/autoload.php');

// NOTE. this file must be writable! 
// The path to your log file
$logfile = dirname(__FILE__).'/tmp/errors.txt';

// create error handling object

$errors = new Gelembjuk\Logger\ErrorScreen(
		array(
			'loggeroptions' => array(
				'logfile' => $logfile,
				'groupfilter' => 'all' // log everything
			),
			'viewformat' => 'html',
			'catchwarnings' => true,
			'catchfatals' => true
		)
	);

$errors->getLogger()->debug('Test started',array('group' => 'test'));

// the example script includes 
$test = $_REQUEST['test'];

if ($test == '1') {
	// Test 1. Catch warning, display error
	
	// set to show warning error. Same can be done with init option `showwarningmessage`=>true
	$errors->setShowWarningError(true);
	
	$errors->getLogger()->debug('Trying to include not existent PHP file',array('group' => 'test'));
	
	include('this_is_unfound_file.php');
	
	
} elseif ($test == '2') {
	// Test 2. Catch warning, don't display error
	
	// set to show warning error. Same can be done with init option `showwarningmessage`=>false
	$errors->setShowWarningError(false);
	
	$errors->getLogger()->debug('Trying to include not existent PHP file',array('group' => 'test'));
	
	include('this_is_unfound_file.php');
	
	echo 'Warning error was loged but not displayed';
	exit;
} elseif ($test == '3') {
	// Test 3. Catch fatal, display error
	
	// set to show warning error. Same can be done with init option `showwarningmessage`=>false
	$errors->setShowFatalError(true);
	
	$errors->getLogger()->debug('Trying to call method from non object',array('group' => 'test'));
	
	$foo->bar();
	
} elseif ($test == '4') {
	// Test 4. Catch warning, display error in JSON format
	
	// set to show warning error. Same can be done with init option `showwarningmessage`=>true
	$errors->setShowWarningError(true);
	$errors->setViewFormat('json');
	
	// don't display real error to a user. He should know details of the error
	$errors->setCommonErrorMessage('Some error happened. Sorry.');
	
	$errors->getLogger()->debug('Trying to shift from non array',array('group' => 'test'));
	
	array_shift($foo);
	
	exit;
} elseif ($test == '5') {
	// Test 5. Catch exception, display error

	try {
		// do something 
		throw new Exception('Some error happened and was not catched. So must to display');
	} catch (Exception $e) {
		$errors->processError($e);
	}
	
} elseif ($test == '6') {
	// Test 6. Catch exception, display error in XML format
	
	$errors->setViewFormat('xml');

	try {
		// do something 
		throw new Exception('Some other error happened in our API');
	} catch (Exception $e) {
		$errors->processError($e);
	}
	
} elseif ($test == '7') {
	// Test 7. Catch exception, display error with trace

	$errors->setShowTrace(true);

	try {
		// do something 
		smallTestFunction();
	} catch (Exception $e) {
		$errors->processError($e);
	}
	
} elseif ($test == '8') {
	// Test 8. Catch warning, display error with trace
	
	// set to show warning error. Same can be done with init option `showwarningmessage`=>true
	$errors->setShowWarningError(true);
	$errors->setShowTrace(true);
	
	$errors->getLogger()->debug('Trying to include not existent PHP file',array('group' => 'test'));
	
	include('this_is_unfound_file.php');
	
	
} else {
	$errors->getLogger()->debug('No test selected',array('group' => 'test'));
}

// show menu of tests

?>
<h3>ErrorScreen tests</h3>

<p>Choose your test</p>
<ul>
	<li><a href="index.php?test=1">Test 1. Catch warning, display error</a></li>
	<li><a href="index.php?test=2">Test 2. Catch warning, don't display error</a></li>
	<li><a href="index.php?test=3">Test 3. Catch fatal, display error</a></li>
	<li><a href="index.php?test=4">Test 4. Catch warning, display error in JSON format and common text</a></li>
	<li><a href="index.php?test=5">Test 5. Catch exception, display error</a></li>
	<li><a href="index.php?test=6">Test 6. Catch exception, display error in XML format</a></li>
	<li><a href="index.php?test=7">Test 7. Catch exception, display error with trace</a></li>
	<li><a href="index.php?test=8">Test 8. Catch warning, display error with trace</a></li>
</ul>

<p>All errors are logged to a file</p>
<?

if (!is_writable($logfile) && file_exists($logfile)
	|| !file_exists($logfile) && !is_writable(dirname($logfile))) {
	echo '<font color="red">No access to write to log file '.$logfile.'</font>';
}

function smallTestFunction() {
	throw new Exception('Some error to show trace.');
}
