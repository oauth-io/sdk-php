<?php

require_once __DIR__ . '/../vendor/autoload.php';

use OAuth_io\OAuth;

class TokenGenerationTest extends PHPUnit_Framework_TestCase {
	protected $oauth;

	protected function setUp() {
		$this->oauth = new OAuth();
	}

	public function testTokenGeneratorExists() {
		$this->assertTrue(method_exists('OAuth', 'generateToken'));
	}

	public function testTokenGeneratorResultFormat() {
		if (method_exists('OAuth', 'generateToken')) {
			$session = array();

			$token1 = $this->oauth->generateToken($session);
			$token2 = $this->oauth->generateToken($session);

			$this->assertTrue(is_string($token1) && is_string(token2));
			$this->assertTrue($token1 !== $token2);
		} else {
			$this->fail('$this->oauth->generateToken does not exist');
		}
	}

	public function testTokenGeneratorSessionStorage() {
		if (method_exists('OAuth', 'generateToken')) {
			$session = array();

			$token1 = $this->oauth->generateToken($session);

			$this->assertTrue(isset($session["oauthio"]["tokens"][0]));
			$this->assertEquals($session["oauthio"]["tokens"][0], $token1);

			$token2 = $this->oauth->generateToken($session);

			$this->assertTrue(isset($session["oauthio"]["tokens"][0]));
			$this->assertEquals($session["oauthio"]["tokens"][0], $token2);

			$this->assertTrue(isset($session["oauthio"]["tokens"][1]));
			$this->assertEquals($session["oauthio"]["tokens"][1], $token1);
		}
	}
}