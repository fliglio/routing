<?php

namespace Fliglio\Routing;

/**
 * Routing_PatternRoute
 * 
 * 	$route = new Routing_PatternRoute("/:arg1/:arg2/something", array("constant" => "myParam"));
 * 
 * @package Fl
 */
class PatternRoute extends RegexRoute {
	protected $pattern;
	protected $toCapture = array();

	public function __construct($pattern, array $defaults = array()) {
		$this->pattern = $pattern;
		list($regex, $this->toCapture) = self::parse($this->pattern);
        parent::__construct($regex, $defaults);
	}

	/**
	 * Parse a pattern like "/:arg1/:arg2/something" and returns a regexp and
	 * list of arguments that the regexp will capture.
	 *
	 * @param string $pattern Route pattern
	 * @return array [the new regexp, captured parameter names]
	 */
	static public function parse($pattern) {
		return array(
			'/^' . preg_replace_callback('/\\\:(\w+)/', 'PatternRoute::__parser_callback', preg_quote($pattern, '/')) . '$/',
			self::__parser_callback(null, true) 
		);
	}

	/**
	 * This is a callback for Routing_PatternRoute::parse to collect "toCapture" matches.
	 * It also does the regexp replacement string.
	 *
	 * @param array  $matches Matched items from the Routing_PatternRoute::parse regexp
	 * @param bool   $flush   If true the stack will be flushed and returned
	 * @return mixed Either the replacement string or the collect stack
	 */
	static private function __parser_callback($matches, $flush = false) {
		static $stack = array();
		
		if ($flush) {
			$stackFlush = $stack;
			$stack = array();
			return $stackFlush;
		}
		else {
			$stack[] = $matches[1];
			return '(?P<' . $matches[1] . '>[^\/]+)';
		}
	}

	public function getArgsToCapture() {
		return $this->toCapture;
	}

	public function urlFor(array $params = array()) {
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

		return new Web_Uri($this->assembleUrl($url, $params));
	}

	public function match($input) {
		if (parent::match($input)) {
			$this->capturedArgs = array_intersect_key($this->capturedArgs, array_flip($this->toCapture));
			return true;
		} 
		else {
			return false;
		}
	}
}