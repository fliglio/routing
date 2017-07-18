<?php

use Fliglio\Http\Http;
use Fliglio\Routing\RouteMap;
use Fliglio\Routing\PatternRoute;
use Fliglio\Routing\Type\RouteBuilder;
use Fliglio\Flfc\Request;

class RouteBuilderTest extends PHPUnit_Framework_TestCase {

	public function setup() {
		$this->routeMap = new RouteMap();

		$this->routeMap->connect('test', RouteBuilder::get()
				->uri('/foo/:id')
				->method(Http::METHOD_GET)
				->command('Fliglio\Routing.StubResource.getFlub')
				->protocol(Http::HTTPS)
				->param('bar', 'baz')
				->build()
			)
			->connect(null, RouteBuilder::get()
				->key('test2')
				->uri('/foo2/:id')
				->method(Http::METHOD_GET)
				->command('Fliglio\Routing.StubResource.getFlub2')
				->protocol(Http::HTTPS)
				->param('bar', 'baz')
				->build()
			)
			->connectRoute(RouteBuilder::get()
				->key('test3')
				->uri('/foo3/:id')
				->method(Http::METHOD_GET)
				->command('Fliglio\Routing.StubResource.getFlub3')
				->protocol(Http::HTTPS)
				->param('bar', 'baz')
				->build()
			);
	}

	public function testUrlFor_StaticRoute() {
		// given
		$this->routeMap->connect(__METHOD__, RouteBuilder::get()
			->uri('/foo/bar/baz')
			->command('Fliglio\Routing.StubResource.getFlub')
			->build()
		);

		// when
		$route = $this->routeMap->urlFor(__METHOD__, ['id' => 'bar']);

		// then
		$this->assertEquals((string)$route, '/foo/bar/baz?id=bar');
	}

	public function testUrlFor_PatternRoute() {
		// when
		$route = $this->routeMap->urlFor('test', ['id' => 'bar']);

		// then
		$this->assertEquals((string)$route, '/foo/bar');
	}

	public function testUrlFor_MultipleRouteParams() {
		// given
		$this->routeMap->connect(__METHOD__, RouteBuilder::get()
			->uri('/foo/:param1/:param2/static/:param3')
			->command('Fliglio\Routing.StubResource.getFlub')
			->build()
		);

		// when
		$route = $this->routeMap->urlFor(__METHOD__, [
			'param1' => 'foo', 
			'param2' => 'bar', 
			'param3' => 'baz',
			'dog'    => '  pup *&@#!',
			'cat'    => 'kitten',
		]);

		// then
		$this->assertEquals((string)$route, '/foo/foo/bar/static/baz?dog=++pup+%2A%26%40%23%21&cat=kitten');
	}

	/**
	 * @expectedException Fliglio\Routing\RouteException
	 */
	public function testUrlFor_InvalidKey() {
		// when
		$this->routeMap->urlFor('doesntExist', ['id' => 'bar']);
	}

	/**
	 * @expectedException Fliglio\Routing\RouteException
	 */
	public function testUrlFor_InvalidRouteParam() {
		// when
		$this->routeMap->urlFor('test', ['badkey' => 'bar']);
	}

	public function testBuilder() {
		// given
		$req = new Request();
		$req->setUrl('/foo/123');
		$req->setHttpMethod(Http::METHOD_GET);
		$req->setProtocol(Http::HTTPS);

		// when
		$route = $this->routeMap->getRoute($req);

		// then
		$params = $route->getParams();

		$this->assertEquals($params, array(
			'id' => 123,
			'bar' => 'baz'
		));
		$this->assertEquals('Fliglio\Routing\StubResource', get_class($route->getResourceInstance()));
		$this->assertEquals('getFlub', $route->getResourceMethod());

		// and
		$req->setUrl('/foo2/123');
		$route = $this->routeMap->getRoute($req);
		$this->assertEquals('getFlub2', $route->getResourceMethod());

		// and
		$req->setUrl('/foo3/123');
		$route = $this->routeMap->getRoute($req);
		$this->assertEquals('getFlub3', $route->getResourceMethod());
	}

}

