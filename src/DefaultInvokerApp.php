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
		
		$instance = $route->getResourceInstance();
		$methodName = $route->getResourceMethod();
		
		$routeParams = $route->getParams();
		$getParams = $_GET;

		$rMethod = $this->getReflectionMethod($instance, $methodName);
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
				throw new CommandNotFoundException("No suitable method signature found: Type ".$paramClass." not recognized");
			}
		}
		return $methodArgs;
	}

}
