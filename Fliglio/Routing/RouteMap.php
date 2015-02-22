<?php

namespace Fliglio\Routing;

use Fliglio\Web\Uri;
use Fliglio\Web\HttpAttributes;
use Fliglio\Routing\Type\Route;

class RouteMap {
		
	private $routes  = array();
	private $indexed = array();

	public function __construct() {
	}

	public static function get() {
		return new self();
	}

	public function connect($key, Route $route) {
		if (isset($this->indexed[$key])) {
			throw new RouteException( "Route '{$key}' already exists" );
		}
		$this->routes[] = $route;
		$this->indexed[$key] = $route;
		return $this;
	}
	public function getRoute(Uri $request, $method) {
		if (substr((string)$request, 0, 1) === '@') {
			$key = substr((string)$request, 1);
			return $this->getRouteByKey($key);
		}
		foreach ($this->routes AS $route) {
			if ($route->match($request, $method)) {
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