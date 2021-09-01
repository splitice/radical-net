<?php
namespace Radical\Utility\Net;

class CIDR {
	protected $cidr;
	
	function __construct($cidr){
		$this->cidr = $cidr;
	}
	
	function mask(){
		list ($net, $mask) = explode ("/", $this->cidr);

		$ip_mask = ~((1 << (32 - $mask)) - 1);
		
		return $ip_mask;
	}

	function address(){
		list ($net) = explode ("/", $this->cidr);

		return $net;
	}

	function cidr(){
		list ($_,$cidr) = explode ("/", $this->cidr);

		return $cidr;
	}
	
	function contains($ip){
		$ip_mask = $this->mask();

        list ($net, $mask) = explode("/", $this->cidr);
        $ip_range = ip2long($net);
		
		$ip_ip = ip2long ($ip);
		
		return ($ip_ip & $ip_mask) == ($ip_range & $ip_mask);
	}
	
	function network(){
		list ($net, $mask) = explode ("/", $this->cidr);
		
		$ip_net = ip2long ($net);
		$ip_mask = ~((1 << (32 - $mask)) - 1);
		
		$ip_ip_net = $ip_net & $ip_mask;
		
		return long2ip($ip_ip_net);
	}
	
	function validate(){
		return preg_match('`^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])(\/(\d|[1-2]\d|3[0-2]))$`m', $this->cidr);
	}
	
	function range($long = false){
		list($start,$end) = CIDRRange::cidrToRange($this->cidr);
		$ret = array();
		for($i = ip2long($start), $f = ip2long($end); $i <= $f; $i++){
			if($long){
				$ret[] = $i;
			} else {
				$ret[] = long2ip($i);
			}
		}
		return $ret;
	}
	
	function __toString(){
		return (string)$this->cidr;
	}
}