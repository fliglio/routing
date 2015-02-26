<?php

namespace Fliglio\Routing\Type;

use Fliglio\Web\Uri;
use Fliglio\Http\RequestReader;

class CatchAllRoute extends Route {

	public function match(RequestReader $req) {
		return true;
	}
}