<?php

namespace Gelembjuk\Logging;

/*
* This class helps to display uncatched error to a user
*/

abstract class ErrorScreen {
	public function __construct($options = array()) {
		if (isset($options['catchwarnings']) && $options['catchwarnings']) {
			set_error_handler(array($this, 'warningHandler'));
		}
		if (isset($options['catchfatals']) && $options['catchfatals']) {
			register_shutdown_function(array($this, 'fatalsHandler'));
		}
	}
	public function showError($exception) {
		$format = $this->getViewFormat();
		
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
	
	protected function showHTML($exception) {
		echo $this->getHTMLError($exception->getMessage());
		exit;
	}
	
	public function getHTMLError($message) {
		return '<div style="position: absolute; top:0; right:0; width:100%; height:100%; background: #ffffff;">'."\n".
			'<table align="center" style="width:65%; margin-top: 30px; background: #FFCC66;" cellpadding="10" cellspacing="1" border="0">'."\n".
			'<tr>'."\n".
				'<td style="background: #FFCC99;">Unexpected error!</td>'."\n".
			'</tr>'."\n".
			'<tr>'."\n".
				'<td style="padding-top: 15px; padding-bottom: 35px; background: #FFFF99;">'."\n".
					$message.'.<br><br>'."\n".
					'We are notified and will solve the problem as soon as possible.<br>'."\n".
				'</td>'."\n".
			'</tr>'."\n".
			'</table>'."\n".
			'</div>';
	}
	
	protected function showHTTP($exception) {
		header("HTTP/1.1 400 Unexpected error");
		$message = preg_replace('![^a-z 0-9_-]!','',$exception->getMessage());
		header("X-Error-Message: ".$message);
		exit;
	}
	
	protected function showJSON($exception) {
		$data = array('status'=>'error','message'=>$exception->getMessage());
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($data);
		exit;
	}
	
	protected function showXML($exception) {
	}
	public function warningHandler($errno, $errstr, $errfile, $errline) {
		
		$message='';
		$report = 0;
		
		switch ($errno) {
			case E_ERROR:
			case E_USER_ERROR:
				$message="FATAL ERROR! [$errno] $errstr .";
				$message.="  Line $errline in file $errfile. Aborting execution.";
				$report = 0;
				break;
			case E_WARNING:	
			case E_USER_WARNING:
				$message="WARNING! [$errno] $errstr .";
				$message.="  Line $errline in file $errfile. Continue execution.";
				$report = 0;
				break;
			case 100:
				$message="DB! $errstr .";
				$report = 0;
				break;	
			case E_USER_NOTICE:
				$message="NOTICE! [$errno] $errstr .";
				$message.="  Line $errline in file $errfile. Continue execution.";
				break;
			default:
				$message="UNKNOWN ERROR TYPE! [$errno] $errstr .";
				$message.="  Line $errline in file $errfile. Continue execution.";
				break;
		}
		
		if($report){
			$moredata='';
			
			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				$t = array();
				
				foreach ($_POST as $k => $v) {
					$t[] = $k . '=' . $v;
				}
				$moredata = preg_replace('![\n\r]!',"",implode('&',$t));
				
				if (strlen($moredata) > 100) {
					$moredata = substr($moredata,0,100);
				}
			}
			$message .= ' '.getmypid()."; ".basename($_SERVER['SCRIPT_FILENAME'])."; ".$_SERVER['REMOTE_ADDR']."; ".
				$_SERVER['REQUEST_METHOD']."; ".$_SERVER['QUERY_STRING']."; ".
				$moredata."; ".$_SERVER['HTTP_USER_AGENT']."; ".$_SERVER['HTTP_REFERER']."; ";
		}
		
		
		restore_error_handler();
		
		if($report){
			$this->logUncatchedError($message);
		}
		
		set_error_handler(array($this, 'warningHandler'));
	}
	public function fatalsHandler() {
		$errfile = "unknown file";
		$errstr  = "shutdown";
		$errno   = E_CORE_ERROR;
		$errline = 0;
	
		$error = error_get_last();
		if($error !== NULL) {
			if($error['type']==E_CORE_ERROR || $error['type']==E_ERROR){
				$message = 'Error: '.$error["message"].' in line: '.$error["line"].' in the file: '.$error["file"];
				$this->logUncatchedError($message);
								
				if($this->getViewFormat() == 'html'){
					$exception = new \Exception('Fatal error happened');
					$this->showHTML($exception);
				}
			}
		}
	}
	/*
	* In child classses this function can write info to a file
	*/
	private function logUncatchedError($message) {
		return true;
	}
	
	abstract protected function getViewFormat();
}