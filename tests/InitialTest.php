<?php

require_once __DIR__ . '/../vendor/autoload.php';


use OAuth_io\OAuth;	



class InitialTest extends PHPUnit_Framework_TestCase {

	protected $oauth;

	protected function setUp() {
		$this->oauth = new OAuth();
	}

	public function testGetVersionReturnsVersion() {
		$version = json_decode(file_get_contents('./composer.json'), true);
		$version = $version["version"];
		
		$this->assertEquals($this->oauth->getVersion(), $version);
	}

	public function testInitializeSetsKeyAndSecret() {
		if (method_exists($this->oauth, 'initialize') && method_exists($this->oauth, 'getAppKey') && method_exists($this->oauth, 'getAppSecret')) {
			$this->oauth->initialize('somekey', 'somesecret');

			$this->assertEquals($this->oauth->getAppKey(), 'somekey');
			$this->assertEquals($this->oauth->getAppSecret(), 'somesecret');	
		} else {
			$this->fail('OAuth::initialize() does not exist');
		}
	}

	public function testOauthdUrlIsOauthioByDefault() {
		if (method_exists($this->oauth, 'initialize') && method_exists($this->oauth, 'setOAuthdUrl')) {

			$this->assertEquals('https://oauth.io', $this->oauth->getOAuthdUrl());

		} else {
			$this->fail('methods are missing');
		}
	}

	public function testSetOauthdUrlSetsUrlInObject() {
		if (method_exists($this->oauth, 'initialize') && method_exists($this->oauth, 'setOAuthdUrl')) {
			$this->oauth->initialize('somekey', 'somesecret');
			$this->oauth->setOAuthdUrl('https://oauthd.local');

			$this->assertEquals($this->oauth->getOAuthdUrl(), 'https://oauthd.local');

		} else {
			$this->fail('methods are missing');
		}
	}
}