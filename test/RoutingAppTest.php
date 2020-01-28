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
				->command('Fliglio\Routing.StubResource.getFoo')
				->method(Http::METHOD_GET)
				->build()
			)
			->connect("staticEx", RouteBuilder::get()
				->uri('/foo')
				->command('Fliglio\Routing.StubResource.getFoo')
				->method(Http::METHOD_GET)
				->build()
			)
			->connect("error", RouteBuilder::get()
				->catchNone()
				->command('Fliglio\Routing.StubResource.error')
				->build()
			)
			->connect("404", RouteBuilder::get()
				->catchAll()
				->command('Fliglio\Routing.StubResource.dne')
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

		$this->assertEquals('Fliglio\Routing\StubResource', get_class($route->getResourceInstance()));
		$this->assertEquals('getFoo', $route->getResourceMethod());
	}

	public function testStaticRoute() {
		$route = $this->getRouteFromUrl('/foo');

		$this->assertEquals('Fliglio\Routing\StubResource', get_class($route->getResourceInstance()));
		$this->assertEquals('getFoo', $route->getResourceMethod());
	}

	public function testCatchNoneParams() {
		$route = $this->getRouteFromUrl('@error');

		$this->assertEquals('Fliglio\Routing\StubResource', get_class($route->getResourceInstance()));
		$this->assertEquals('error', $route->getResourceMethod());
	}

	/** @expectedException Fliglio\Http\Exceptions\NotFoundException */
	public function testGetRouteByKey_whenDoesNotExist() {
		// when
		$this->getRouteFromUrl('@foo');
	}

	public function testCatchAllParams() {
		$route = $this->getRouteFromUrl('/dne');

		$this->assertEquals('Fliglio\Routing\StubResource', get_class($route->getResourceInstance()));
		$this->assertEquals('dne', $route->getResourceMethod());
	}

}

