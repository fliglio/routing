<?php

namespace Fliglio\Routing;

use Fliglio\Flfc\Context;
use Fliglio\Flfc\Request;
use Fliglio\Flfc\Response;
use Fliglio\Http\Http;
use Fliglio\Routing\Type\RouteBuilder;

class DefaultInvokerTest extends \PHPUnit_Framework_TestCase {

	/** @var  Request */
	private $request;
	private $context;
	private $routeMap;

	public function setup() {
		$this->request = new Request();
		$this->request->setHost("www.google.com");
		$this->request->setProtocol(Http::HTTP);
		$this->request->setHttpMethod(Http::METHOD_GET);
		$this->context = new Context($this->request, new Response());

		$this->routeMap = new RouteMap();
		$this->routeMap
			->connect('postEx', RouteBuilder::get()
				->uri('/foo')
				->command('Fliglio\Routing.StubResource.addFoo')
				->method(Http::METHOD_POST)
				->build()
			)
			->connect('ex', RouteBuilder::get()
				->uri('/foo/:id')
				->command('Fliglio\Routing.StubResource.getFoo')
				->method(Http::METHOD_GET)
				->build()
			)
			->connect('exE', RouteBuilder::get()
				->uri('/foo/:id/entity')
				->command('Fliglio\Routing.StubResource.getEntity')
				->method(Http::METHOD_POST)
				->build()
			)
			->connect('ex2', RouteBuilder::get()
				->uri('/bar/:id')
				->command('Fliglio\Routing.StubResourceChild.getFoo')
				->method(Http::METHOD_GET)
				->build()
			)
			->connect('proto', RouteBuilder::get()
				->uri('/biz/:id')
				->command('Fliglio\Routing.StubResourceChild.getFoo')
				->method(Http::METHOD_GET)
				->protocol(Http::HTTPS)
				->build()
			)
			->connect('bad', RouteBuilder::get()
				->uri('/baz/:id')
				->command('Fliglio\Routing.StubResource.getFlub')
				->method(Http::METHOD_GET)
				->protocol(Http::HTTP)
				->build()
			)
			->connect('par', RouteBuilder::get()
				->uri('/par/:id')
				->command('Fliglio\Routing.StubResourceChild.getFlub')
				->method(Http::METHOD_GET)
				->protocol(Http::HTTP)
				->build()
			)
			->connect('catchAll', RouteBuilder::get()
				->catchAll()
				->command('Fliglio\Routing.StubResource.getCatchAll')
				->method(Http::METHOD_GET)
				->build()
			);

	}

	/** @expectedException Fliglio\Routing\RouteException */
	public function testConnectingDuplicateRoutes() {
		// when
		$this->routeMap->connect('postEx', 
			RouteBuilder::get()
				->uri('/foo')
				->command('Fliglio\Routing.StubResource.addFoo')
				->method(Http::METHOD_POST)
				->build()
		);
	}

	/** @expectedException Fliglio\Flfc\Exceptions\RedirectException */
	public function testChangeOfProtocol() {
		// given
		$this->request->setUrl('/biz/123');

		$app = new RoutingApp(new DefaultInvokerApp(), $this->routeMap);

		// when
		$app->call($this->context);

		// then
		$this->assertTrue(false);
	}

	/** @expectedException Fliglio\Flfc\Exceptions\RedirectException */
	public function testChangeOfProtocol_withQueryParams() {
		// given
		$this->request->setUrl('/biz/123?q=1&b=3');

		$app = new RoutingApp(new DefaultInvokerApp(), $this->routeMap);

		// when
		$app->call($this->context);

		// then
		$this->assertTrue(false);
	}

	public function testCatchAll() {
		// given
		$this->request->setUrl('/route/does/not/exist');

		$app = new RoutingApp(new DefaultInvokerApp(), $this->routeMap);

		// when
		$app->call($this->context);
		$result = $this->context->getResponse()->getBody()->getContent();

		// then
		$this->assertEquals('Not Found', $result);
	}

