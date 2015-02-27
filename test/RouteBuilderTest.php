<?php


use Fliglio\Web\Url;
use Fliglio\Http\Http;
use Fliglio\Routing\RouteMap;
use Fliglio\Routing\PatternRoute;
use Fliglio\Routing\Type\RouteBuilder;
use FLiglio\Flfc\Request;

class RouteBuilderTest extends PHPUnit_Framework_TestCase {

	public function testBuilder() {
		$routeMap = new RouteMap();
		$routeMap->connect('test', RouteBuilder::get()
				->uri('/foo/:id')
				->method(Http::METHOD_GET)
				->command('TestApp\Example.FooController.getFoo')
				->protocol(Http::HTTPS)
				->param('bar', 'baz')
				->build()
			);

		$req = new Request();
		$req->setUrl(new Url('/foo/123'));
		$req->setHttpMethod(Http::METHOD_GET);

		$route = $routeMap->getRoute($req);
		$params = $route->getParams();

		$this->assertEquals($params, array(
			'id' => 123,
			'bar' => 'baz'
		));

		$this->assertEquals('TestApp\Example.FooController.getFoo', $route->getCommand());
	}

}

