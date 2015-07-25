<?php 

/**
 * Example. Usage of FileLogger to log with configurable filtering
 * The example demonstrates how to use the gelembjuk/logger package to have configurable logging
 * 
 * This example is part of gelembjuk/logger package by Roman Gelembjuk (@gelembjuk)
 */

// path to your composer autoloader
require ('../vendor/autoload.php');

// example class. It has some logging inside.
// Using the group events filter you can turn groups On and Off
class Worker {
	protected $logger;
	public function __construct($logger) {
		$this->logger = $logger;
		$this->logger->debug('Class object created',array('group' => 'construct'));
	}

	public function doSomething() {
		$this->A();
		$this->B();
		$this->C();
	}
	protected function A() {
		$this->logger->debug('Called A',array('group' => 'A'));

		$this->C();
	}
	protected function B() {
		$this->logger->debug('Called B',array('group' => 'B'));

		$this->C();
		$this->D();
	}
	protected function C() {
		$this->logger->debug('Called C',array('group' => 'C'));
	}
	protected function D() {
		// this will log if groups D or A or B are in the filter
		$this->logger->debug('Called D',array('group' => 'D|A|B'));
	}
}

// NOTE. this file must be writable! 
// The path to your log file
$logfile = dirname(__FILE__).'/tmp/log.txt';

// such check is done only for this test
// on production you have to decide if you need it or not
if (!is_writable($logfile) && file_exists($logfile)
	|| !file_exists($logfile) && !is_writable(dirname($logfile))) {
	echo '<font color="red">No access to write to log file '.$logfile.'</font>';
	exit;
}

// create the logger object
$logger1 = new Gelembjuk\Logger\FileLogger(
		array(
			'logfile' => $logfile,
			'groupfilter' => 'all' // log everything
		));

// do test log write. at this time all logs will be written
$logger1->debug('Test log',array('group' => 'test'));

// create test class object
$worker = new Worker($logger1);

// call a method to log somethign to a file 
$worker->doSomething();

$logger1->debug('Now disable logging',array('group' => 'test'));

// disable all loggin with empty filter
$logger1->setGroupFilter('');  

// call the method and nothing will be logged 
$worker->doSomething();

// log only selected groups events
// now log only test and C group events
$logger1->setGroupFilter('test|C');  

$logger1->debug('Now `test` and `C` groups to log',array('group' => 'test'));

// call the method. Only `C` logs will be logged 
$worker->doSomething();

// now log only test and C group events
$logger1->setGroupFilter('test|B');  

$logger1->debug('Now `test` and `B` groups to log',array('group' => 'test'));

// call the method. Only `B` logs will be logged 
$worker->doSomething();

$logger1->debug('End of program! NOTE. Each log line has a time and process ID, sometimes this helps!',array('group' => 'test'));

echo 'Now look in your log file!';
