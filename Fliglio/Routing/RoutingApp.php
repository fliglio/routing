<?php
namespace Fliglio\Routing;

use Fliglio\Web\Uri;
use Fliglio\Flfc\Context;
use Fliglio\Flfc\Apps\MiddleWare;
use Fliglio\Flfc\Apps\App;
use Fliglio\Flfc\Exceptions\RedirectException;

/**
 * 
 */
class RoutingApp extends MiddleWare {

	const CURRENT_ROUTE = 'currentRoute';
	const ROUTE_PARAMS  = 'routeParams';

	public function __construct(App $appToWrap, RouteMap $routeMap) {
		parent::__construct($appToWrap);
		$this->routeMap = $routeMap;
	}
	
	public function call(Context $context) {
		$currentHost = $context->getRequest()->getHost();
		$currentProtocol = $context->getRequest()->getProtocol();
		$currentUrl = $context->getRequest()->getCurrentUrl();
		$currentMethod = $context->getRequest()->getHttpMethod();

		// Identify current Command; register RouteMap & params with Context
		$route;
		try {
			$route = $this->routeMap->getRoute(new Uri($currentUrl), $currentMethod);
		} catch (RouteException $e) {
			throw new PageNotFoundException(sprintf(
				"Route not found for request: %s %s://%s%s",
				$currentMethod, $currentProtocol, $currentHost, $currentUrl 
			));
		}
		$params = $route->getParams();

		$context->getRequest()->setProp(self::CURRENT_ROUTE, $route);
		$context->getRequest()->setProp(self::ROUTE_PARAMS, $params);

		// Force pages to their designated protocol if specified
		if ($route->getProtocol() != null) {
			if ($currentProtocol != $route->getProtocol()) {
				$url = Uri::get(sprintf("%s://%s/", $route->getProtocol(), $currentHost))
						->join($currentUrl)
						->addParams($_GET);
				throw new RedirectException('Change Protocol', 301, $url);
			}
		}

		return $this->wrappedApp->call($context);
	}
}
