<?php

namespace Fliglio\Routing;

use Fliglio\Flfc\CommandNotFoundException;
use Fliglio\Flfc\Context;
use Fliglio\Flfc\App;
use Fliglio\Web\HttpAttributes;
use Fliglio\Routing\Input\RouteParam;
use Fliglio\Routing\Input\GetParam;

/**
 * 
 */
class InvokerApp extends App {
	
	public function call(Context $context) {
		$cmd = $context->getRequest()->getCommand();
		list($ns, $name, $methodName) = explode('.', $context->getRequest()->getCommand());
		
		$className = $ns . '\\' . $name;
		
		$routeParams = $context->getRequest()->getProp('routeParams');
		$getParams = $_GET;
		
		$rConst = new \ReflectionMethod($className, '__construct');
		$constructorArgs = self::getMethodArgs($rConst, $context, $routeParams, $getParams);
		$instance;
	
	    try {
			$rClass = new \ReflectionClass($className);
			if (!$rClass->hasMethod($methodName)) {
				throw new CommandNotFoundException("Method '{$methodName}' does not exist (".$methodName.")");
			}

			$instance = $rClass->newInstanceArgs($constructorArgs);
	    } catch (ReflectionException $Exception) {
			throw new CommandNotFoundException("Class '{$className}' does not exist (".$className.")");
	    }


		$rMethod = new \ReflectionMethod($className, $methodName);
		$methodArgs = self::getMethodArgs($rMethod, $context, $routeParams, $getParams);


		$to = $rMethod->invokeArgs($instance, $methodArgs);
		
		if (is_object($to)) {
			$reflector = new \ReflectionClass(get_class($to));
			if ($reflector->implementsInterface("Fliglio\Flfc\ResponseContent")) {
				$context->getResponse()->setContent($to);
			}
		}
		
		return $to;
	}

	private static function getMethodArgs(\ReflectionMethod $rMethod, Context $context, $routeParams, $getParams) {
		$methodArgs = array();

		$params = $rMethod->getParameters();
		
		foreach ($params as $param) {
			//$param is an instance of ReflectionParameter
			$paramName = $param->getName();
			$paramClass = $param->getClass();

			switch ($paramClass->getName()) {
			case 'Fliglio\Flfc\Context':
				$methodArgs[] = $context;
				break;
			case 'Fliglio\Routing\Input\RouteParam':
				if (!isset($routeParams[$paramName])) {
					throw new \Exception("route param ".$paramName." does not exist");
				}	
				$methodArgs[] = new RouteParam($routeParams[$paramName]);
				
				break;	
			case 'Fliglio\Routing\Input\GetParam':
				if (!isset($getParams[$paramName])) {
					if (!$param->isOptional()) {
						throw new \Exception("get param ".$paramName." does not exist");
					}
				} else {
					$methodArgs[] = new GetParam($getParams[$paramName]);
				}
				break;	
			default:
				throw new \Exception("Type ".$paramClass->getName()." not recognized");
			}
		}
		return $methodArgs;
	}

}
