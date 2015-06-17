<?php

namespace Fliglio\Routing;

use Fliglio\Web\Url;
use Fliglio\Http\Http;
use Fliglio\Routing\Type\Route;
use Fliglio\Http\RequestReader;

class RouteMap {
		
	private $routes  = array();
	private $indexed = array();

	public function __construct() {
	}

	public static function get() {
		return new self();
	}

	public function connectRoute(Route $route) {
		$this->routes[] = $route;
			$key = $route->getKey();
		if (!is_null($key)) {
			if (isset($this->indexed[$key])) {
				throw new RouteException( "Route '{$key}' already exists" );
			}
			$this->indexed[$key] = $route;
		}
		return $this;
	}

	/* ig $key isn't null, set it on the route. Else ignore
	 *
	 * @Deprecated
	 */
	public function connect($key, Route $route) {
		if (!is_null($key)) {
			$route->setKey($key);
		}	
		return $this->connectRoute($route);
	}
	public function getRoute(RequestReader $request) {
		$url = (string)$request->getUrl();
		$method = $request->getHttpMethod();

		if (substr($url, 0, 1) === '@') {
			$key = substr($url, 1);
			return $this->getRouteByKey($key);
		}
		foreach ($this->routes AS $route) {
			if ($route->match($request)) {
				return $route;
			}
		}
		throw new RouteException("Route Not Found");
	}

	private function getRouteByKey($key) {
		if (!isset($this->indexed[$key])) {
			throw new RouteException("Route '{$key}' does not exist");
		}
		return $this->indexed[$key];
	}

}
