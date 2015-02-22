<?php

namespace Fliglio\Routing\Type;

use Fliglio\Web\Uri;
use Fliglio\Web\HttpAttributes;

class RouteBuilder {
	const TYPE_PATTERN = 0;
	const TYPE_STATIC = 1;
	const TYPE_ALL = 2;
	const TYPE_NONE = 3;

	private $command = null;
	private $uriTemplate = "";
	private $routeType = null;
	private $protocol = null;
	private $methods = array();
	private $params = array();

	public function __construct() {}
	public static function get() {
		return new self();
	}
	public function command($cmd) {
		$this->command = $cmd;
		return $this;
	}
	public function protocol($protocol) {
		$this->protocol = $protocol;
		return $this;
	}

	public function catchAll() {
		$this->routeType = self::TYPE_ALL;
		return $this;
	}
	public function catchNone() {
		$this->routeType = self::TYPE_NONE;
		return $this;
	}

	public function uri($uriTemplate) {
		$this->uriTemplate = $uriTemplate;
		if (strPos($uriTemplate, ':') === false) {
			$this->routeType = self::TYPE_STATIC;
		} else {
			$this->routeType = self::TYPE_PATTERN;
		}
		return $this;
	}

	public function method($type) {
		$this->methods[] = $type;
		return $this;
	}

	public function param($key, $val) {
		$this->params[$key] = $val;
		return $this;
	}

	public function build() {
		$route;

		switch ($this->routeType) {
		case self::TYPE_ALL:
			$route = new CatchAllRoute($this->params);
			break;
		case self::TYPE_NONE:
			$route = new CatchNoneRoute($this->params);
			break;
		case self::TYPE_STATIC:
			$route = new StaticRoute($this->uriTemplate, $this->params);
			break;
		case self::TYPE_PATTERN:
			$route = new PatternRoute($this->uriTemplate, $this->params);
		break;
		default:
			throw new RouteException("Not enough info to build a route");
		}
		$route->setProtocol($this->protocol);
		$route->setCommand($this->command);

		if (!empty($this->methods)) {
			$route->setMethods($this->methods);
		}

		return $route;
	}
}