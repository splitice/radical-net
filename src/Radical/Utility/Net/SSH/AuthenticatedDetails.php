<?php
namespace Radical\Utility\Net\SSH;

class AuthenticatedDetails {
	private $method;
	private $arguments;
	
	function __construct($method,array $arguments = array()){
		$this->method = $method;
		$this->arguments = $arguments;
	}
	
	function execute(Authenticate $object){
		call_user_func_array(array($object,$this->method), $this->arguments);
	}
}