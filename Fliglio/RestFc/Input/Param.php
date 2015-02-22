<?php

namespace Fliglio\Routing\Input;

class Param {
	private $val;

	public function __construct($val) {
		$this->val = $val;
	}
	public function get() {
		return $this->val;
	}
}