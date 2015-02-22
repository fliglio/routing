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
		list($ns, $commandGroup, $methodName) = explode('.', $context->getRequest()->getCommand());
		
		$className = $ns . '\\' . $commandGroup;
		
		
		$instance  = new $className();
		
		
		if (!method_exists($instance, $methodName)) {
			throw new CommandNotFoundException("Method '{$methodName}' does not exist (".$methodName.")");
		}
		
		if (!($instance instanceof Routable)) {
			throw new CommandNotRoutableException("CommandGroups must implement Fliglio\Flfc\Routable: " . $className);
		}
		
		$routeParams = $context->getRequest()->getProp('routeParams');
		$getParams = $_GET;
		$methodArgs = self::getMethodArgs($context, $className, $methodName, $routeParams, $getParams);


		$to = call_user_func_array(array($instance, $methodName), $methodArgs);
		
		if (is_object($to)) {
			$reflector = new \ReflectionClass(get_class($to));
			if ($reflector->implementsInterface("Fliglio\Flfc\ResponseContent")) {
				$context->getResponse()->setContent($to);
			}
		}
		
		return $to;
	}

	private static function getMethodArgs(Context $context, $className, $methodName, $routeParams, $getParams) {
		$methodArgs = array();

		$r = new \ReflectionMethod($className, $methodName);
		$params = $r->getParameters();
		
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
