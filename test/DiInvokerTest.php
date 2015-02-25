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

class DiInvokerTest extends \PHPUnit_Framework_TestCase {

	private $request;
	private $context;
	private $routeMap;

	public function setup() {
		$this->request = new Request();
		$this->request->setProtocol(HttpAttributes::HTTP);
		$this->request->setHttpMethod(HttpAttributes::METHOD_GET);
		$this->context = new Context($this->request, new Response());

		$this->routeMap = new RouteMap();
		$this->routeMap
			->connect('ex', RouteBuilder::get()
				->uri('/foo/:id')
				->command('Fliglio\Routing.StubResource.getFoo')
				->method(HttpAttributes::METHOD_GET)
				->build()
			)
			->connect('ex2', RouteBuilder::get()
				->uri('/bar/:id')
				->command('Fliglio\Routing.StubResourceChild.getFoo')
				->method(HttpAttributes::METHOD_GET)
				->build()
			)
			->connect('bad', RouteBuilder::get()
				->uri('/baz/:id')
				->command('Fliglio\Routing.StubResource.getFlub')
				->method(HttpAttributes::METHOD_GET)
				->build()
			);

	}

	public function testRequestInjection() {
		$this->request->setUrl('/foo/123');

		$app = new RoutingApp(new DiInvokerApp(), $this->routeMap);

		// when
		$app->call($this->context);
		$result = $this->context->getProp('rawResponse');
		
		// then
		$this->assertEquals(HttpAttributes::METHOD_GET, $result['method']);
	}

	public function testRouteParamInjection() {
		$this->request->setUrl('/foo/123');

		$app = new RoutingApp(new DiInvokerApp(), $this->routeMap);

		// when
		$app->call($this->context);
		$result = $this->context->getProp('rawResponse');
		
		// then
		$this->assertEquals('123', $result['id']);
	}

	public function testGetParamInjection() {
		$_GET['type'] = "foo";
		$this->request->setUrl('/foo/123');

		$app = new RoutingApp(new DiInvokerApp(), $this->routeMap);

		// when
		$app->call($this->context);
		$result = $this->context->getProp('rawResponse');
		
		// then
		$this->assertEquals('foo', $result['type']);
	}

	public function testOptionalGetParamInjection() {
		unset($_GET['type']);
		$this->request->setUrl('/foo/123');

		$app = new RoutingApp(new DiInvokerApp(), $this->routeMap);

		// when
		$app->call($this->context);
		$result = $this->context->getProp('rawResponse');
		
		// then
		$this->assertEquals(null, $result['type']);
	}

	public function testInheritance() {
		$this->request->setUrl('/bar/123');

		$app = new RoutingApp(new DiInvokerApp(), $this->routeMap);

		// when
		$app->call($this->context);
		$result = $this->context->getProp('rawResponse');
		
		// then
		$this->assertEquals(HttpAttributes::METHOD_GET, $result['method']);
	}

	/**
	 * @expectedException Fliglio\Flfc\Exceptions\CommandNotFoundException
	 */
	public function testMethodNotFound() {
		$this->request->setUrl('/baz/123');

		$app = new RoutingApp(new DiInvokerApp(), $this->routeMap);

		// when
		$app->call($this->context);
	}

}
