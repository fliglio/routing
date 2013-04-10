<?php

namespace Fliglio\Routing;

use Fliglio\Flfc\CommandNotFoundException;
use Fliglio\Flfc\Context;
use Fliglio\Flfc\App;

/**
 * 
 */
class RestInvokerApp extends App {
	
	public function call(Context $context) {
		$cmd = $context->getRequest()->getCommand();
		list($ns, $commandGroup, $command) = explode('.', $context->getRequest()->getCommand());
		
		$className = $ns . '\\' . $commandGroup;
		
		
		$instance  = new $className($context);
		
		if (!method_exists($instance, $command)) {
			throw new CommandNotFoundException("Command '{$command}' does not exist (".$context->getRequest()->getCommand().")");
		}
		
		if (!($instance instanceof Routable)) {
			throw new CommandNotRoutableException("CommandGroups must implement Fliglio\Flfc\Routable: " . get_class($instance));
		}
		
		$to = $instance->{$command}();
		
		if (is_object($to)) {
			$reflector = new \ReflectionClass(get_class($to));
			if ($reflector->implementsInterface("Fliglio\Flfc\ResponseContent")) {
				$context->getResponse()->setContent($to);
			}
		}
		
		return $to;
	}
}
