<?php

namespace Fliglio\Routing;

use Fliglio\Flfc\Exceptions\CommandNotFoundException;
use Fliglio\Flfc\Context;
use Fliglio\Web\Body;
use Fliglio\Web\RouteParam;
use Fliglio\Web\GetParam;

class DefaultInjectablesFactory {

	public function createAll() {
		return array(
			$this->createRequestReader(), 
			$this->createResponseWriter(),
			$this->createBody(),
			$this->createRouteParam(),
			$this->createGetParam()
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
				$c = $req->isHeaderSet('ContentType') ? $req->getHeader('ContentType') : null;
				return new Body($req->getBody(), $c);
			}
		);
	}

	public function createRouteParam() {
		return new InjectableProperty(
			'Fliglio\Web\RouteParam', 
			function(Context $context, $paramName) {
				$route = $context->getProp(RoutingApp::CURRENT_ROUTE);
				$routeParams = $route->getParams();
				
				if (!isset($routeParams[$paramName])) {
					throw new CommandNotFoundException("No suitable method signature found: Route param ".$paramName." does not exist");
				}	
				return new RouteParam($routeParams[$paramName]);
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
}