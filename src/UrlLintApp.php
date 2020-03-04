<?php
namespace Fliglio\Routing;

use Fliglio\Web\Url;
use Fliglio\Http\Http;
use Fliglio\Flfc\Context;
use Fliglio\Flfc\Apps\App;
use Fliglio\Flfc\Apps\MiddleWare;
use Fliglio\Http\Exceptions\MovedPermanentlyException;

/**
 * - strip trailing slashes
 * - remove repeated slashes
 * - optionally perform redirect to url (e.g. for seo)
 */
class UrlLintApp extends MiddleWare {

	private $redirect;

	public function __construct(App $appToWrap, $redirect = true) {
		$this->wrappedApp = $appToWrap;
		$this->redirect = $redirect;
	}

	public function call(Context $context) {
		$currentUrl = $context->getRequest()->getUrl();
		$currentMethod = $context->getRequest()->getHttpMethod();

		if ($currentMethod == Http::METHOD_GET) {
			$lintedPath = $this->lintPath($currentUrl);

			if ((string)$currentUrl != $lintedPath && (string)$currentUrl != '/') {
				$protocol = $context->getRequest()->getProtocol();
				$host = $context->getRequest()->getHost();
				
				if ($this->redirect) {
					$url = Url::fromParts([
						'scheme' => $protocol,
						'host' => $host,
						'path' => $lintedPath,
						'query' => $this->arrayToQuery($context->getRequest()->getGetParams()),
					]);
					throw new MovedPermanentlyException($url);
				} else {
					$context->getRequest()->setUrl($lintedPath);
				}
			}
		}

		$this->wrappedApp->call($context);
	}

	private function lintPath($path) {
		$path = rtrim($path, '/');
		$path = preg_replace('#/+#','/',$path);
		return $path;
	}

	private function arrayToQuery($arr) {
		return http_build_query($arr);
	}
}
