<?php


namespace Fliglio\Routing\Type;


use Fliglio\Http\RequestReader;

class RegexRoute extends Route {

	private $regex;

	public function __construct($regex, array $params = []) {
		parent::__construct($params);

		$this->regex = $regex;
	}

	public function match(RequestReader $req) {
		if (!parent::match($req)) {
			return false;
		}

		return (bool)preg_match($this->regex, (string)$req->getUrl());
	}
}