<?php

namespace Fliglio\Routing\Type;

use Fliglio\Web\Uri;

class CatchAllRoute extends Route {

	public function match(Uri $input, $method) {
		return true;
	}
}