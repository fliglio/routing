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

		$routeMap->connect('test', RouteBuilder::get()
				->uri('/foo/:id')
				->method(Http::METHOD_GET)
				->command('Fliglio\Routing.StubResource.getFlub')
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
	}

}

