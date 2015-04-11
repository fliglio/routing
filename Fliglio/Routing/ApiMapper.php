<?php

namespace Fliglio\Routing;

interface ApiMapper {
	public function marshal($entity);
	public function unmarshal($serialized);
}