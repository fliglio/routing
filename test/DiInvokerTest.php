<?php
namespace Fliglio\Routing;


use Fliglio\Web\Uri;
use Fliglio\Web\HttpAttributes;
use Fliglio\Flfc\Exceptions\RedirectException;
use Fliglio\Flfc\Apps\App;
use Fliglio\Flfc\Context;
use Fliglio\Flfc\Request;
use Fliglio\Flfc\Response;
use Fliglio\Routing\Type\RouteBuilder;
use Fliglio\Routing\RouteMap;
use Fliglio\Routing\RoutingApp;

class DiInvokerTest extends \PHPUnit_Framework_TestCase {

	private $request;
	private $context;
	private $routeMap;

	public function setup() {
		$this->request = new Request();
		$this->context = new Context($this->request, new Response());

		$this->routeMap = new RouteMap();
		$this->routeMap
			->connect('ex', RouteBuilder::get()
				->uri('/foo/:id')
				->command('Fliglio\RestFc.StubResource.getFoo')
				->method(HttpAttributes::METHOD_GET)
				->build()
			);

	}

	public function testRouteParam() {
		$this->request->setCurrentUrl('/foo/123');

		$app = new RoutingApp(new DiInvokerApp(), $this->routeMap);

		// when
		$result = $app->call($this->context);
		
		// then
		$this->assertEquals('123', $result['id']);
	}

	public function testGetParam() {
		$_GET['type'] = "foo";
		$this->request->setCurrentUrl('/foo/123');

		$app = new RoutingApp(new DiInvokerApp(), $this->routeMap);

		// when
		$result = $app->call($this->context);
		
		// then
		$this->assertEquals('foo', $result['type']);
	}

	public function testOptionalGetParam() {
		unset($_GET['type']);
		$this->request->setCurrentUrl('/foo/123');

		$app = new RoutingApp(new DiInvokerApp(), $this->routeMap);

		// when
		$result = $app->call($this->context);
		
		// then
		$this->assertEquals(null, $result['type']);
	}
}
