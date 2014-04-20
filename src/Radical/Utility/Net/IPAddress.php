<?php
namespace Radical\Utility\Net;

use Radical\Basic\Cryptography\CRC32;

class IPAddress {
	private $ip;
	
	function __construct($ip){
		if($ip instanceof self){
			$ip = $ip->getIp();
		}
		if(!is_string($ip)){
			throw new \Exception('$ip must be a string not '.gettype($ip));
		}
		$this->ip = $ip;
	}
	
	/**
	 * @return the $ip
	 */
	public function getIp() {
		return $this->ip;
	}
	
	private $_version;
	public function getVersion(){
		if($this->_version) return $this->_version;
		if(filter_var ( $this->ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 )){
			$this->_version = 6;
		}elseif(filter_var ( $this->ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 )){
			$this->_version = 4;
		}
		return $this->_version;
	}
	
	function isValid(){
		return filter_var ( $this->ip, FILTER_VALIDATE_IP );
	}
	
	function hash(){
		return CRC32::Hash($this->ip);
	}
	
	function reverse(){
		$e = array_reverse(explode('.',$this->ip));
		return new static(implode('.',$e));
	}
	
	function ping($timeout = 1) {
		/* ICMP ping packet with a pre-calculated checksum */
		$package = "\x08\x00\x7d\x4b\x00\x00\x00\x00PingHost";
		$socket  = socket_create(AF_INET, SOCK_RAW, 1);
		socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array('sec' => $timeout, 'usec' => 0));
		socket_connect($socket, $this->ip, null);
	
		$ts = microtime(true);
		socket_send($socket, $package, strLen($package), 0);
		
		if (socket_read($socket, 255))
			$result = microtime(true) - $ts;
		else
			$result = false;
		
		socket_close($socket);
	
		return $result;
	}

	function __toString(){
		return $this->ip;
	}
	
	function toEscaped(){
		return \Radical\DB::E($this->ip);
	}
}