<?php


namespace Fliglio\Routing;


use Fliglio\Flfc\Context;
use Fliglio\Flfc\Request;
use Fliglio\Flfc\Response;
use Fliglio\Http\Http;
use Fliglio\Routing\Type\RouteBuilder;

class FileUploadInjectorTest extends \PHPUnit_Framework_TestCase {

	/** @var  Request */
	private $request;
	private $context;
	private $routeMap;

	public function setup() {
		$this->request = new Request();
		$this->request->setHost("www.google.com");
		$this->request->setProtocol(Http::HTTP);
		$this->context = new Context($this->request, new Response());

		$this->routeMap = new RouteMap();
	}

	public function testFileUpload_injectable() {
		// given
		$this->routeMap
			->connect('uploadEx', RouteBuilder::get()
				->uri('/upload')
				->command('Fliglio\Routing.StubResource.uploadFile')
				->method(Http::METHOD_POST)
				->build()
			);
		// match the array format of the global $_FILES array
		$files = [ "fieldName" => [
			"name" => "foo.jpg",
			"type" => "image/jpg",
			"size" => 12345,
			"tmp_name" => "/tmp/123/asd23f",
			"error" => UPLOAD_ERR_OK,
		]];
		$this->request->setHttpMethod(Http::METHOD_POST);
		$this->request->setFiles($files);


		$this->request->setUrl('/upload');

		$app = new RoutingApp(new DefaultInvokerApp(), $this->routeMap);

		// when
		$app->call($this->context);
		$file = $this->context->getResponse()->getBody()->getContent();

		// then
		$this->assertEquals("foo.jpg", $file->getName());
		$this->assertEquals("image/jpg", $file->getType());
		$this->assertEquals(12345, $file->getSize());
		$this->assertEquals("/tmp/123/asd23f", $file->getTmpName());
		$this->assertEquals(UPLOAD_ERR_OK, $file->getError());

	}


}