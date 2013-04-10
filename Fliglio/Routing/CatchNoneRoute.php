<?php

namespace Fliglio\Routing;

use Fliglio\Web\Uri;

class CatchNoneRoute extends Route {

	public function __construct(array $defaults = array()) {
		parent::__construct($defaults);
	}
	public function urlFor(array $params = array()) {
		return '';
	}
	
	public function match(Uri $input) {
		return false;
	}


}