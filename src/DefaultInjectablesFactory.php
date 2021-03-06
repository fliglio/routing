<?php

namespace Fliglio\Routing;

use Fliglio\Flfc\Exceptions\CommandNotFoundException;
use Fliglio\Flfc\Context;
use Fliglio\Web\Body;
use Fliglio\Web\FileUpload;
use Fliglio\Web\PathParam;
use Fliglio\Web\GetParam;
use Fliglio\Web\IntPathParam;
use Fliglio\Web\IntGetParam;
use Fliglio\Web\Entity;

class DefaultInjectablesFactory {

	public function createAll() {
		return [
			$this->createRoute(), 
			$this->createRequestReader(), 
			$this->createResponseWriter(),
			$this->createBody(),
			$this->createEntity(),
			$this->createPathParam(),
			$this->createIntPathParam(),
			$this->createGetParam(),
			$this->createIntGetParam(),
			$this->createFileUploadParam(),
		];
	}

	public function createRoute() {
		return new InjectableProperty(
			'Fliglio\Routing\Type\Route', 
			function(Context $context, $paramName) {
				return isset($context->getProps()['currentRoute']) ? $context->getProps()['currentRoute'] : null;
			}
		);
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
				$c = $req->isHeaderSet('content-type') ? $req->getHeader('content-type') : null;
				return new Body($req->getBody(), $c);
			}
		);
	}
	
	public function createEntity() {
		return new InjectableProperty(
			'Fliglio\Web\Entity', 
			function(Context $context, $paramName) {
				$req = $context->getRequest();
				$c = $req->isHeaderSet('content-type') ? $req->getHeader('content-type') : null;
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

	public function createFileUploadParam() {
		return new InjectableProperty(
			'Fliglio\Web\FileUpload',
			function(Context $context, $paramName) {
				$files = $context->getRequest()->getFiles();

				if (!isset($files[$paramName])) {
					throw new CommandNotFoundException('No suitable method signature found: $_FILE array does not contain '.$paramName);
				} else {
					return new FileUpload($files[$paramName]);
				}
			}
		);
	}

}
