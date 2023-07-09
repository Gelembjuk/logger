<?php

/**
* ErrorScreen - error logging and display class.
* Can catch warning and fatal error to display a user frendly messages
* Can be used to log exceptions with a trace.
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

	/**
	* Switcher to control if to show warning errors to a user
	*
	* @var boolean
	*/
	protected $showwarningmessage = false;
	
	/**
	* Switcher to control if to show error screen to a user in case of fatal error
	*
	* @var boolean
	*/
	protected $showfatalmessage = true; // user will still have a way to see it
	
	/**
	* Error screen display format. Possible values: html, json, xml, http
	*
	* @var string
	*/
	protected $viewformat = 'html';
	
	/**
	* Switcher to control if to show error trace as part of error screen. Use only for dev mode
	*
	* @var boolean
	*/
	protected $showtrace = false;
	
	/**
	* This is the message to show for a user when error happens.
	* If this is empty string then acrual error message will be shown.
	*
	* @var string
	*/
	protected $commonerrormessage = '';
	
	/**
	* Internal counter of errors to stop when too much errors.
	*
	* @var boolean
	*/
	protected $countoferrors = 0;
	
	/**
	* To know if exception handler was already set
	*
	* @var boolean
	*/
	protected $exceptionhandlerset = false;
	/**
	* To know if warning handler was already set
	*
	* @var boolean
	*/
	protected $warninghandlerset = false;
	/**
	* To know if fatal handler was already set
	*
	* @var boolean
	*/
	protected $fatalhandlerset = false;
	
	/**
	 * The constructor. 
	 * 
	 * Options:
	 *   catchwarnings	- (true|false) . If true then user error handler is set to catch warnings
	 *   catchfatals	- (true|false) . If true then fatal errors are catched. Use to log error and show `normal` error screen
	 *   catchexceptions	- (true|false) . If true then uncatched exceptions will be catched by the object
	 *   showwarningmessage	- (true|false) . If true then error screen is displayed in case of warning. If is false then error is only logged 
	 *   showfatalmessage 	- (true|false) . Display error screen for fatal errors. If false then only log is dine. User will see `standard` fatal error in this case
	 *   viewformat		- set vaue for the `viewformat` variable. Possible values: html, json, xml, http . html is default value
	 *   showtrace		- (true|false). Switcher to know if to show error trace for a user as part of error screen
	 *   commonerrormessage	- string Common error message to show to a user when error happens
	 *   logger		- Object of logger class
	 *   loggeroptions 	- Options to create new FileLogger object
	 * 
	 * @param array $options The list of settings for an object
	 */
	public function __construct($options = array()) {
		if (isset($options['catchwarnings']) && $options['catchwarnings']) {
			$this->setCatchWarnings(true);
		}

		if (isset($options['catchfatals']) && $options['catchfatals']) {
			$this->setCatchFatals(true);
		}
		
		if (isset($options['catchexceptions']) && $options['catchexceptions']) {
			$this->setCatchExceptions(true);
		}

		if (isset($options['showwarningmessage'])) {
			$this->setShowWarningError($options['showwarningmessage']);
		}

		if (isset($options['showfatalmessage'])) {
			$this->setShowFatalError($options['showfatalmessage']);
		}

		if (isset($options['viewformat'])) {
			$this->setViewFormat($options['viewformat']);
		}

		if (isset($options['showtrace'])) {
			$this->setShowTrace($options['showtrace']);
		}
		
		if (isset($options['commonerrormessage'])) {
			$this->setCommonErrorMessage($options['commonerrormessage']);
		}
		
		if (isset($options['logger'])) {
			// setLogger is from the ApplicationLogger trait
			$this->setLogger($options['logger']);
		} elseif (isset($options['loggeroptions'])) {
			// create new logger
			// initLogger is from the ApplicationLogger trait
			$this->initLogger($options['loggeroptions']);
		}
	}
	/**
	 * Sets/Unsets exeption catch function. It will work only for uncatched exceptions
	 * This function will work in case if some exception was not catched
	 * with any user's try {} catch and is ready to dispay Fatal error to a user
	 * So fatal errir will not be displayed because this catch will action
	 * 
	 * @param boolean $catchexceptions (true|false) to set or unset exception catch
	 * 
	 */
	public function setCatchExceptions($catchexceptions = true) {
		// check $this->exceptionhandlerset to know if handler was set or not
		// so operation is not repeated
		if ($catchexceptions && !$this->exceptionhandlerset) {
			set_exception_handler(array($this, 'exceptionsHandler'));
			$this->exceptionhandlerset = true;
		} elseif ($this->exceptionhandlerset) {
			// there can be problems if an application sets/unsets exception handler in other places
			// for now no solution for this yet
			restore_exception_handler();
			$this->exceptionhandlerset = false;
		}
	}
	/**
	 * Sets/Unsets user error catch function. It is used to catch warnings
	 * 
	 * @param boolean $catchwarnings (true|false) to set or unset warnings catch
	 * 
	 */
	public function setCatchWarnings($catchwarnings = true) {
		if ($catchwarnings && !$this->warninghandlerset) {
			set_error_handler(array($this, 'warningHandler'));
			$this->warninghandlerset = true;
		} elseif ($this->warninghandlerset) {
			restore_error_handler();
			$this->warninghandlerset = false;
		}
	}
	/**
	 * Sets/Unsets fatal error catch function. It is used to catch fatal errors
	 * 
	 * @param boolean $catchfatals (true|false) to set or unset fatals catch
	 * 
	 */
	public function setCatchFatals($catchfatals = true) {
		if ($catchfatals && !$this->fatalhandlerset) {
			register_shutdown_function(array($this, 'fatalsHandler'));
			$this->fatalhandlerset = true;
		} elseif($this->fatalhandlerset) {
			$this->fatalhandlerset = false;
		}
	}
	/**
	 * Sets/Unsets display of warning error screen to a user 
	 * 
	 * @param boolean $showwarning (true|false) to set or unset display error screen to a user
	 * 
	 */
	public function setShowWarningError($showwarning = false) {
		$this->showwarningmessage = $showwarning;
	}
	/**
	 * Sets/Unsets display of fatal error screen to a user 
	 * 
	 * @param boolean $showfatal (true|false) to set or unset display fatal error screen to a user
	 * 
	 */
	public function setShowFatalError($showfatal = false) {
		$this->showfatalmessage = $showfatal;
	}
	/**
	 * The function to get view format for error screen.
	 * This function is created to change it easy in child classes if view format has some specific rules
	 * 
	 * @return string View format
	 * 
	 */
	protected function getViewFormat() {
		return $this->viewformat;
	}
	/**
	 * Sets view format .
	 * 
	 * @param string $format possible values: html, json, xml, http
	 * 
	 */
	public function setViewFormat($format) {
		$this->viewformat = $format;
	}
	
	/**
	 * Sets common error message
	 * 
	 * @param string $message Error message to show to a user. If empty then actual error will be displayed
	 * 
	 */
	public function setCommonErrorMessage($message) {
		$this->commonerrormessage = $message;
	}
	/**
	 * Sets show trace flag On or Off
	 * 
	 * @param boolean $showtrace true or false to show trace
	 * 
	 */
	public function setShowTrace($showtrace) {
		$this->showtrace = $showtrace;
	}
	/**
	 * The function logs an error and desides what to show to a user
	 * It can be called from outside or from internal methods in case of warning or fatal error
	 * 
	 * @param Exception $exception An exception to log and display
	 * @param string $type Type of exception error. Possible values: exception, warning, fatal
	 * 
	 * @return boolean
	 */
	public function processError($exception, $type = 'exception', $logonly = false, $forcelogtrace = false) {
		// log information 
		// error function is from the trait . It will log something if there is logger inited
		
		$this->countoferrors++;
		
		if ($this->countoferrors > 500) {
			// if there are so many errors then somethign went wrong. better to stop
			// as it can be some wrong loop 
			return false;
		}
		
		$this->error($exception->getMessage(),
			// set group and exception as context for logger. Logger will decide what to write to a file
			array(
				'group' => 'error|'.$type,
				'exception' => $exception, 
				'forcelogtrace' => $forcelogtrace,
				'extrainfo' => 'Request info: '.$this->getRequestInformation()));
		
		$this->actionOnError($exception,$type);

		// this condition can be used to log information about some exception but don't stop the app
		if ($logonly) {
			return true;
		}

		if (!$this->showwarningmessage && $type == 'warning') {
			// show nothing to a user in case of warning
			return true;
		}
		if (!$this->showfatalmessage && $type == 'fatal') {
			// show nothing to a user in case of fatal error
			return true;
		}

		// decide hot to show to a user based on requested format
		$format = $this->getViewFormat();

		if ($format != 'html' && $type == 'fatal'){
			// for fatal error no sense to display somethign if this is not html request
			return true;
		}
		
		// display error screen based on the format
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
		
		// if error message is displayed then no sence to do somethig else.
		die();
	}

	/**
	 * To do something else with error message in a child class.
	 * For example, send email with alert to admin about error
	 */
	protected function actionOnError($exception,$type) {
		return true;
	}
	
	/**
	 * Display error in HTML format
	 * 
	 * @param Exception $exception Exception to show message from
	 */
	protected function showHTML($exception) {
		echo $this->getHTMLError($exception);
	}
	/**
	 * Build HTML to display error
	 * 
	 * @param Exception $exception Exception to show message from
	 * 
	 * @return string HTML of the error screen
	 */
	public function getHTMLError($exception) {
		$html = '<div style="position: absolute; top:0; right:0; width:100%; height:100%; background: #ffffff;">'."\n".
			'<table align="center" style="width:65%; margin-top: 30px; background: #FFCC66;" cellpadding="10" cellspacing="1" border="0">'."\n".
			'<tr>'."\n".
				'<td style="background: #FFCC99;">Unexpected error!</td>'."\n".
			'</tr>'."\n".
			'<tr>'."\n".
				'<td style="padding-top: 15px; padding-bottom: 35px; background: #FFFF99;">'."\n".
					$this->getMessageForUser($exception).'.<br><br>'."\n".
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
	/**
	 * Return to user only headers with an error as part of a header
	 */
	protected function showHTTP($exception) {
		header("HTTP/1.1 400 Unexpected error");
		$message = preg_replace('![^a-z 0-9_-]!','',$this->getMessageForUser($exception));
		header("X-Error-Message: ".$message);
	}
	/**
	 * Display error in JSON format
	 * 
	 * @param Exception $exception Exception to show message from
	 */
	protected function showJSON($exception) {
		$data = array('status'=>'error','message'=>$this->getMessageForUser($exception));
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($data);
	}
	/**
	 * Display error in XML format
	 * 
	 * @param Exception $exception Exception to show message from
	 */
	protected function showXML($exception) {
		$endline = "\r\n";
		$xml = '<?xml version="1.0" encoding="UTF-8"?>'.$endline.
				'<response>'.$endline.
					'<status>error</status>'.$endline.
					'<message>'.htmlspecialchars($this->getMessageForUser($exception)).'</message>'.$endline.
				'</response>';

		header('Content-Type: application/xml; charset=utf-8');
		echo $xml;
	}
	/**
	 * To catch warnings. Notices are ignored in this function.
	 * This is the callback for a function set_error_handler
	 */
	public function warningHandler($errno, $errstr, $errfile, $errline) {
		if ($errno != E_WARNING && $errno != E_USER_WARNING) {
			return false;
		}
		
		$message = "WARNING! [$errno] $errstr. Line $errline in file $errfile. ";
		
		// because some more problems can happen during logging or display
		// better to disable this handler
		$this->setCatchWarnings(false);
		
		$exception = new \Exception($message);

		$this->processError($exception,'warning');		
		
		$this->setCatchWarnings(true);
	}
	/**
	 * Catch Fatal error. Note. Fatal error message will be displayed anyway. Not possible to hide it at all
	 * But we can use the HTML+CSS trick to hide it from a user if this was html reuest (no JSON or XML)
	 * This is the callback for the function register_shutdown_function
	 */
	public function fatalsHandler() {
		if (!$this->fatalhandlerset) {
			// do nothing. fatals handler was canceled
			// no way to remove settings of this callback
			return false;
		}
		$error = error_get_last();

		if($error !== NULL) {
			if ($error['type'] == E_CORE_ERROR || $error['type'] == E_ERROR) {

				$message = 'Fatal Error: '.$error["message"].' in line: '.$error["line"].' in the file: '.$error["file"];

				$exception = new \Exception($message);

				$this->processError($exception,'fatal');
			}
		}
		return true;
	}
	/**
	 * Catch exception not catched by any try {} catch
	 * This is the callback for the function set_exception_handler
	 */
	public function exceptionsHandler($e) {
		$this->processError($e);
		return true;
	}
	/**
	 * Collect request information to log it. This can help to solve a problem later.
	 * 
	 * @return string Requets information, for logging
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
			basename($_SERVER['SCRIPT_FILENAME'] ?? '') . "; " .
			($_SERVER['REMOTE_ADDR'] ?? ''). "; " .
			($_SERVER['REQUEST_METHOD'] ?? ''). "; " .
			($_SERVER['QUERY_STRING'] ?? '') . "; " .
			$postdata . "; " .
			($_SERVER['HTTP_USER_AGENT'] ?? '') . "; " .
			($_SERVER['HTTP_REFERER'] ?? '');
		return $requestinfo;
	}
	/**
	 * The function decides what message to show to a user. Real error or some common text
	 * 
	 * @return string Text message
	 */
	protected function getMessageForUser($exception) {
		if ($this->commonerrormessage != '') {
			return $this->commonerrormessage;
		}
		if (is_a($exception, 'ParseError')) {
            return $exception->getMessage().'. Line '.$exception->getLine().' on '.$exception->getFile();
		}
		
		return $exception->getMessage();
	}
}