	/** @expectedException Fliglio\Http\Exceptions\NotFoundException */
	public function testRouteDoesNotExist() {
		// given
		$this->request->setUrl('/foo/123');

		$app = new RoutingApp(new DefaultInvokerApp(), RouteMap::get());

		// when
		$app->call($this->context);

		// then
		$this->assertTrue(false);
	}

	public function testRequestInjection() {
		// given
		$this->request->setUrl('/foo/123');

		$app = new RoutingApp(new DefaultInvokerApp(), $this->routeMap);

		// when
		$app->call($this->context);
		$result = $this->context->getResponse()->getBody()->getContent();

		// then
		$this->assertEquals(Http::METHOD_GET, $result['method']);
	}

	public function testPathParamInjection() {
		// given
		$this->request->setUrl('/foo/123');

		$app = new RoutingApp(new DefaultInvokerApp(), $this->routeMap);

		// when
		$app->call($this->context);
		$result = $this->context->getResponse()->getBody()->getContent();

		// then
		$this->assertEquals('123', $result['id']);
	}

	public function testGetParamInjection() {
		// given
		$this->request->setGetParams(array("type" => "foo"));
		$this->request->setUrl('/foo/123');

		$app = new RoutingApp(new DefaultInvokerApp(), $this->routeMap);

		// when
		$app->call($this->context);
		$result = $this->context->getResponse()->getBody()->getContent();

		// then
		$this->assertEquals('foo', $result['type']);
	}

	public function testOptionalGetParamInjection() {
		// given
		$this->request->setUrl('/foo/123');

		$app = new RoutingApp(new DefaultInvokerApp(), $this->routeMap);

		// when
		$app->call($this->context);
		$result = $this->context->getResponse()->getBody()->getContent();

		// then
		$this->assertEquals(null, $result['type']);
	}

	public function testInheritance() {
		// given
		$this->request->setUrl('/bar/123');

		$app = new RoutingApp(new DefaultInvokerApp(), $this->routeMap);

		// when
		$app->call($this->context);
		$result = $this->context->getResponse()->getBody()->getContent();

		// then
		$this->assertEquals(Http::METHOD_GET, $result['method']);
	}

	public function testEntityInjectionHeader() {
		// given
		$formData = 'foo=bar&baz=foo';

		$this->request->setUrl('/foo/321/entity');
		$this->request->setHttpMethod(Http::METHOD_POST);
		$this->request->setBody($formData);
		$this->request->addHeader('Content-Type', $type = uniqid());

		$app = new RoutingApp(new DefaultInvokerApp(), $this->routeMap);

		// when
		$app->call($this->context);
		$result = $this->context->getResponse()->getBody()->getContent();

		// then
		$this->assertEquals([
			'id' => 321,
			'body' => 'foo=bar&baz=foo',
			'contentType' => $type
		], $result);
	}

	public function testBodyInjection() {
		// given
		$json = '{"foo": "bar"}';

		$this->request->setUrl('/foo');
		$this->request->setHttpMethod(Http::METHOD_POST);
		$this->request->setBody($json);

		$app = new RoutingApp(new DefaultInvokerApp(), $this->routeMap);

		// when
		$app->call($this->context);
		$result = $this->context->getResponse()->getBody()->getContent();

		// then
		$this->assertEquals($result, $json);
	}

	/** @expectedException Fliglio\Flfc\Exceptions\CommandNotFoundException */
	public function testMethodNotFound() {
		// given
		$this->request->setUrl('/baz/123');

		$app = new RoutingApp(new DefaultInvokerApp(), $this->routeMap);

		// when
		$app->call($this->context);
	}

	/** @expectedException Fliglio\Flfc\Exceptions\CommandNotFoundException */
	public function testMethodNotFound_whenParent() {
		// given
		$this->request->setUrl('/par/123');

		$app = new RoutingApp(new DefaultInvokerApp(), $this->routeMap);

		// when
		$app->call($this->context);
	}

}
