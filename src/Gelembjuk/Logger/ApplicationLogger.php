<?php

/**
 * This trait helps to include logging functionality in your classes
 */

namespace Gelembjuk\Logger;

trait ApplicationLogger {
	// inherit from Psr\Log 
	use \Psr\Log\LoggerTrait;
	
	protected $logger;
	
	public function log($level, $message, array $context = array()) {
		if (property_exists($this,'application') && is_object($this->application)) {
			$logger = $this->application->getLogger();
			if (is_object($logger)) {
				$logger->log($level, $message, $context);
			}
		} elseif (property_exists($this,'logger') && is_object($this->logger)) {
			$this->logger->log($level, $message, $context);
		}
	}
	
	protected function logQ($message,$group = '', $level = '') {
		$this->log($level, $message, array('group'=>$group));
	}
	public function setLogger($logger) {
		if (property_exists($this,'logger')) {
			$this->logger = $logger;
		}
	}
	public function initLogger($options = array()) {
		if (property_exists($this,'logger')) {
			$this->logger = new FileLogger($options);
		}
	}
	public function getLogger() {
		if (property_exists($this,'logger')) {
			return $this->logger;
		}
		if (property_exists($this,'application') && is_object($this->application)) {
			return $this->application->getLogger();
		}
		return null;
	}
}
