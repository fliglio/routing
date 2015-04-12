<?php

namespace Fliglio\Routing\Input;

use Fliglio\Routing\ApiMapper;

class Body {
	private $body;

	public function __construct($body, $contentType) {
		$this->body = $body;
		$this->contentType = $contentType;
	}
	public function bind(ApiMapper $mapper) {
		$arr = null;
		switch($this->contentType) {

		// assume json
		case 'application/json':
		default:
			$arr = json_decode($this->body, true);
		}

		return $mapper->unmarshal($arr);
	}
}