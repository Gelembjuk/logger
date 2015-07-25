<?php

/**
* FileLogger - logging class based on Psr\Log
* Saves log messages to a file with timestampt abd a process ID  
* Efective to use together with the ApplicationLogger trait 
*
* LICENSE: MIT
*
* @category   Logging
* @package    Gelembjuk/Logger
* @copyright  Copyright (c) 2015 Roman Gelembjuk. (http://gelembjuk.com)
* @version    1.0
* @link       https://github.com/Gelembjuk/logger
*/

namespace Gelembjuk\Logger;

use \Psr\Log\LogLevel;

class FileLogger extends \Psr\Log\AbstractLogger
{
	/**
	* Path to log file. If this is not correct path then logging will not work (without any error reporting)
	* If file exists and not writable then logging will nto work too without a notice
	*
	* @var string
	*/
	protected $logfile;
	
	/**
	* Logging filter. It is expected each log message will have a `group` key in a context array
	* This filter is checked against that value to know if to log a message this time or not
	* The value `all` means everything will be logged.
	* EMpty value for this attribute will mean nothing is logged
	*
	* @var string
	*/
	protected $groupfilter;
	
	/**
	* Switcher to log exceptions trace for always when a message is logged
	*
	* @var boolean
	*/
	protected $forcelogtrace = false;

	/**
	 * The constructor. 
	 * 
	 * Options:
	 *   logfile		- path to a log file
	 *   groupfilter 	- logging filter. With groups splitted with `|` symbol
	 *   forcelogtrace	- force to log trace in case if exception is logged
	 *   
	 * @param array $options The list of settings for an object
	 */
	public function __construct($options = array()) {

		if (isset($options['logfile'])) {
			$this->logfile = $options['logfile'];
		}
		
		$this->groupfilter = '';
		
		if (isset($options['groupfilter'])) {
			$this->groupfilter = $options['groupfilter'];
		}
		
		if (isset($options['forcelogtrace'])) {
			$this->forcelogtrace = $options['forcelogtrace'];
		}
	}
	/**
	 * Set group filter
	 * `all` means to log everything
	 * `` empty string means to log nothing
	 * Examples: A|B|C to group events where A or B or C is set in a group option in context
	 * 
	 * @param string $groupfilter
	 */
	public function setGroupFilter($groupfilter) {
		$this->groupfilter = $groupfilter;
	}
	/**
	 * Extra filter to implement in child classes
	 * 
	 * @param int $level This is message level number. Is inherited from Psr\Log
	 * @param array $context Same as for log function
	 * 
	 * @return boolean To know if log write allowed for this message or not
	 */
	protected function extraFilter($level,$context) {
		return true;
	}
	/**
	 * The log function. It is not usually called directly. Usually it is better to call other functions from Psr\Log
	 * like debug, error, alert etc
	 * 
	 * @param int $level This is message level number. Is inherited from Psr\Log
	 * @param string|array|object $message A log message. Can me array or object
	 * @param array $context Contains information about the message. A group of event. It is used to filter messages. Can have other information, like exception
	 * 
	 * @return boolean to know if a message was logged or not
	 */
	public function log($level, $message, array $context = array())
	{
		$group = '';
		
		if (isset($context['group'])) {
			$group = $context['group'];
		}
		
		if ($this->groupfilter == '' || $group == '' && $this->groupfilter != 'all') {
			return false;
		}
		
		$logallowed = false;
		
		if ($this->groupfilter == 'all') {
			$logallowed = true;
		} else {
			foreach (explode('|',$group) as $f) {
				if (preg_match('!\\|'.preg_quote($f).'\\|!','|'.$this->groupfilter.'|')) {
					// do log if any path of the filter is allowed to log
					$logallowed = true;
					break;
				}
			}
		}
		
		if (!$logallowed) {
			return false;
		}
		
		if (!$this->extraFilter($level,$context)) {
			return false;
		}
		
		// add log entry to the file
		
		if (is_array($message) || is_object($message)) {
			$message = print_r($message,true);
		}

		$logfilehandle = @fopen($this->logfile,'a');
		
		if (!$logfilehandle) {
			return false;
		}
		
		fwrite($logfilehandle, $this->formatLogLine($message) . "\n");
		
		if (isset($context['extrainfo'])) {
			// log this too
			fwrite($logfilehandle, "\t" . $context['extrainfo'] . "\n");
		}
		
		if ($level == LogLevel::ERROR || 
			$level == LogLevel::CRITICAL ||
			$level == LogLevel::EMERGENCY) {
			
			// maybe logging of trace is needed
			if (isset($context['exception']) && $context['exception'] instanceof \Exception) {
				if ($this->forcelogtrace || $context['forcelogtrace'] === true) {
					fwrite($logfilehandle, $context['exception']->getTraceAsString() . "\n");
				}
			}
		}

		fclose($logfilehandle);
		
		return true;
	}
	/**
	 * Format log message.
	 * 
	 * @param string $message
	 */
	protected function formatLogLine($message) {
		$line = '['.date('d-m-Y H:i:s').']: '.getmypid().': ';
		$line .= $message;
		return $line;
	}
}
