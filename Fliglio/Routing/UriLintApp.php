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
class UriLintApp extends MiddleWare {
	
	public function call(Context $context) {
		$currentUrl = $context->getRequest()->getCurrentUrl();
		
		// Strip trailing "/", adding back in namespace if necessary
		if (HttpAttributes::getMethod() == HttpAttributes::METHOD_GET) {
			if (substr($currentUrl, -1) == '/' && $currentUrl != '/') {

				$url = Uri::get(sprintf("%s://%s/", HttpAttributes::getProtocol(), HttpAttributes::getHttpHost()))
						->join(rtrim($currentUrl, '/'))
						->addParams($_GET);
			
				throw new RedirectException("stripping trailing slash", 301, $url);
			}
		}

		$this->wrappedApp->call($context);
	}
}
