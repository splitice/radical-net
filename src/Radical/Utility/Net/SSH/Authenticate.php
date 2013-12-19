<?php
namespace Radical\Utility\Net\SSH;

class Authenticate {
	public $ssh;
	/**
	 * @var AuthenticatedDetails
	 */
	private $auth;
	
	function __construct($ssh){
		$this->ssh = $ssh;
	}
	
	function password($username,$password){
		if($this->ssh && is_resource($this->ssh)){
			$this->auth = new AuthenticatedDetails(__FUNCTION__,array($username,$password));
			return ssh2_auth_password($this->ssh,$username,$password);
		}
	}
	
	function Authenticate(Authenticate $auth){
		$auth->Execute($this);
	}
	
	function execute(Authenticate $object = null){
		if(!$this->auth) return false;
		if($object == null) $object = $this;
		$this->auth->Execute($object);
		return true;
	}
}