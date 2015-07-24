<?php

namespace Gelembjuk\Logger;

use \Psr\Log\LogLevel;

class FileLogger extends \Psr\Log\AbstractLogger
{
	protected $logfile;
	protected $groupfilter;

	public function __construct($options = array()) {

		if (isset($options['logfile'])) {
			$this->logfile = $options['logfile'];
		}
		
		$this->groupfilter = '';
		
		if (isset($options['groupfilter'])) {
			$this->groupfilter = $options['groupfilter'];
		}
	}
	public function setGroupFilter($groupfilter) {
		$this->groupfilter = $groupfilter;
	}
	protected function extraFilter($level,$context) {
		return true;
	}
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

		fclose($logfilehandle);
		
		return true;
	}
	protected function formatLogLine($message) {
		$line = '['.date('d-m-Y H:i:s').']: '.getmypid().': ';
		$line .= $message;
		return $line;
	}
}
