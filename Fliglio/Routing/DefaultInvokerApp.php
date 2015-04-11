<?php

namespace Fliglio\Routing;

use Fliglio\Flfc\Context;
use Fliglio\Flfc\DefaultBody;
use Fliglio\Flfc\UnmarshalledBody;
use Fliglio\Flfc\Apps\App;
use Fliglio\Flfc\Exceptions\CommandNotFoundException;
use Fliglio\Routing\Input\RouteParam;
use Fliglio\Routing\Input\GetParam;
use Fliglio\Routing\RoutingApp;
use Fliglio\Http\ResponseBody;

class DefaultInvokerApp extends App {

	private $mappers = array();

	public function addMapper($entityName, ApiMapper $mapper) {
		$this->mappers[$entityName] = $mapper;
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
		$context->getResponse()->setBody($this->processBody($to));
	}

	private function processBody($to) {
		$className = '';
		if (is_object($to)) {
			$className = get_class($to);	
		}

		if ($to instanceof ResponseBody) {
			return $to;
		} 
		if (isset($this->mappers[$className])) {
			return new UnmarshalledBody($this->mappers[$className]->marshal($to));
		}
		if ($this->isMappableCollection($to)) {
			if (count($to) == 0) {
				return new UnmarshalledBody(array());	
			} else {
				return new UnmarshalledBody($this->mappers[$this->getCollectionClassName($to)]->marshalCollection($to));
			}
		}

		return new UnmarshalledBody($to);;
	}
	private function isMappableCollection($to) {
		if (!is_array($to)) {
			return false;
		}
		$className = null;
		foreach ($to as $elem) {
			if (!is_object($elem)) {
				return false;
			}
			if (is_null($className)) {
				$className = get_class($elem);
			}
			if (get_class($elem) != $className) {
				return false;
			}
		}
		return true;
	}
	private function getCollectionClassName($to) {
		return get_class($to[0]);
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
			$paramClass = $param->getClass();

			// unmarshal request body into entity if a suitable mapper is found registered
			// and add to arguments
			if (isset($this->mappers[$paramClass->getName()])) {
				$entity = $this->mappers[$paramClass->getName()]->unmarshal($context->getRequest()->getPostData());
				$methodArgs[] = $entity;

			// look for core/generic argument types
			} else {
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
		}
		return $methodArgs;
	}

}
