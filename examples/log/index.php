<?php 

// if you installed gelembjuk/logger with composer then replace next line with
// path to your composer autoloader
// require '../vendor/autoload.php';
require ('../autoload.php');


/*
Example. Usage of FileLogger to log with configurable filtering
*/


class Worker {
	protected $logger;
	public function __construct($logger) {
		$this->logger = $logger;
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
$logfile = dirname(__FILE__).'/tmp/log.txt';

$logger1 = new Gelembjuk\Logger\FileLogger(
			array(
				'logfile' => $logfile,
				'groupfilter' => 'all' // log everything
			));

// do test log write
$logger1->debug('Test log',array('group' => 'test'));

// create test class object
$worker = new Worker($logger1);

// call a method to log somethign to a file 
$worker->doSomething();

$logger1->debug('Now disable logging',array('group' => 'test'));

// disable all loggin with empty filter
$logger1->setGroupFilter('');  // log nothing

// call the methind and nothing will be logged 
$worker->doSomething();

// log only selected groups events
$logger1->setGroupFilter('test|C');  // now log only test and C group events

$logger1->debug('Now `test` and `C` groups to log',array('group' => 'test'));

$worker->doSomething();

$logger1->setGroupFilter('test|B');  // now log only test and C group events

$logger1->debug('Now `test` and `B` groups to log',array('group' => 'test'));

$worker->doSomething();

$logger1->debug('End of program! NOTE. Each log line has a time and process ID, sometimes this helps!',array('group' => 'test'));

echo 'Now look in your log file!';