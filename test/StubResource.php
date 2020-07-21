<?php

namespace Fliglio\Routing;

use Fliglio\Http\RequestReader;
use Fliglio\Web\Entity;
use Fliglio\Web\FileUpload;
use Fliglio\Web\IntPathParam;
use Fliglio\Web\PathParam;
use Fliglio\Web\GetParam;
use Fliglio\Web\Body;

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
	public function uploadFile(FileUpload $fieldName) {
		return $fieldName;
	}
	public function addFoo(Body $body) {
		return $body->get();
	}	
	public function getCatchAll() {
		return 'Not Found';
	}
	public function getCatchNone() {
		return 'None';
	}
	public function getEntity(Entity $entity, IntPathParam $id) {
		return [
			'id' => $id->get(),
			'body' => $entity->get(),
			'contentType' => $entity->getContentType()
		];
	}
	public function dne() {}
	public function error() {}
}

class StubResourceChild extends StubResource {}

#class StubResourceChild extends StubResource {}