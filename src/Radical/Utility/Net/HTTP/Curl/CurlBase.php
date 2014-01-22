<?php
namespace Radical\Utility\Net\HTTP\Curl;
use Radical\Basic\Arr\Object\CollectionObject;

abstract class CurlBase extends CollectionObject {
	function setUrl($url){
		$this->data[CURLOPT_URL] = $url;
	}
}