<?php

namespace Fliglio\Routing;

use Fliglio\Web\Uri;

class StaticRoute extends Route {

	protected $criteria;

	public function __construct($criteria, array $defaults = array()) {
		parent::__construct($defaults);

		$this->criteria = $criteria;
	}
	
	public function match(Uri $input) {
		return (string)$input === $this->criteria;
	}

	public function urlFor(array $params = array()) {

		return new Uri($this->assembleUrl($this->criteria, $params));
	}


}