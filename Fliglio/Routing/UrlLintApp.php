<?php
namespace Fliglio\Routing;

use Fliglio\Web\Url;
use Fliglio\Web\HttpAttributes;
use Fliglio\Flfc\Apps\MiddleWare;
use Fliglio\Flfc\Context;
use Fliglio\Flfc\Exceptions\RedirectException;

/**
 * 
 */
class UrlLintApp extends MiddleWare {
	
	public function call(Context $context) {
		$currentUrl = $context->getRequest()->getUrl();
		$currentMethod = $context->getRequest()->getHttpMethod();
		// Strip trailing "/", adding back in namespace if necessary
		if ($currentMethod == HttpAttributes::METHOD_GET) {
			if (substr($currentUrl, -1) == '/' && $currentUrl != '/') {

				$protocol = $context->getRequest()->getProtocol();
				$host = $context->getRequest()->getHost();
				$url = Url::fromString(sprintf("%s://%s/", $protocol, $host))
						->join(rtrim($currentUrl, '/'))
						->addParams($_GET);
			
				throw new RedirectException("stripping trailing slash", 301, $url);
			}
		}

		$this->wrappedApp->call($context);
	}
}
