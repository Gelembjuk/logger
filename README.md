## Logger

PHP Package for easy logging and error catching. 
**FileLogger** class based on Psr/Log helps to filter what to log. 
**ErrorScreen** class helps to catch PHP Warnings and Fatal errors and display correct error screen to a user.
**ApplicationLogger** is a trait to include logger in different classes very easy

### Installation
Using composer: [gelembjuk/logger](http://packagist.org/packages/gelembjuk/logger) ``` require: {"gelembjuk/logger": "dev-master"} ```

### Configuration

Configuration is done in run time with a constructor options (as hash argument)

Configure **FileLogger** 

**logfile** path to your log file (where to write logs)
**groupfilter** list of groups of events to log. `all` means log everything. Groups separated with **|** symbol

```php
$logger1 = new Gelembjuk\Logger\FileLogger(
	array(
		'logfile' => $logfile,  // path to your log file (where to write logs)
		'groupfilter' => 'group1|group2|group3'  // list of groups of events to log. `all` means log everything
	));

```

Configure **ErrorScreen** 

**catchwarnings**	- (true|false) . If true then user error handler is set to catch warnings

**catchfatals**		- (true|false) . If true then fatal errors are catched. Use to log error and show `normal` error screen

**showwarningmessage**	- (true|false) . If true then error screen is displayed in case of warning. If is false then error is only logged 

**showfatalmessage** 	- (true|false) . Display error screen for fatal errors. If false then only log is dine. User will see `standard` fatal error in this case

**viewformat**		- set vaue for the `viewformat` variable. Possible values: html, json, xml, http . html is default value

**showtrace**		- (true|false). Switcher to know if to show error trace for a user as part of error screen

**commonerrormessage**	- string Common error message to show to a user when error happens

**logger**		- Object of FileLogger class

**loggeroptions** 	- Options to create new FileLogger object


```php

$errors = new Gelembjuk\Logger\ErrorScreen(
		array(
			'logger' => $logger1 /*create before*/,
			'viewformat' => 'html',
			'catchwarnings' => true,
			'catchfatals' => true,
			'showfatalmessage' => true,
			'commonerrormessage' => 'Sorry, somethign went wrong. We will solve ASAP'
		)
	);


```

### Usage

## FileLogger

```php

require '../vendor/autoload.php';

$logger1 = new Gelembjuk\Logger\FileLogger(
	array(
		'logfile' => '/tmp/log.txt',
		'groupfilter' => 'all' // log everything this time
	));

// do test log write
$logger1->debug('Test log',array('group' => 'test'));

$logger1->setGroupFilter('group1|group2'); // after this only group1 and group2 events are logged

$logger1->debug('This message will not be in logs as `test` is out of filter',array('group' => 'test'));

```
## ApplicationLogger trait

```php

require '../vendor/autoload.php';

class A {
}

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

class C {
	use Gelembjuk\Logger\ApplicationLogger;
	
	public function __construct($logger) {
		$this->setLogger($logger);

		$this->logQ('C object create','construct|C');
	}

	public function doOtherThing() {
		$this->logQ('oOtherThing() in C','C');
	}
}

$b = new B($logger1); // $logger1 is instance of FileLogger
$c = new C($logger1);

$b->doSomething();
$c->doOtherThing();

```

## ErrorScreen

```php

require '../vendor/autoload.php';

$errors = new Gelembjuk\Logger\ErrorScreen(
		array(
			'logger' => $logger1 /*created before*/,
			'viewformat' => 'html',
			'catchwarnings' => true,
			'catchfatals' => true,
			'showfatalmessage' => true,
			'commonerrormessage' => 'Sorry, somethign went wrong. We will solve ASAP'
		)
	);

// to catch exceptions on the top level of the app
try {
	// do something 
	
} catch (Exception $e) {
	$errors->processError($e);
}

// presume there was no exception
// now catch warning

// warning is raised and catched in errors object
// error message displayed to a user
include('not_existent_file.php'); 	

```

### Author

Roman Gelembjuk (@gelembjuk)

