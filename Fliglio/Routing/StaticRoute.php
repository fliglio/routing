<?php

namespace Fliglio\Routing;

use Fliglio\Web\Uri;

class StaticRoute extends Route {

	private $criteria;

	public function __construct($criteria, array $params = array()) {
		parent::__construct($params);

		$this->criteria = $criteria;
	}
	
	public function match(Uri $input, $method) {
		return (string)$input === $this->criteria;
	}
}