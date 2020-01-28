<?php

namespace Fliglio\Routing;

use Fliglio\Flfc\Context;

interface Injectable {
	public function getClassName(); // string
	public function create(Context $context, $paramName); // property to inject
}