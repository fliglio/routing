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

class DefaultInvokerTest extends \PHPUnit_Framework_TestCase {

	private $request;
	private $context;
	private $routeMap;

	public function setup() {
		$this->request = new Request();
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
			->connect('ex2', RouteBuilder::get()
				->uri('/bar/:id')
				->command('Fliglio\Routing.StubResourceChild.getFoo')
				->method(Http::METHOD_GET)
				->build()
			)
			->connect('bad', RouteBuilder::get()
				->uri('/baz/:id')
				->command('Fliglio\Routing.StubResource.getFlub')
				->method(Http::METHOD_GET)
				->build()
			)
			->connect('catchNone', RouteBuilder::get()
				->uri('')
				->command('Fliglio\Routing.StubResource.getCatchNone')
				->method(Http::METHOD_GET)
				->build()
			)
			->connect('catchAll', RouteBuilder::get()
				->uri('*')
				->command('Fliglio\Routing.StubResource.getCatchAll')
				->method(Http::METHOD_GET)
				->build()
			);

	}

	public function testCatchAll() {
		$this->request->setUrl('/route/does/not/exist');

		$app = new RoutingApp(new DefaultInvokerApp(), $this->routeMap);

		// when
		$app->call($this->context);
		$result = $this->context->getResponse()->getBody()->getContent();

		// then
		$this->assertEquals('Not Found', $result);
	}

	public function testCatchNone() {
		$this->request->setUrl('');

		$app = new RoutingApp(new DefaultInvokerApp(), $this->routeMap);

		// when
		$app->call($this->context);
		$result = $this->context->getResponse()->getBody()->getContent();

		// then
		$this->assertEquals('None', $result);
	}

	public function testRequestInjection() {
		$this->request->setUrl('/foo/123');

		$app = new RoutingApp(new DefaultInvokerApp(), $this->routeMap);

		// when
		$app->call($this->context);
		$result = $this->context->getResponse()->getBody()->getContent();

		// then
		$this->assertEquals(Http::METHOD_GET, $result['method']);
	}

	public function testPathParamInjection() {
		$this->request->setUrl('/foo/123');

		$app = new RoutingApp(new DefaultInvokerApp(), $this->routeMap);

		// when
		$app->call($this->context);
		$result = $this->context->getResponse()->getBody()->getContent();

		// then
		$this->assertEquals('123', $result['id']);
	}

	public function testGetParamInjection() {
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
		$this->request->setUrl('/foo/123');

		$app = new RoutingApp(new DefaultInvokerApp(), $this->routeMap);

		// when
		$app->call($this->context);
		$result = $this->context->getResponse()->getBody()->getContent();

		// then
		$this->assertEquals(null, $result['type']);
	}

	public function testInheritance() {
		$this->request->setUrl('/bar/123');

		$app = new RoutingApp(new DefaultInvokerApp(), $this->routeMap);

		// when
		$app->call($this->context);
		$result = $this->context->getResponse()->getBody()->getContent();

		// then
		$this->assertEquals(Http::METHOD_GET, $result['method']);
	}

	public function testBodyInjection() {
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

	/**
	 * @expectedException Fliglio\Flfc\Exceptions\CommandNotFoundException
	 */
	public function testMethodNotFound() {
		$this->request->setUrl('/baz/123');

		$app = new RoutingApp(new DefaultInvokerApp(), $this->routeMap);

		// when
		$app->call($this->context);
	}

}
