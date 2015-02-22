<?php

namespace Fliglio\Routing;

use Fliglio\Flfc\CommandNotFoundException;
use Fliglio\Flfc\Context;
use Fliglio\Flfc\App;
use Fliglio\Web\HttpAttributes;

/**
 * 
 */
class DefaultInvokerApp extends App {
	
	public function call(Context $context) {
		$cmd = $context->getRequest()->getCommand();
		list($ns, $name, $methodName) = explode('.', $context->getRequest()->getCommand());
		
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

			$instance = $rClass->newInstance($context);
	    } catch (ReflectionException $Exception) {
			throw new CommandNotFoundException("Class '{$className}' does not exist (".$className.")");
	    }
		
		$rMethod = new \ReflectionMethod($className, $methodName);


		$to = $rMethod->invoke($instance);
		
		if (is_object($to)) {
			$reflector = new \ReflectionClass(get_class($to));
			if ($reflector->implementsInterface("Fliglio\Flfc\ResponseContent")) {
				$context->getResponse()->setContent($to);
			}
		}
		
		return $to;
	}
}
