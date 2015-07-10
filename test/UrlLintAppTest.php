<?php
namespace Fliglio\Routing;


use Fliglio\Web\Url;
use Fliglio\Http\Http;
use Fliglio\Http\Exceptions\MovedPermanentlyException;
use Fliglio\Flfc\Apps\App;
use Fliglio\Flfc\Context;
use Fliglio\Flfc\Request;
use Fliglio\Flfc\Response;
use Fliglio\Routing\Type\RouteBuilder;

class UrlLintAppTest extends \PHPUnit_Framework_TestCase {

	private function createContext(Url $url) {
		$request = new Request();
		$request->setHttpMethod(Http::METHOD_GET);
		$request->setUrl($url);
		return new Context($request, new Response());
	}

	public function testGoodUrlPassesThrough() {
		// given
		$url = Url::fromString('/foo/bar');
		$sapp = new StubApp();
		$app = new UrlLintApp($sapp);
		$ctx = $this->createContext($url);

		// when
		$app->call($ctx);

		// then
		$this->assertEquals(1, $sapp->called);
		$this->assertEquals($url, $ctx->getRequest()->getUrl());
	}
	/**
	 * @expectedException Fliglio\Http\Exceptions\MovedPermanentlyException
	 */
	public function testTrailingSlashCausesRedirect() {
		// given
		$app = new UrlLintApp(new StubApp());
		$ctx = $this->createContext(Url::fromString('/foo/bar/'));

		// when
		$app->call($ctx);
	}

	public function testTrimsTrailingSlash() {
		// given
		$app = new UrlLintApp(new StubApp(), false);
		$ctx = $this->createContext(Url::fromString('/foo/bar/'));

		// when
		$app->call($ctx);

		$this->assertEquals('/foo/bar', (string)$ctx->getRequest()->getUrl());
	}
	
	public function testTrimsRepeatedSlashes() {
		// given
		$app = new UrlLintApp(new StubApp(), false);
		$ctx = $this->createContext(Url::fromString('/foo//bar'));

		// when
		$app->call($ctx);

		$this->assertEquals('/foo/bar', (string)$ctx->getRequest()->getUrl());
			
	}
	
	public function testRetainQueryString() {
		// given
		$app = new UrlLintApp(new StubApp());
		$ctx = $this->createContext(Url::fromString('/foo/bar/'));
		$ctx->getRequest()->setGetParams(['foo' => 'bar', 'baz' => 'bin']);

		// when
		$loc = '';
		try {
			$app->call($ctx);
		} catch (MovedPermanentlyException $e) {
			$loc = $e->getLocation();
		}

		//then
		$this->assertEquals('/foo/bar?foo=bar&baz=bin', (string)$loc);
	}

	public function testNoRedirectOnRootPage() {
		// given
		$app = new UrlLintApp(new StubApp());
		$ctx = $this->createContext(Url::fromString('/'));

		// when
		$app->call($ctx);

		// then
		$this->assertTrue(true);
	}

}
