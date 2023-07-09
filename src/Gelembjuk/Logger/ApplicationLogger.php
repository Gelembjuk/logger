<?php

/**
* This trait helps to include logging functionality in your classes.
* It manipulates with logger based on Psr/Log class 
* 
* Use this trait to include centralised logging in different clases of your application with minimum coding
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

use \Psr\Log\LogLevel as LogLevel;

trait ApplicationLogger {
	// inherit from Psr\Log 
	use \Psr\Log\LoggerTrait;
	
	/**
	* This is logger object , instance of Psr\Log\AbstractLogger
	*
	* @var \Psr\Log\AbstractLogger
	*/
	protected $logger;
	
	/**
	 * log function . Forwards the call to same function of logger object
	 * Of checks if there is application property in a class where this trait is used
	 * if application is found then logger from application is used
	 * $this->application can be any object where same trait is used
	 * 
	 * @param int $level 
	 * @param string $message
	 * @param array $context
	 */
	public function log($level, $message, array $context = array()) {
		if (property_exists($this,'application') && is_object($this->application)) {
			$logger = $this->application->getLogger();
			if (is_object($logger)) {
				$logger->log($level, $message, $context);
			}
		} elseif (is_object($this->logger)) {
			$this->logger->log($level, $message, $context);
		}
	}
	
	/**
	 * Quick log function. It is shortcut for log() function
	 * There is no need to provide a context array, only a group to use for filtering
	 * 
	 * @param string $message Log message
	 * @param string $group Filtering froup or groups splitted with `|`
	 * @param int $level Log level number, LogLevel::ALERT by default
	 */
	protected function logQ($message,$group = '', $level = -1) 
	{
		if ($level === -1) {
			$level = LogLevel::ALERT;
		}
		$this->log($level, $message, array('group'=>$group));
	}
	/**
	 * Set logger, instance of \Psr\Log\AbstractLogger
	 * 
	 * @param \Psr\Log\AbstractLogger $logger
	 */
	public function setLogger($logger, $checkifexists = false) {
		if ($checkifexists && is_object($this->logger)) {
			return false;
		}
		
		$this->logger = $logger;
		
		return true;
	}
	/**
	 * Create new logger object, instance of FileLogger
	 * 
	 * @param array $options Options for new instance of FileLogger
	 */
	public function initLogger($options = array()) {
		$this->logger = new FileLogger($options);
	}
	/**
	 * Returns logger used in a class
	 * 
	 * @return \Psr\Log\AbstractLogger Logger object
	 */
	public function getLogger() {
		if (is_object($this->logger)) {
			return $this->logger;
		}
		if (property_exists($this,'application') && is_object($this->application)) {
			return $this->application->getLogger();
		}
		return null;
	}
}
