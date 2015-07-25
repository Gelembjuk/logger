<?php 

/**
 * Example. Usage of ApplicationLogger trait to simplify usage of FileLogger object in your classes
 * The example demonstrates how to use the gelembjuk/logger package to have configurable logging
 * 
 * This example is part of gelembjuk/logger package by Roman Gelembjuk (@gelembjuk)
 */

// path to your composer autoloader
require ('../vendor/autoload.php');

/**
 * example class 1. It demostrates how logger can be mapped to one of classes 
 * and other classes will use same logger
 */
class Application {
	// include the trait
	use Gelembjuk\Logger\ApplicationLogger;
	
	public function __construct($logger = null) {
		$this->setLogger($logger);
		
		// if logger is not set or null then there will not be any error
		$this->debug('Application object created',array('group' => 'construct|application'));
		
		// same action with short call 
		// logQ($logmessage,$group)
		$this->logQ('Application object created (2-nd log)','construct|application');
		
		// NOTE. if $logger is null then 2 previous log records will not be done
		// but there will not be any error message.
	}

	public function doSomething() {
		$this->logQ('doSomething() in application','application');
	}
}

/**
 * Just base classe for other example
 */
class A {
}
/**
 * Use the trait in other class . Again, it saves your time on describing logging functions
 */
class B extends A {
	// include the trait to have logging functionality in this class
	use Gelembjuk\Logger\ApplicationLogger;
	
	public function __construct($logger) {
		$this->setLogger($logger);

		$this->logQ('B object create','construct|B');
	}

	public function doSomething() {
		$this->logQ('doSomething() in B','B');
	}
}

/**
 * Use the trait to log with logger from other object where a logger is already set
 * In this case a class must have an $application property
 */
class C {
	// in this case we will use $this->application property to access logger
	// from other object (we presume it uses this trite too)
	use Gelembjuk\Logger\ApplicationLogger;
	
	protected $application;
	
	public function __construct($application) {
		$this->application = $application;

		// this call will call $this->application->logQ(...) method
		$this->logQ('C object create','construct|C');
	}
	public function doSomething() {
		$this->logQ('doSomething() in C','C');
	}
}

// ========================== TESTS ======================
// ****** TEST 1 ******

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

// pass created logger as argument
// after object created and the method called you will see logs in your log file
// at this moment all logs are written
$application1 = new Application($logger1);
$application1->doSomething();

// now wee loggeing from the class B
$b1 = new B($logger1);
$b1->doSomething();

// this shows how to use logger from other class where the trite is used
// object of this class must be set to $this->application property
$c1 = new C($application1);
$c1->doSomething();

unset($application1);
unset($b1);
unset($c1);

// ****** TEST 2 ******
// don't use existent logger, but create it inside
$application2 = new Application(null);

// we set filtering to `construct`. So only logs from constructors will be saved
$application2->initLogger(array('logfile' => $logfile,'groupfilter' => 'construct' ));
$application2->doSomething();

// get logger object reference from application
$b2 = new B($application2->getLogger());
$b2->doSomething();

// this usage is same as above. Doesn't mapper how application created logger
// this class will just reuse it
$c2 = new C($application2);
$c2->doSomething();

$logger = $application2->getLogger();

echo "Now see to your log file!";
