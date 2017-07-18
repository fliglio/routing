<?php

namespace Fliglio\Routing;

use Fliglio\Flfc\Context;

class InjectableProperty implements Injectable {

	private $className;
	private $closure;

	public function __construct($className, $closure) {
		$this->className = $className;
		$this->closure = $closure;
	}
	public function getClassName() {
		return $this->className;
	}

	public function create(Context $context, $paramName) {
		return $this->closure->__invoke($context, $paramName);
	}

}