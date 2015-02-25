<?php

namespace Fliglio\Routing;

use Fliglio\Flfc\Exceptions\CommandNotFoundException;
use Fliglio\Flfc\Context;
use Fliglio\Flfc\App;
use Fliglio\Web\HttpAttributes;

/**
 * 
 */
class DefaultInvokerApp extends App {
	
	public function call(Context $context) {
		$route = $context->getRequest()->getProp(RoutingApp::CURRENT_ROUTE);
		$cmd = $route->getCommand();

		list($ns, $name, $methodName) = explode('.', $cmd);
		
		$className = $ns . '\\' . $name;
		
		$routeParams = $context->getRequest()->getProp('routeParams');
		$getParams = $_GET;
		
		$rConst = new \ReflectionMethod($className, '__construct');
		$instance;
	
	    try {
			$rClass = new \ReflectionClass($className);
			if (!$rClass->hasMethod($methodName)) {
				throw new CommandNotFoundException("Method '{$methodName}' does not exist (".$methodName.")");
			}

			$instance = new $className();
	    } catch (ReflectionException $Exception) {
			throw new CommandNotFoundException("Class '{$className}' does not exist (".$className.")");
	    }
		
		$rMethod = new \ReflectionMethod($className, $methodName);


		$to = $rMethod->invoke($instance);
		$context->getResponse()->setBody(new RawView($to));
	}
}
