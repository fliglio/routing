<?php

namespace Fliglio\Routing;

use Fliglio\Flfc\Context;
use Fliglio\Flfc\DefaultBody;
use Fliglio\Flfc\UnmarshalledBody;
use Fliglio\Flfc\Apps\App;
use Fliglio\Flfc\Exceptions\CommandNotFoundException;
use Fliglio\Routing\RoutingApp;
use Fliglio\Http\ResponseBody;

class DefaultInvokerApp extends App {

	private $injectables = array();
	/**
	 * Create new DefaultInvokerApp
	 * optionally include array of injectables
	 * - default to use default set of injectables
	 * - explicitely set to use none by passing in empty array
	 */
	public function __construct(array $defaultInjectables = null) {
		parent::__construct();

		if (is_null($defaultInjectables)) {
			$fac = new DefaultInjectablesFactory();
			$defaultInjectables = $fac->createAll();
		}

		foreach ($defaultInjectables as $injectable) {
			$this->addInjectable($injectable);
		}
	}

	public function addInjectable(Injectable $i) {
		$this->injectables[$i->getClassName()] = $i;
		return $this;
	}
	
	public function call(Context $context) {
		$route = $context->getProp(RoutingApp::CURRENT_ROUTE);
		$cmd = $route->getCommand();
		list($ns, $name, $methodName) = explode('.', $cmd);
		
		$className = $ns . '\\' . $name;
		
		$routeParams = $route->getParams();
		$getParams = $_GET;
		
		$instance = new $className();
	

		$rMethod = $this->getReflectionMethod($className, $methodName);
		$methodArgs = $this->getMethodArgs($rMethod, $context, $routeParams, $getParams);

		$to = $rMethod->invokeArgs($instance, $methodArgs);
		$body;
		if ($to instanceof ResponseBody) {
			$body = $to;
		} else {
			$body = new UnmarshalledBody($to);
		}

		$context->getResponse()->setBody($body);
	}

	private function getReflectionMethod($className, $methodName) {
		try {
			return new \ReflectionMethod($className, $methodName);
		} catch (\ReflectionException $e) {
			$rClass = new \ReflectionClass($className);
			$parentRClass = $rClass->getParentClass();
			if (!is_object($parentRClass)) {
				throw new CommandNotFoundException("Method '{$methodName}' does not exist");
			}
			$parentClassName = $parentRClass->getName();
			return self::getReflectionMethod($parentClassName, $methodName);
		}
	}
	private function getMethodArgs(\ReflectionMethod $rMethod, Context $context, $routeParams, $getParams) {
		$methodArgs = array();

		$params = $rMethod->getParameters();
		
		foreach ($params as $param) {
			//$param is an instance of ReflectionParameter
			$paramName = $param->getName();
			$paramClass = $param->getClass()->getName();

			if (array_key_exists($paramClass, $this->injectables)) {
				try {
					$methodArgs[] = $this->injectables[$paramClass]->create($context, $paramName);
				} catch (CommandNotFoundException $e) {

					if ($param->isOptional()) {
						return $methodArgs;
					} else {
						throw $e;
					}
				}
			} else {
				throw new CommandNotFoundException("No suitable method signature found: Type ".$paramClass->getName()." not recognized");
			}
		}
		return $methodArgs;

		// 	switch ($paramClass->getName()) {
		// 	case 'Fliglio\Http\RequestReader':
		// 		$methodArgs[] = $context->getRequest();
		// 		break;
		// 	case 'Fliglio\Http\ResponseWriter':
		// 		$methodArgs[] = $context->getResponse();
		// 		break;
		// 	case 'Fliglio\Web\Body':
		// 		$req = $context->getRequest();
		// 		$c = $req->isHeaderSet('ContentType') ? $req->getHeader('ContentType') : null;
		// 		$methodArgs[] = new Body($req->getBody(), $c);
		// 		break;
		// 	case 'Fliglio\Web\RouteParam':
		// 		if (!isset($routeParams[$paramName])) {
		// 			throw new CommandNotFoundException("No suitable method signature found: Route param ".$paramName." does not exist");
		// 		}	
		// 		$methodArgs[] = new RouteParam($routeParams[$paramName]);
				
		// 		break;	
		// 	case 'Fliglio\Web\GetParam':
		// 		if (!isset($getParams[$paramName])) {
		// 			if (!$param->isOptional()) {
		// 				throw new CommandNotFoundException("No suitable method signature found: GET param ".$paramName." does not exist");
		// 			}
		// 		} else {
		// 			$methodArgs[] = new GetParam($getParams[$paramName]);
		// 		}
		// 		break;	
		// 	default:
		// 		throw new CommandNotFoundException("No suitable method signature found: Type ".$paramClass->getName()." not recognized");
		// 	}
		// }
		// return $methodArgs;
	}

}
