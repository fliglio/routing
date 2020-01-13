<?php
namespace Fliglio\Routing;

use Fliglio\Web\Url;
use Fliglio\Flfc\Context;
use Fliglio\Flfc\Apps\MiddleWare;
use Fliglio\Flfc\Apps\App;
use Fliglio\Flfc\Exceptions\RedirectException;
use Fliglio\Http\Exceptions\NotFoundException;

class RoutingApp extends MiddleWare {

	const CURRENT_ROUTE = 'currentRoute';
	const ROUTE_PARAMS  = 'routeParams';

	public function __construct(App $appToWrap, RouteMap $routeMap) {
		parent::__construct($appToWrap);
		$this->routeMap = $routeMap;
	}
	
	public function call(Context $context) {
		$currentHost     = $context->getRequest()->getHost();
		$currentProtocol = $context->getRequest()->getProtocol();
		$currentUrl      = $context->getRequest()->getUrl();
		$currentMethod   = $context->getRequest()->getHttpMethod();

		// Identify current Command; register RouteMap & params with Context
		$route = null;
		try {
			$route = $this->routeMap->getRoute($context->getRequest());
		} catch (RouteException $e) {
			throw new NotFoundException(sprintf(
				"Route not found for request: %s %s://%s%s",
				$currentMethod, $currentProtocol, $currentHost, $currentUrl 
			));
		}

		$context->setProp(self::CURRENT_ROUTE, $route);

		// Force pages to their designated protocol if specified
		if ($route->getProtocol() != null) {
			if ($currentProtocol != $route->getProtocol()) {
				$getParams = implode("&", $_GET);
				$getParams = !empty($getParams) ? "?".$getParams : "";

				/** @var Url $url */
				$url = Url::fromString(sprintf("%s://%s%s", $route->getProtocol(), $currentHost, $currentUrl));

				throw new RedirectException('Change Protocol', 301, $url);
			}
		}

		$this->wrappedApp->call($context);
	}
}
