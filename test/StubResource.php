<?php

namespace Fliglio\RestFc;

use Fliglio\Flfc\Context;
use Fliglio\Routing\Routable;
use Fliglio\RestFc\Input\RouteParam;
use Fliglio\RestFc\Input\GetParam;

use Fliglio\Fltk\View;
use Fliglio\Fltk\JsonView;

class StubResource {

	public function __construct(Context $context) {
	}
	
	public function getFoo(Context $context, RouteParam $id, GetParam $type = null) {
		return array(
			'id' => $id->get(),
			'type' => $type == null ? null : $type->get()
		);
	}

	
}