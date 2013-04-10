<?php

namespace Fliglio\Routing;

use Fliglio\Web\Uri;

abstract class RegexRoute extends Route {

	protected $regex;
	
	public function __construct($regex, array $defaults = array()) {
		parent::__construct($defaults);

		$this->regex = $regex;
	}
	
	
	public function match(Uri $input) {
		return (bool) preg_match($this->regex, (string)$input, $this->capturedArgs);
	}

}