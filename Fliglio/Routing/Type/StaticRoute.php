<?php

namespace Fliglio\Routing\Type;

use Fliglio\Web\Url;
use Fliglio\Http\RequestReader;

class StaticRoute extends Route {

	private $criteria;

	public function __construct($criteria, array $params = array()) {
		parent::__construct($params);

		$this->criteria = $criteria;
	}
	public function getCriteria() {
		return $this->criteria;
	}

	public function match(RequestReader $req) {
		if (!parent::match($req)) {
			return false;
		}
		return (string)$req->getUrl() === $this->criteria;
	}
}
