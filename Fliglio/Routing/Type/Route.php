<?php

namespace Fliglio\Routing\Type;

use Fliglio\Web\Url;
use Fliglio\Http\Http;
use Fliglio\Http\RequestReader;

abstract class Route {
	private $params;

	private $protocol;
	private $command;
	private $methods = array(
		Http::METHOD_GET, 
		Http::METHOD_POST, 
		Http::METHOD_PUT, 
		Http::METHOD_DELETE, 
		Http::METHOD_PATCH, 
		Http::METHOD_OPTIONS
	);

	public function __construct(array $params) {
		$this->params = $params;
	}

	public function match(RequestReader $req) {
		return in_array($req->getHttpMethod(), $this->getMethods());
	}


	public function setMethods(array $methods) {
		$this->methods = $methods;
	}
	public function getMethods() {
		return $this->methods;
	}

	public function setCommand($cmd) {
		$this->command = $cmd;
	}
	public function getCommand() {
		return $this->command;
	}

	public function setProtocol($val) {
		$this->protocol = $val;
	}
	public function getProtocol() {
		return $this->protocol;
	}



	public function getParams() {
		return $this->params;
	}

}