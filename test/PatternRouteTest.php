<?php

require_once dirname(dirname(__FILE__)).'/vendor/autoload.php';

use Fliglio\Web\Uri;
use Fliglio\Routing\RouteMap;
use Fliglio\Routing\PatternRoute;

class PatternRouteTest extends PHPUnit_Framework_TestCase {

	public function testRouteMapParams() {
		$routeMap = new RouteMap();
		$routeMap->connect('test', new PatternRoute('/api/rest/:command', array(
			'ns'           => 'TestApp\Example',
			'commandGroup' => 'Services',
		)));

		$params = $routeMap->getRoute(new Uri('/api/rest/methodName'))->getParams();

		$this->assertEquals($params, array(
			'command'      => 'methodName',
			'ns'           => 'TestApp\Example',
			'commandGroup' => 'Services',
		));
	}

}

