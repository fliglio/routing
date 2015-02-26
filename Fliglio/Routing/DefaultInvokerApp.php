<?php

namespace Fliglio\Routing;

use Fliglio\Flfc\Context;
use Fliglio\Flfc\UnmarshalledBody;
use Fliglio\Flfc\Apps\App;
use Fliglio\Flfc\Exceptions\CommandNotFoundException;
use Fliglio\Routing\Input\RouteParam;
use Fliglio\Routing\Input\GetParam;
use Fliglio\Routing\RoutingApp;
use Fliglio\Http\ResponseBody;

class DefaultInvokerApp extends App {
	
	public function call(Context $context) {
		$route = $context->getProp(RoutingApp::CURRENT_ROUTE);
		$cmd = $route->getCommand();
		list($ns, $name, $methodName) = explode('.', $cmd);
		
		$className = $ns . '\\' . $name;
		
		$routeParams = $route->getParams();
		$getParams = $_GET;
		
		$instance = new $className();
	

		$rMethod = self::getReflectionMethod($className, $methodName);
		$methodArgs = self::getMethodArgs($rMethod, $context, $routeParams, $getParams);

		$to = $rMethod->invokeArgs($instance, $methodArgs);
		$body;
		if ($to instanceof ResponseBody) {
			$body = $to;
		} else {
			$body = new UnmarshalledBody($to);
		}
		$context->getResponse()->setBody($body);
	}

	private static function getReflectionMethod($className, $methodName) {
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
	private static function getMethodArgs(\ReflectionMethod $rMethod, Context $context, $routeParams, $getParams) {
		$methodArgs = array();

		$params = $rMethod->getParameters();
		
		foreach ($params as $param) {
			//$param is an instance of ReflectionParameter
			$paramName = $param->getName();
			$paramClass = $param->getClass();

			switch ($paramClass->getName()) {
			case 'Fliglio\Http\RequestReader':
				$methodArgs[] = $context->getRequest();
				break;
			case 'Fliglio\Http\ResponseWriter':
				$methodArgs[] = $context->getResponse();
				break;
			case 'Fliglio\Routing\Input\RouteParam':
				if (!isset($routeParams[$paramName])) {
					throw new \CommandNotFoundException("No suitable method signature found: Route param ".$paramName." does not exist");
				}	
				$methodArgs[] = new RouteParam($routeParams[$paramName]);
				
				break;	
			case 'Fliglio\Routing\Input\GetParam':
				if (!isset($getParams[$paramName])) {
					if (!$param->isOptional()) {
						throw new \CommandNotFoundException("No suitable method signature found: GET param ".$paramName." does not exist");
					}
				} else {
					$methodArgs[] = new GetParam($getParams[$paramName]);
				}
				break;	
			default:
				throw new \CommandNotFoundException("No suitable method signature found: Type ".$paramClass->getName()." not recognized");
			}
		}
		return $methodArgs;
	}

}
