<?php

namespace Fliglio\Routing;

class RouteMap {
	protected static $routeConfig = array();
	
	private $ns;
	
	private $routes  = array();
	private $indexed = array();

	public static function setRoutes(array $routes) {
		self::$routeConfig = $routes;
	}
	
	public function __construct($ns = null) {
		$this->ns = $ns;
		foreach (self::$routeConfig as $key => $route) {
			$this->connect($key, $route);
		}
	}

	public function connect( $key, Route $route ) {
		if (isset($this->indexed[$key])) {
			throw new RouteException( "Route '{$key}' already exists" );
		}
		$route->setNamespace($this->ns);
		$this->routes[] = $route;
		$this->indexed[$key] = $route;
	}

	public function urlFor($key, array $params = array()) {
		if (!isset($this->indexed[$key])) {
			throw new RouteException("'" . $key . "' not found in routes");
		}
		if (isset($params['protocol'])) {
			$this->indexed[$key]->setProtocol( $params['protocol'] );
			unset($params['protocol']);
		}
		if ($this->indexed[$key]->getProtocol() == Web_HttpAttributes::getProtocol()) {
			return $this->indexed[$key]->urlFor($params);
		} else {
			$base = new Web_Uri(sprintf('%s://%s/', $this->indexed[$key]->getProtocol(), Web_HttpAttributes::getHttpHost()));
			return $base->join($this->indexed[$key]->urlFor($params));
		}
	}
	public function getRouteByKey($key) {
		if (!isset($this->indexed[$key])) {
			throw new RouteException("Route '{$key}' does not exist");
		}
		return $this->indexed[$key];
	}
	public function getRouteKey($request) {
		foreach ($this->indexed AS $key => $route) {
			if($route->match($request)) {
				return $key;
			}
		}
		throw new RouteException("No Match");
	}
	public function getRouteKeys() {
		return array_keys( $this->indexed );
	}
	public function getRoute( Web_Uri $request ) {
		if ( substr( (string)$request, 0, 1 ) === '@' ) {
			$key = substr( (string)$request, 1 );
			if ( array_key_exists( $key, $this->indexed ) ) {
				return $this->indexed[$key];
			} else {
				throw new RouteException( sprintf( "Internal Route Not Found for '%s'", $request ) );
			}
		}
		foreach ( $this->routes AS $route ) {
			if ( $route->match( $request ) ) {
				return $route;
			}
		}
		throw new RouteException( "Route Not Found" );
	}

	protected function cleanupRoute( $route ) {
		if( isset( $route['cmd'] ) ) {
			list( $route['module'], $route['commandGroup'], $route['command'] ) = explode( '.', $route['cmd'] );
			unset( $route['cmd'] );
		}
		return $route;
	}

}