<?php

/**
* ErrorScreen - error logging and display class.
* Can catch warning and fatal error to displau user frently messages
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

class ErrorScreen {
	// inherit logging functions
	use ApplicationLogger;

	protected $hidewarningmessage = true;
	protected $hidefatalmessage = true; // user will still have a way to see it
	protected $viewformat = 'html';
	protected $showtrace = false;
	
	public function __construct($options = array()) {
		if (isset($options['catchwarnings']) && $options['catchwarnings']) {
			set_error_handler(array($this, 'warningHandler'));
		}

		if (isset($options['catchfatals']) && $options['catchfatals']) {
			register_shutdown_function(array($this, 'fatalsHandler'));
		}

		if (isset($options['hidewarningmessage'])) {
			$this->hidewarningmessage = $options['hidewarningmessage'];
		}

		if (isset($options['hidefatalmessage'])) {
			$this->hidefatalmessage = $options['hidefatalmessage'];
		}

		if (isset($options['viewformat'])) {
			$this->viewformat = $options['viewformat'];
		}

		if (isset($options['showtrace'])) {
			$this->showtrace = $options['showtrace'];
		}
	}
	public function processError($exception,$type = 'exception') {
		// log information 
		// error function is from the trait . It will log something if there is logger inited
		$this->error($exception->getMessage(),
			// set group and exception as context for logger. Logger will decide what to write to a file
			array('group' => 'error|'.$type,'exception' => $exception));
		
		$this->actionOnError($exception,$type);

		if ($this->hidewarningmessage && $type == 'warning') {
			// how nothing to a user in case of warning
			return true;
		}
		if ($this->hidefatalmessage && $type == 'fatal') {
			// how nothing to a user in case of warning
			return true;
		}

		// decide hot to show to a user based on requested format
		$format = $this->getViewFormat();

		if ($format != 'html' && $type == 'fatal'){
			// for fatal error no sense to display somethign if this is not html request
			return true;
		}
		
		switch ($format) {
			case 'json' :
				$this->showJSON($exception);
				break;
			case 'xml':
				$this->showXML($exception);
				break;
			case 'http':
				$this->showHTTP($exception);
				break;
			default:
				$this->showHTML($exception);
		}
	}

	// to do something else with error message in child class
	protected function actionOnError($exception,$type) {
		return true;
	}
	
	/*
	* Display error in HTML format
	*/
	protected function showHTML($exception) {
		echo $this->getHTMLError($exception);
		exit;
	}
	/*
	* Build HTML to display error
	*/
	public function getHTMLError($exception) {
		$html = '<div style="position: absolute; top:0; right:0; width:100%; height:100%; background: #ffffff;">'."\n".
			'<table align="center" style="width:65%; margin-top: 30px; background: #FFCC66;" cellpadding="10" cellspacing="1" border="0">'."\n".
			'<tr>'."\n".
				'<td style="background: #FFCC99;">Unexpected error!</td>'."\n".
			'</tr>'."\n".
			'<tr>'."\n".
				'<td style="padding-top: 15px; padding-bottom: 35px; background: #FFFF99;">'."\n".
					$exception->getMessage().'.<br><br>'."\n".
					'We are notified and will solve the problem as soon as possible.<br>'."\n".
				'</td>'."\n".
			'</tr>'."\n";
		
		if ($this->showtrace) {
			$html .= '<tr>'."\n".
					'<td style="padding-top: 15px; padding-bottom: 35px; background: #FFFF99; white-space: pre ;">'."\n".
						$exception->getTraceAsString()."\n".
					'</td>'."\n".
				'</tr>'."\n";
		}

		$html .= '</table>'."\n".
			'</div>';
		return $html;
	}
	/*
	* Return only headers with an error as part of a header
	*/
	protected function showHTTP($exception) {
		header("HTTP/1.1 400 Unexpected error");
		$message = preg_replace('![^a-z 0-9_-]!','',$exception->getMessage());
		header("X-Error-Message: ".$message);
		exit;
	}
	/*
	* Display as JSON document
	*/
	protected function showJSON($exception) {
		$data = array('status'=>'error','message'=>$exception->getMessage());
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($data);
		exit;
	}
	/*
	* Display as XML document
	*/
	protected function showXML($exception) {
		$endline = "\r\n";
		$xml = '<?xml version="1.0" encoding="UTF-8"?>'.$endline.
				'<response>'.$endline.
					'<status>error</status>'.$endline.
					'<message>'.htmlspecialchars($exception->getMessage()).'</message>'.$endline.
				'</response>';

		header('Content-Type: application/xml; charset=utf-8');
		echo $xml;
		exit;
	}
	/*
	* To catch warnings. Notices are ignored in this function.
	*/
	public function warningHandler($errno, $errstr, $errfile, $errline) {
		if ($errno != E_WARNING && $errno != E_USER_WARNING) {
			return false;
		}
		
		$message = "WARNING! [$errno] $errstr .";
		$message .= "  Line $errline in file $errfile. Continue execution.";
		$message .= $this->getRequestInformation();
		
		// because some more problems can happen during logging or display
		// better to disable this handler
		restore_error_handler();
		
		$exception = new \Exception($message);

		$this->processError($exception,'warning');		
		
		set_error_handler(array($this, 'warningHandler'));
	}
	/*
	* Catch Fatal error. Note. Fatal error message will be displayed anyway. Not possible to hide it at all
	* But we can use the HTML+CSS trick to hide it from a user if this was html reuest (no JSON or XML)
	*/
	public function fatalsHandler() {
		$error = error_get_last();

		if($error !== NULL) {
			if ($error['type'] == E_CORE_ERROR || $error['type'] == E_ERROR) {

				$message = 'Fatal Error: '.$error["message"].' in line: '.$error["line"].' in the file: '.$error["file"];
				$message .= $this->getRequestInformation();

				$exception = new \Exception($message);

				$this->processError($exception,'fatal');
			}
		}
		return true;
	}
	/*
	* Collect request information to log it. This can help to solve a problem later.
	*/
	protected function getRequestInformation() {
		$postdata = '';
			
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			// build a request string
			$t = array();
			
			foreach ($_POST as $k => $v) {
				$t[] = $k . '=' . $v;
			}

			// make a one line string
			$postdata = preg_replace('![\n\r]!',"",implode('&',$t));
			
			// truncate if too long
			if (strlen($postdata) > 250) {
				$postdata = substr($postdata,0,250);
			}
		}

		// build a string with information about a request
		$requestinfo = ' '.getmypid() . "; " .
			basename($_SERVER['SCRIPT_FILENAME']) . "; " .
			$_SERVER['REMOTE_ADDR'] . "; " .
			$_SERVER['REQUEST_METHOD'] . "; " .
			$_SERVER['QUERY_STRING'] . "; " .
			$postdata . "; " .
			$_SERVER['HTTP_USER_AGENT'] . "; " .
			$_SERVER['HTTP_REFERER'];
		return $requestinfo;
	}
	
	protected function getViewFormat() {
		return $this->viewformat;
	}
	public function setViewFormat($format) {
		$this->viewformat = $format;
	}
}