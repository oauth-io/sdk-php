<?php

require_once __DIR__ . '/../vendor/autoload.php';

use OAuth_io\OAuth;
use OAuth_io\Injector;

class TokenGenerationTest extends PHPUnit_Framework_TestCase {
	protected $oauth;
	protected $injector;

	protected function setUp() {
		$this->injector = new Injector();
		$this->injector->session = array();
		$this->injector = new Injector();
		$this->injector->session = array(
			'hello' => 'world'
		);
		Injector::setInstance($this->injector);
		$this->oauth = new OAuth();
		$this->oauth->initialize('somekey', 'somesecret');
	}

	public function testTokenGeneratorExists() {
		$this->assertTrue(method_exists($this->oauth, 'generateStateToken'));
	}

	public function testTokenGeneratorResultFormat() {
		if (method_exists($this->oauth, 'generateStateToken')) {

			$token1 = $this->oauth->generateStateToken();
			$token2 = $this->oauth->generateStateToken();

			$this->assertTrue(is_string($token1) && is_string($token2));
			$this->assertTrue($token1 !== $token2);
		} else {
			$this->fail('$this->oauth->generateStateToken does not exist');
		}
	}

	public function testTokenGeneratorSessionStorage() {
		if (true || method_exists($this->oauth, 'generateStateToken')) {


			$token1 = $this->oauth->generateStateToken();

			$this->assertTrue(isset($this->injector->session["oauthio"]["tokens"][0]));
			$this->assertEquals($this->injector->session["oauthio"]["tokens"][0], $token1);

			$token2 = $this->oauth->generateStateToken();
			$this->assertTrue(isset($this->injector->session["oauthio"]["tokens"][0]));
			$this->assertEquals($this->injector->session["oauthio"]["tokens"][0], $token2);

			$this->assertTrue(isset($this->injector->session["oauthio"]["tokens"][1]));
			$this->assertEquals($this->injector->session["oauthio"]["tokens"][1], $token1);
		}
	}
}