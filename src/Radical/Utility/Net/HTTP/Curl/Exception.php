<?php
namespace Radical\Utility\Net\HTTP\Curl;

class Exception extends \Exception {
	function __construct($message,\Radical\Utility\Net\HTTP\Curl $curl){
		$message .= ' [URL: '.$curl[CURLOPT_URL].']';
		parent::__construct($message);
	}
}