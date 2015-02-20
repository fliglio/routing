<?php

namespace Fliglio\Routing;

use Fliglio\Web\Uri;

/**
 * Routing_PatternRoute
 * 
 * 	$route = new Routing_PatternRoute("/:arg1/:arg2/something", array("constant" => "myParam"));
 * 
 * @package Fl
 */
class PatternRoute extends Route {
	private $regex;

	private $toCapture = array();
	private $capturedArgs = array();

	public function __construct($pattern, array $params = array()) {
        parent::__construct($params);

		list($regex, $this->toCapture) = $this->parse($pattern);
		$this->regex = $regex;
	}

	/**
	 * Parse a pattern like "/:arg1/:arg2/something" and returns a regexp and
	 * list of arguments that the regexp will capture.
	 *
	 * @param string $pattern Route pattern
	 * @return array [the new regexp, captured parameter names]
	 */
	private function parse($pattern) {
		return array(
			'/^' . preg_replace_callback('/\\\:(\w+)/', 'Fliglio\Routing\PatternRoute::__parser_callback', preg_quote($pattern, '/')) . '$/',
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


	public function match(Uri $input, $method) {
		if ((bool)preg_match($this->regex, (string)$input, $this->capturedArgs)) {
			$this->capturedArgs = array_intersect_key($this->capturedArgs, array_flip($this->toCapture));
			return true;
		} 
		else {
			return false;
		}
	}

	public function getParams() {
		$params = parent::getParams();

		return array_merge($params, $this->capturedArgs);
	}
}