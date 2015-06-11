<?php

namespace Fliglio\Routing;

use Fliglio\Flfc\Exceptions\CommandNotFoundException;
use Fliglio\Flfc\Context;
use Fliglio\Web\Body;
use Fliglio\Web\PathParam;
use Fliglio\Web\GetParam;
use Fliglio\Web\IntPathParam;
use Fliglio\Web\IntGetParam;
use Fliglio\Web\Entity;

class DefaultInjectablesFactory {

	public function createAll() {
		return [
			$this->createRequestReader(), 
			$this->createResponseWriter(),
			$this->createBody(),
			$this->createEntity(),
			$this->createPathParam(),
			$this->createIntPathParam(),
			$this->createGetParam(),
			$this->createIntGetParam(),
		];
	}

	public function createRequestReader() {
		return new InjectableProperty(
			'Fliglio\Http\RequestReader', 
			function(Context $context, $paramName) {
				return $context->getRequest();
			}
		);
	}

	public function createResponseWriter() {
		return new InjectableProperty(
			'Fliglio\Http\ResponseWriter', 
			function(Context $context, $paramName) {
				return $context->getResponse();
			}
		);
	}

	public function createBody() {
		return new InjectableProperty(
			'Fliglio\Web\Body', 
			function(Context $context, $paramName) {
				$req = $context->getRequest();
				$c = $req->isHeaderSet('ContentType') ? $req->getHeader('ContentType') : null;
				return new Body($req->getBody(), $c);
			}
		);
	}
	
	public function createEntity() {
		return new InjectableProperty(
			'Fliglio\Web\Entity', 
			function(Context $context, $paramName) {
				$req = $context->getRequest();
				$c = $req->isHeaderSet('ContentType') ? $req->getHeader('ContentType') : null;
				return new Entity($req->getBody(), $c);
			}
		);
	}

	public function createPathParam() {
		return new InjectableProperty(
			'Fliglio\Web\PathParam', 
			function(Context $context, $paramName) {
				$route = $context->getProp(RoutingApp::CURRENT_ROUTE);
				$routeParams = $route->getParams();
				
				if (!isset($routeParams[$paramName])) {
					throw new CommandNotFoundException("No suitable method signature found: Route param ".$paramName." does not exist");
				}	
				return new PathParam($routeParams[$paramName]);
			}
		);
	}

	public function createIntPathParam() {
		return new InjectableProperty(
			'Fliglio\Web\IntPathParam', 
			function(Context $context, $paramName) {
				$route = $context->getProp(RoutingApp::CURRENT_ROUTE);
				$routeParams = $route->getParams();
				
				if (!isset($routeParams[$paramName])) {
					throw new CommandNotFoundException("No suitable method signature found: Route param ".$paramName." does not exist");
				}	
				return new IntPathParam($routeParams[$paramName]);
			}
		);
	}
	
	public function createGetParam() {
		return new InjectableProperty(
			'Fliglio\Web\GetParam', 
			function(Context $context, $paramName) {
				$getParams = $context->getRequest()->getGetParams();

				if (!isset($getParams[$paramName])) {
					throw new CommandNotFoundException("No suitable method signature found: GET param ".$paramName." does not exist");
				} else {
					return new GetParam($getParams[$paramName]);
				}
			}
		);
	}

	public function createIntGetParam() {
		return new InjectableProperty(
			'Fliglio\Web\IntGetParam', 
			function(Context $context, $paramName) {
				$getParams = $context->getRequest()->getGetParams();

				if (!isset($getParams[$paramName])) {
					throw new CommandNotFoundException("No suitable method signature found: GET param ".$paramName." does not exist");
				} else {
					return new IntGetParam($getParams[$paramName]);
				}
			}
		);
	}
}
