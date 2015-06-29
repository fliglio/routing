<?php

namespace Fliglio\Routing;

use Fliglio\Flfc\Apps\App;
use Fliglio\Flfc\Context;

class StubApp extends App {
	public $called = false;
	public function call(Context $context) {
		$this->called = true;
	}
}
