<?php

namespace Fliglio\Routing\Type;

use Fliglio\Web\Url;
use Fliglio\Http\RequestReader;

class CatchNoneRoute extends Route {
	
	public function match(RequestReader $req) {
		return false;
	}
}