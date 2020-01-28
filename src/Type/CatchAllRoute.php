<?php

namespace Fliglio\Routing\Type;

use Fliglio\Http\RequestReader;

class CatchAllRoute extends Route {

	public function match(RequestReader $req) {
		return true;
	}

}