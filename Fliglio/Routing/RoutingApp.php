<?php
namespace Fliglio\Routing;

use Fliglio\Web\Uri;
use Fliglio\Web\HttpAttributes;
use Fliglio\Flfc\MiddleWare;
use Fliglio\Flfc\Context;
use Fliglio\Flfc\RedirectException;
use Fliglio\Flfc\App;

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
		$currentUrl = $context->getRequest()->getCurrentUrl();

		// Identify current Command; register RouteMap & params with Context
		$route = $this->routeMap->getRoute(new Uri($currentUrl));
		$params = $route->getParams();

		$context->getRequest()->setProp(self::CURRENT_ROUTE, $route);
		$context->getRequest()->setProp(self::ROUTE_PARAMS, $params);

		// Force pages to their designated protocol if specified
		if (HttpAttributes::getMethod() == HttpAttributes::METHOD_GET) {
			if(HttpAttributes::getProtocol() != $route->getProtocol()) {
				$url = Uri::get(sprintf("%s://%s/", $route->getProtocol(), HttpAttributes::getHttpHost()))
						->join($currentUrl)
						->addParams($_GET);
				throw new RedirectException('Change Protocol', 301, $url);
			}
		}

		// Register command
		$restfulFlag = $route->isRestful() ? RestInvokerApp::FLAG : "";
		$context->getRequest()->setCommand($params['ns'] . '.' .  $params['commandGroup'] . '.' . $params['command'] . $restfulFlag);
		$this->wrappedApp->call($context);
	}
}
