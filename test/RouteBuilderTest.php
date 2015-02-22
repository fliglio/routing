<?php


use Fliglio\Web\Uri;
use Fliglio\Web\HttpAttributes;
use Fliglio\Routing\RouteMap;
use Fliglio\Routing\PatternRoute;
use Fliglio\Routing\RouteBuilder;

class RouteBuilderTest extends PHPUnit_Framework_TestCase {

	public function testBuilder() {
		$routeMap = new RouteMap();
		$routeMap->connect('test', RouteBuilder::get()
				->uri('/foo/:id')
				->method(HttpAttributes::METHOD_GET)
				->command('TestApp\Example.FooController.getFoo')
				->protocol(HttpAttributes::HTTPS)
				->param('bar', 'baz')
				->build()
			);

		$route = $routeMap->getRoute(new Uri('/foo/123'), HttpAttributes::METHOD_GET);
		$params = $route->getParams();

		$this->assertEquals($params, array(
			'id' => 123,
			'bar' => 'baz'
		));

		$this->assertEquals('TestApp\Example.FooController.getFoo', $route->getCommand());
	}

}
