<?php

require_once __DIR__ . '/../vendor/autoload.php';

use OAuth_io/OAuth;

class RequestsTest extendes PHPUnit_Framework_TestCase {
	protected $oauth;
	protected $result;

	protected function setUp() {
		$http = $this->getMockBuilder('OAuth_io\CurlHttpRequest')
			->getMock();

		$http->expects($this->once())
			->method('execute')
			->will($this->returnValue(json_encode($response)));

		$this->oauth = new OAuth();
		$this->oauth->initialize('somekey', 'somesecret');
		$this->result = $this->oauth->authenticate('somecode', $http);
	}

	public function testRequest() {
		
	}
}