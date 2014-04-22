<?php
namespace Radical\Utility\Net\URL;

class Path {
	public $query;
	private $fragment;
	private $path = array();

	function __construct($path,$query,$fragment){
		if(empty($path)){
			$path = '/';
		}
		$path = (string)$path;
		if($path{0} == '/')
			$path = substr($path,1);
		
		if($path) $this->path = explode('/',$path);
		
		$this->query = $query;
		$this->fragment = $fragment;
	}
	
	/**
	 * @param field_type $query
	 */
	public function setQuery($query) {
		if(is_string($query)){
			parse_str($query);
		}
		$this->query = $query;
	}

	/**
	 * @param field_type $fragment
	 */
	public function setFragment($fragment) {
		$this->fragment = $fragment;
	}

	/**
	 * @param multitype: $path
	 */
	public function setPath($path) {
		if(is_string($path)){
			$path = explode('/',ltrim($path,'/'));
		}
		$this->path = $path;
	}

	/**
	 * @return the $query
	 */
	public function getQuery() {
		return $this->query;
	}

	/**
	 * @return string $fragment
	 */
	public function getFragment() {
		return $this->fragment;
	}

	/**
	 * @return array $path
	 */
	public function getPath($asString = false) {
		if($asString){
			return '/'.implode('/',$this->path);
		}
		return $this->path;
	}

	function __toString(){
		$url =  $this->getPath(true);
		if ($this->query) {
			$url .= '?' . http_build_query($this->query);
		}
		if ($this->fragment) {
			$url .= '#' . $this->fragment;
		}
		return $url;
	}
	
	function firstPathElement(){
		return isset($this->path[0])?$this->path[0]:null;
	}
	function removeFirstPathElement(){
		unset($this->path[0]);
		$this->path = array_values($this->path);
	}
	function queryString(){
		return $this->query_string;
	}
	
	/* Static construct */
	static function fromPath($path){
		$query = null;
		if(($pos = strrpos($path,'?'))!==false){
			parse_str(substr($path,$pos+1),$query);
			$path = substr($path,0,$pos);
		}
		
		$fragment = null;
		if(($pos = strrpos($path,'#'))!==false){
			$fragment = substr($path,$pos+1);
			$path = substr($path,0,$pos);
		}
		
		return static::fromSplit($path, $query, $fragment);
	}
	static function fromSplit($path,$query,$fragment){
		return new Path($path,$query,$fragment);
	}
}
