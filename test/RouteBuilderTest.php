<?php


use Fliglio\Http\Http;
use Fliglio\Routing\RouteMap;
use Fliglio\Routing\PatternRoute;
use Fliglio\Routing\Type\RouteBuilder;
use Fliglio\Flfc\Request;

class RouteBuilderTest extends PHPUnit_Framework_TestCase {

	public function testBuilder() {

		// given
		$routeMap = new RouteMap();

		$req = new Request();
		$req->setUrl('/foo/123');
		$req->setHttpMethod(Http::METHOD_GET);
		$req->setProtocol(Http::HTTPS);
		// when

		$routeMap
			->connect('test', RouteBuilder::get()
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


		// then
		$route = $routeMap->getRoute($req);
		$params = $route->getParams();

		$this->assertEquals($params, array(
			'id' => 123,
			'bar' => 'baz'
		));
		$this->assertEquals('Fliglio\Routing\StubResource', get_class($route->getResourceInstance()));
		$this->assertEquals('getFlub', $route->getResourceMethod());

		// and
		$req->setUrl('/foo2/123');
		$route = $routeMap->getRoute($req);
		$this->assertEquals('getFlub2', $route->getResourceMethod());
	
		// and
		$req->setUrl('/foo3/123');
		$route = $routeMap->getRoute($req);
		$this->assertEquals('getFlub3', $route->getResourceMethod());
	}

}

