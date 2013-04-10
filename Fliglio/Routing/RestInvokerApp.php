<?php

namespace Fliglio\Routing;

use Fliglio\Flfc\CommandNotFoundException;
use Fliglio\Flfc\Context;
use Fliglio\Flfc\App;
use Fliglio\Web\HttpAttributes;

/**
 * 
 */
class RestInvokerApp extends App {
	
	public function call(Context $context) {
		$cmd = $context->getRequest()->getCommand();
		list($ns, $commandGroup, $command) = explode('.', $context->getRequest()->getCommand());
		
		$className = $ns . '\\' . $commandGroup;
		
		
		$instance  = new $className($context);
		
		$method = $command;
		if (substr($command, -1) == '!') {
			$name = substr($command, 0, strlen($command)-1);
			$method = HttpAttributes::getMethod() . ucFirst($name);
		}
		
		if (!method_exists($instance, $method)) {
			throw new CommandNotFoundException("Command '{$command}' does not exist (".$context->getRequest()->getCommand().")");
		}
		
		if (!($instance instanceof Routable)) {
			throw new CommandNotRoutableException("CommandGroups must implement Fliglio\Flfc\Routable: " . get_class($instance));
		}
		
		$to = $instance->{$method}();
		
		if (is_object($to)) {
			$reflector = new \ReflectionClass(get_class($to));
			if ($reflector->implementsInterface("Fliglio\Flfc\ResponseContent")) {
				$context->getResponse()->setContent($to);
			}
		}
		
		return $to;
	}
}
