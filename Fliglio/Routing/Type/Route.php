<?php

namespace Fliglio\Routing\Type;

use Fliglio\Web\Url;
use Fliglio\Http\Http;
use Fliglio\Http\RequestReader;
use Fliglio\Routing\RouteException;

abstract class Route {
	private $params;

	private $key;
	private $protocol;
	private $methods = array(
		Http::METHOD_GET, 
		Http::METHOD_POST, 
		Http::METHOD_PUT, 
		Http::METHOD_DELETE, 
		Http::METHOD_PATCH, 
		Http::METHOD_OPTIONS
	);

	private $resource;
	private $resourceMethod;

	public function __construct(array $params) {
		$this->params = $params;
	}

	public function match(RequestReader $req) {
		return in_array($req->getHttpMethod(), $this->getMethods());
	}

	public function setMethods(array $methods) {
		$this->methods = $methods;
	}
	public function getMethods() {
		return $this->methods;
	}

	public function setResource($resource, $resourceMethod) {
		$this->resource = $resource;
		$this->resourceMethod = $resourceMethod;
	}
	public function getResourceInstance() {
		return $this->resource;
	}
	public function getResourceMethod() {
		return $this->resourceMethod;
	}

	public function setKey($key) {
		$this->key = $key;
	}
	public function getKey() {
		return $this->key;
	}

	public function setProtocol($val) {
		$this->protocol = $val;
	}
	public function getProtocol() {
		return $this->protocol;
	}

	public function getParams() {
		return $this->params;
	}

	protected function assembleUrl($url, array $params) {
		if (count($params) > 0) {
			$cleanParams = array_map(
				[$this, 'urlEncodeParts'], 
				array_keys($params), 
				array_values($params)
			);

			$queryString = implode("&", $cleanParams);

			$url .= "?" . $queryString;
		}

		return Url::fromString($url);
	}

	private function urlEncodeParts($key, $val) {
		return urlencode($key) . "=" . urlencode($val);
	}

}
