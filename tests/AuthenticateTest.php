<?php

require_once __DIR__ . '/../vendor/autoload.php';

use OAuth_io\OAuth;

class AuthenticateTest extends PHPUnit_Framework_TestCase {

	protected $oauth;
	protected $token;

	protected function setUp() {
		$this->oauth = new OAuth();
		$this->oauth->initialize('somekey', 'somesecret');
		$this->token = $this->oauth->generateToken();
	}

	public function testAuthenticateMethodExists() {
		$this->assertTrue(method_exists('OAuth', 'authenticate'));
	}

	public function testAuthenticateMethodCallsOauthioWithCredentialsAndCode() {
		$http = $this->getMockBuilder('OAuth_io\CurlHttpRequest')
			->getMock();
		
		$http->expects($this->once())
			->method('setOption')
			->with(CURLOPT_URL, 'https://oauth.io/auth/token');

		$http->expects($this->once())
			->method('setOption')
			->with(CURLOPT_POST, 3);

		$fields = array(
			'code' => 'somecode',
			'key' => 'somekey',
			'secret' => 'somesecret'
		);

		$http->expects($this->once())
			->method('setOption')
			->with(CURLOPT_POSTFIELDS, http_build_query($fields));

		$response = array(
			'access_token' => 'someaccesstoken',
			'state' => $this->token
		);

		$http->expects($this->once())
			->method('execute')
			->will($this->returnValue(json_encode($response)));
		
		$result = $this->oauth->authenticate('somecode', $http);

		$this->assertTrue(is_array($result));
		$this->assertEquals($result['access_token'], 'someaccesstoken');
		$this->assertEquals($result['state'], $this->token);
	}
}