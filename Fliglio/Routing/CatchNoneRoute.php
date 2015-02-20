<?php

namespace Fliglio\Routing;

use Fliglio\Web\Uri;

class CatchNoneRoute extends Route {
	
	public function match(Uri $input, $method) {
		return false;
	}
}