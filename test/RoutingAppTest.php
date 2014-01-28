<?php

require_once dirname(dirname(__FILE__)).'/vendor/autoload.php';

use Fliglio\Web\Uri;
use Fliglio\Routing\RouteMap;
use Fliglio\Routing\PatternRoute;
use Fliglio\Routing\StaticRoute;
use Fliglio\Routing\CatchNoneRoute;
use Fliglio\Routing\RoutingApp;
use Fliglio\Flfc\RedirectException;
use Fliglio\Flfc\App;
use Fliglio\Flfc\Context;
use Fliglio\Flfc\Request;
use Fliglio\Flfc\Response;

class RoutingAppTest extends PHPUnit_Framework_TestCase {

	private $request;
	private $context;
	private $routeMap;

	public function setup() {
		$this->request = new Request();
		$this->context = new Context($this->request, new Response());

		$this->routeMap = new RouteMap();
		$this->routeMap
			->connect('test', new PatternRoute('/api/rest/:command', array(
				'ns'           => 'TestApp',
				'commandGroup' => 'Services',
			)))
			->connect("test2", new StaticRoute('/api/static/baz', array(
				'cmd' => 'MyApp\Example.Services.baz',
			)))
			->connect("error", new CatchNoneRoute(array(
				'cmd' => 'MyApp\Example.PageNotFound.handleError',
			)));
	}

	public function testPatternRouteParams() {
		$this->request->setCurrentUrl('/api/rest/method');

		$app = new RoutingApp(new StubApp, $this->routeMap);

		$app->call($this->context);

		$params = $this->context->getRequest()->getProp(RoutingApp::ROUTE_PARAMS);

		$this->assertEquals($params['command'], 'method');
		$this->assertEquals($params['commandGroup'], 'Services');
		$this->assertEquals($params['ns'], 'TestApp');
	}

	public function testStaticRouteParams() {
		$this->request->setCurrentUrl('/api/static/baz');

		$app = new RoutingApp(new StubApp, $this->routeMap);

		$app->call($this->context);

		$params = $this->context->getRequest()->getProp(RoutingApp::ROUTE_PARAMS);

		$this->assertEquals($params['command'], 'baz');
		$this->assertEquals($params['commandGroup'], 'Services');
		$this->assertEquals($params['ns'], 'MyApp\Example');
	}

	public function testRouteGetParams() {
		$this->request->setCurrentUrl('/api/static/baz?var1=value');

		$app = new RoutingApp(new StubApp, $this->routeMap);

		$app->call($this->context);

		$params = $this->context->getRequest()->getProp(RoutingApp::ROUTE_PARAMS);

		$this->assertEquals($params['command'], 'baz');
		$this->assertEquals($params['commandGroup'], 'Services');
		$this->assertEquals($params['ns'], 'MyApp\Example');
	}

	/**
	 * @expectedException Fliglio\Routing\RouteException
	 */
	public function testCatchAllParams() {
		$this->request->setCurrentUrl('/asdf');

		$app = new RoutingApp(new StubApp, $this->routeMap);

		$app->call($this->context);
	}

}

class StubApp extends App {
	public $called = false;
	public function call(Context $context) {
		$this->called = true;
	}
}