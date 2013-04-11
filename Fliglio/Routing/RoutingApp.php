<?php
namespace Fliglio\Routing;

use Fliglio\Web\Uri;
use Fliglio\Web\HttpAttributes;
use Fliglio\Flfc\MiddleWare;
use Fliglio\Flfc\Context;
use Fliglio\Flfc\RedirectException;

/**
 * 
 */
class RoutingApp extends MiddleWare {
	
	public function call(Context $context) {
		$currentUrl = $context->getRequest()->getCurrentUrl();
		
		/* Strip trailing "/", adding back in namespace if necessary
		 */
		if (substr($currentUrl, -1) == '/' && $currentUrl != '/') {
			$url = new Uri(sprintf("%s://%s/", HttpAttributes::getProtocol(), HttpAttributes::getHttpHost()));
			$url->join(rtrim($currentUrl, '/'));
			
			$getParams = $context->getRequest()->getParams();
			if (isset($getParams["fliglio_request"])) {
				unset($getParams["fliglio_request"]);
			}
			if (isset($getParams["PHPSESSID"])) {
				unset($getParams["PHPSESSID"]);
			}
			
			$url = Uri::merge($url, $getParams);
			
			throw new RedirectException("stripping trailing slash", 301, $url);
		}
	
		/* Register RouteMap with Context. Identify current Command
		 */
		$routeMap = new RouteMap();
		$route = $routeMap->getRoute($currentUrl);
		
		
		/* Register Route Parameters
		 */
		$params = $route->getParams();
		$context->getRequest()->setProp('currentRoute', $route);
		$context->getRequest()->setProp('routeParams', $params);

		/* Force pages to their designated protocol (https is default) =======
		 */
		if(HttpAttributes::getProtocol() != $route->getProtocol()) {
			$url = new Uri(sprintf("%s://%s/", $route->getProtocol(), HttpAttributes::getHttpHost()));
			$url->join($context->getRequest()->getCurrentUrl());
			throw new RedirectException('Change Protocol', 301, $url);
		}
		// ===================================================================

		/* Register command
		 */
		$restfulFlag = $route->isRestful() ? "!" : "";
		$context->getRequest()->setCommand($params['ns'] . '.' .  $params['commandGroup'] . '.' . $params['command'] . $restfulFlag);
		$this->wrappedApp->call($context);
	}
}
