<?php
namespace Fliglio\Routing;


use Fliglio\Web\Url;
use Fliglio\Http\Http;
use Fliglio\Flfc\Exceptions\RedirectException;
use Fliglio\Flfc\Apps\App;
use Fliglio\Flfc\Context;
use Fliglio\Flfc\Request;
use Fliglio\Flfc\Response;
use Fliglio\Routing\Type\RouteBuilder;

class RoutingAppTest extends \PHPUnit_Framework_TestCase {

	private $request;
	private $context;
	private $routeMap;

	public function setup() {
		$this->request = new Request();
		$this->request->setHttpMethod(Http::METHOD_GET);
		$this->context = new Context($this->request, new Response());

		$this->routeMap = new RouteMap();
		$this->routeMap
			->connect('patternEx', RouteBuilder::get()
				->uri('/foo/:id')
				->command('MyApp\Example.FooResource.getFoo')
				->method(Http::METHOD_GET)
				->build()
			)
			->connect("staticEx", RouteBuilder::get()
				->uri('/foo')
				->command('MyApp\Example.FooResource.getAllFoos')
				->method(Http::METHOD_GET)
				->build()
			)
			->connect("error", RouteBuilder::get()
				->catchNone()
				->command('MyApp\Example.ErrorResource.handleError')
				->build()
			)
			->connect("404", RouteBuilder::get()
				->catchAll()
				->command('MyApp\Example.ErrorResource.handlePageNotFound')
				->build()
			);
	}

	private function getRouteFromUrl($url) {
		$this->request->setUrl($url);

		$app = new RoutingApp(new StubApp, $this->routeMap);

		$app->call($this->context);

		return $this->context->getProp(RoutingApp::CURRENT_ROUTE);
	}

	public function testPatternRoute() {
		$route = $this->getRouteFromUrl('/foo/123');

		$this->assertEquals('MyApp\Example.FooResource.getFoo', $route->getCommand());
	}

	public function testStaticRoute() {
		$route = $this->getRouteFromUrl('/foo');

		$this->assertEquals('MyApp\Example.FooResource.getAllFoos', $route->getCommand());
	}

	public function testCatchNoneParams() {
		$route = $this->getRouteFromUrl('@error');

		$this->assertEquals('MyApp\Example.ErrorResource.handleError', $route->getCommand());
	}

	public function testCatchAllParams() {
		$route = $this->getRouteFromUrl('/dne');

		$this->assertEquals('MyApp\Example.ErrorResource.handlePageNotFound', $route->getCommand());
	}

}

class StubApp extends App {
	public $called = false;
	public function call(Context $context) {
		$this->called = true;
	}
}