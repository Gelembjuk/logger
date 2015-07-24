<?php

namespace Gelembjuk\Logger;

/*
* This trait logs data using the application property in an object
*/

trait ApplicationLogger {
	use \Psr\Log\LoggerTrait;
	
	public function log($level, $message, array $context = array()) {
		if (property_exists($this,'application') && is_object($this->application)) {
			$this->application->getLogger()->log($level, $message, $context);
		} elseif (property_exists($this,'logger') && is_object($this->logger)) {
			$this->logger->log($level, $message, $context);
		} elseif (property_exists($this,'logger') && method_exists($this,'getLogger')) {
			$this->getLogger()->log($level, $message, $context);
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