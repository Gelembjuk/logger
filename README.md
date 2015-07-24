## Logger
PHP Package for easy logging and error catching. 
**FileLogger** class based on Psr/Log helps to filter what to log. 
**ErrorScreen** class helps to catch PHP Warnings and Fatal errors and display correct error screen to a user.
**ApplicationLogger** is a trait to include logger in different classes very easy

### Installation
Using composer: [gelembjuk/logger](http://packagist.org/packages/gelembjuk/logger) ``` require: {"gelembjuk/logger": "dev-master"} ```

### Configuration

Configuration is done in run time with a constructor options (as hash argument)

**logfile** path to your log file (where to write logs)
**groupfilter** list of groups of events to log. `all` means log everything. Groups separated with **|** symbol

```php
$logger1 = new Gelembjuk\Logger\FileLogger(
	array(
		'logfile' => $logfile,  // path to your log file (where to write logs)
		'groupfilter' => 'group1|group2|group3'  // list of groups of events to log. `all` means log everything
	));

```

### Usage

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