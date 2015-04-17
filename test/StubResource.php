<?php

namespace Fliglio\Routing;

use Fliglio\Http\RequestReader;
use Fliglio\Routing\Routable;
use Fliglio\Web\PathParam;
use Fliglio\Web\GetParam;
use Fliglio\Web\Body;

use Fliglio\Fltk\View;
use Fliglio\Fltk\JsonView;

class StubResource {

	public function __construct() {
	}
	
	public function getFoo(RequestReader $req, PathParam $id, GetParam $type = null) {
		return array(
			'method' => $req->getHttpMethod(),
			'id' => $id->get(),
			'type' => $type == null ? null : $type->get()
		);
	}

	public function addFoo(Body $body) {
		return $body->get();
	}	
}

class StubResourceChild extends StubResource {


	
}