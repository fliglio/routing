<?php

namespace Fliglio\Routing\Type;

use Fliglio\Http\RequestReader;
use Fliglio\Routing\RouteException;

/**
 * Routing_PatternRoute
 *
 * 	$route = new PatternRoute("/:arg1/:arg2/something", array("constant" => "myParam"));
 * 
 * @package Fl
 */
class PatternRoute extends Route {

	private $pattern;
	private $regex;

	private $toCapture = [];
	private $capturedArgs = [];

	public function __construct($pattern, array $params = []) {
		parent::__construct($params);

		$this->pattern = $pattern;
		$this->parse($pattern);
	}

	public function getPattern() {
		return $this->pattern;
	}
	/**
	 * Parse a pattern like "/:arg1/:arg2/something" and returns a regexp and
	 * list of arguments that the regexp will capture.
	 *
	 * @param string $pattern Route pattern
	 */
	private function parse($pattern) {
		$this->toCapture = [];

		$regexInner = preg_replace_callback(
			'/\\\:(\w+)/',
			function(array $matches) {
				$this->toCapture[] = $matches[1];
				return '(?P<' . $matches[1] . '>[^\/]+)';
			},
			preg_quote($pattern, '/')
		);
		$this->regex = '/^' . $regexInner . '$/';
	}

	public function urlFor(array $params = []) {
		$url = $this->pattern;
		foreach ($this->toCapture as $key) {
			if (!array_key_exists($key, $params)) {
				throw new RouteException('Missing parameter "' . $key . '" in params');
			}

			$url = str_replace(':' . $key, $params[$key], $url);

			// Remove parameters that are in the url. They'll be added back
			// to the url query.
			unset($params[$key]);
		}

		return $this->assembleUrl($url, $params);
	}

	public function match(RequestReader $req) {
		if (!parent::match($req)) {
			return false;
		}

		if ((bool)preg_match($this->regex, (string)$req->getUrl(), $this->capturedArgs)) {
			$this->capturedArgs = array_intersect_key($this->capturedArgs, array_flip($this->toCapture));
			return true;
		} else {
			return false;
		}
	}

	public function getParams() {
		$params = parent::getParams();

		return array_merge($params, $this->capturedArgs);
	}
}
