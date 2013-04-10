<?php

namespace Fliglio\Routing;

class CatchNoneRoute extends Route {

	public function __construct(array $defaults = array()) {
		parent::__construct($defaults);
	}
	public function urlFor(array $params = array()) {
		return '';
	}
	
	public function match($input) {
		return false;
	}


}