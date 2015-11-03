<?php
namespace Radical\Utility\Net;
use Radical\Basic\String\UTF8;

class URL {
	protected $scheme;
	protected $host;
	protected $port = 80;
	/**
	 * @var URL\Path
	 */
	protected $path;
	
	static private function _SCHEME_VALID($url) {
		$scheme = strtolower ( ( string ) parse_url ( $url, PHP_URL_SCHEME ) );
		if ($scheme == 'http' || $scheme == 'https' || $scheme == 'ftp' || $scheme == 'ftps') {
			return true;
		}
		return false;
	}
	function __construct($data) {
		$this->scheme = empty($data ['scheme'])? null : $data ['scheme'];
		$this->host = empty($data ['host'])? null : new URL\Host($data ['host']);
		if(isset($data ['port']))
			$this->port = (int)$data ['port'];
		
		if($this->port == 0)
			$this->port = 80;
		
		$path = isset($data ['path'])?$data ['path']:'/';
		$query = isset($data ['query'])?$data ['query']:null;
		if($query){
			parse_str($query,$query);
		}
		
		$fragment = isset($data ['fragment'])?$data ['fragment']:null;
		
		$this->path = URL\Path::fromSplit($path,$query,$fragment);
	}
	
	function isSubDomainOf($domain) {
		if (! ($domain instanceof URL)) {
			$domain = self::fromURL ( $domain );
		}
		$domain_host = $domain->getHost ();
		$len = strlen ( $domain_host );
		
		if (strtolower(substr ( $this->getHost (), $len * - 1 )) == strtolower($domain_host)) {
			return true;
		}
		return false;
	}
	function isPartOfDomain($subdomain) {
		if (! ($subdomain instanceof URL)) {
			$subdomain = self::fromURL ( $subdomain );
		}
		return $subdomain->isSubDomainOf ( $this );
	}
	function domainParts(){
		$ret = array();
		$p = explode('.',$this->getHost());
		$c = count($p);
		foreach($p as $k=>$v){
			$domain = implode('.',array_slice($p,$k,$c-$k));
			$ret[] = $domain;
		}
		return $ret;
	}
	
	function toURL() {
        $url = '';
        if($this->host !== null || $this->scheme !== null){
		    $url = $this->scheme . '://' . $this->host;

            if($this->port != 80){
                $url .= ':'.$this->port;
            }
        }
		$url .='/' . ltrim($this->path->__toString(),'/');
		return rtrim($url,'/');
	}
	
	function __toString() {
		return $this->toURL ();
	}
	
	function __clone(){
		$this->path = clone $this->path;
	}
	
	/* Getters/Setters */
	/**
	 * @return the $scheme
	 */
	public function getScheme() {
		return $this->scheme;
	}
	
	/**
	 * @return the $host
	 */
	public function getHost() {
		return $this->host;
	}
	
	function getIP(){
		$ip = new IP((string)$this->host);
		if(!$ip->isValid()){
			$ip = gethostbyname($this->host);
			if($ip == $this->host){
				throw new \Exception('DNS lookup for hostname failed');
			}
			$ip = new IP($ip);
		}
		return $ip;
	}
	
	/**
	 * @return the $port
	 */
	public function getPort() {
		return $this->port;
	}

	/**
	 * @param number $port
	 */
	public function setPort($port) {
		$this->port = $port;
	}

	/**
	 * @return \Radical\Utility\Net\URL\Path $path
	 */
	public function getPath() {
		return $this->path;
	}
	
	/**
	 * @return the $query
	 */
	public function getQuery() {
		return $this->path->getQuery();
	}
	
	/**
	 * @return the $fragment
	 */
	public function getFragment() {
		return $this->path->getFragment();
	}
	
	/**
	 * @param field_type $scheme
	 */
	public function setScheme($scheme) {
		$this->scheme = $scheme;
	}
	
	/**
	 * @param field_type $host
	 */
	public function setHost($host) {
		$this->host = $host;
	}
	
	/**
	 * @param field_type $path
	 */
	public function setPath($path) {
		$this->path = $path;
	}
	
	/**
	 * @param string $query
	 */
	public function setQuery($query) {
		$this->path->setQuery($query);
	}
	
	/**
	 * @param field_type $fragment
	 */
	public function setFragment($fragment) {
		$this->fragment = $fragment;
	}
	
	/* Virtual Construct */

    /**
     * @param $url
     * @return bool|URL
     */
	static function fromURL($url) {
		if (! strpos ( $url, '://' ) || ! self::_SCHEME_VALID ( $url )) {
			$url = 'http://' . $url;
		}
		$ret = parse_url ( $url );
		if (isset ( $ret ['scheme'] )) {
			$ret ['scheme'] = UTF8::lower ( $ret ['scheme'] );
			$ret ['host'] = UTF8::lower ( $ret ['host'] );
			return new static ( $ret );
		}
		return false;
	}

    /**
     * @param $url
     * @return URL
     */
    static function fromURLorPath($url){
        $ret = parse_url ( $url );
        if (isset ( $ret ['scheme'] )) {
            $ret ['scheme'] = UTF8::lower ( $ret ['scheme'] );
            $ret ['host'] = UTF8::lower ( $ret ['host'] );
        }
        return new static ( $ret );
    }

    /**
     * @param null $path
     * @return bool|URL
     */
	static function fromRequest($path = null){
		$scheme = 'http://';
		if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']){
			$scheme = 'https://';
		}

        $host = isset($_SERVER["HTTP_HOST"])?$_SERVER["HTTP_HOST"]:$_SERVER['SERVER_ADDR'];
		$url = $scheme.$host;
		if(!$path) $path = $_SERVER['REQUEST_URI'];
		$url.=$path;

		return static::fromURL($url);
	}
}