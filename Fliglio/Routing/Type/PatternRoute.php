<?php

namespace Fliglio\Routing\Type;

use Fliglio\Web\Url;
use Fliglio\Http\RequestReader;

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

		$this->parse($pattern);
	}

	/**
	 * Parse a pattern like "/:arg1/:arg2/something" and returns a regexp and
	 * list of arguments that the regexp will capture.
	 *
	 * @param string $pattern Route pattern
	 */
	private function parse($pattern) {
		$this->toCapture = array();

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