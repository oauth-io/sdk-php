<?php
require_once __DIR__ . '/../vendor/autoload.php';

use OAuth_io\OAuth;
use OAuth_io\Injector;

class AuthTest extends PHPUnit_Framework_TestCase {
    protected $oauth;
    protected $token;
    protected $adapter_mock;
    protected $session;
    protected $injector;
    
    protected function setUp() {
        $this->injector = $this->getMockBuilder('OAuth_io\Injector')->getMock();
        OAuth_io\Injector::setInstance($this->injector);
        $this->request_mock = $this->getMockBuilder('OAuth_io\HttpWrapper')->getMock();

        $this->injector->expects($this->any())->method('getRequest')->will($this->returnValue($this->request_mock));
        
        $this->injector->session = array(
            'hello' => 'world'
        );
        $this->oauth = new OAuth();
        $this->oauth->initialize('somekey', 'somesecret');
        $this->token = $this->oauth->generateToken();
    }
    
    public function testAuthMethodExists() {
        $this->assertTrue(method_exists($this->oauth, 'auth'));
    }
    
    public function testAuthMethodCallsOauthioWithCredentialsAndCode() {
        if (method_exists($this->oauth, 'auth')) {
            $fields = array(
                'code' => 'somecode',
                'key' => 'somekey',
                'secret' => 'somesecret'
            );

            $res = new stdClass();
            $res->access_token = 'someaccesstoken';
            $res->state = $this->token;
            $res->provider = 'some_provider';
            $response = new StdClass();
            $response->body = $res;
           
            $this->request_mock->expects($this->once())->method('make_request')->will($this->returnValue($response));

            $result = $this->oauth->auth('somecode');
            $this->assertEquals($result['access_token'], 'someaccesstoken');
            $this->assertEquals($result['state'], $this->token);
        } else {
            $this->fail('OAuth::auth() does not exist');
        }
    }
    
    public function testAuthMethodSetsProviderFieldInSessions() {
        if (method_exists($this->oauth, 'auth')) {
            $fields = array(
                'code' => 'somecode',
                'key' => 'somekey',
                'secret' => 'somesecret'
            );
            $res = new stdClass();
            $res->access_token = 'someaccesstoken';
            $res->state = $this->token;
            $res->provider = 'blabla';
            $response = new StdClass();
            $response->body = $res;

            $this->request_mock->expects($this->once())->method('make_request')->will($this->returnValue($response));

            $result = $this->oauth->auth('somecode');
            $this->assertTrue(isset($this->injector->session['oauthio']['auth']['blabla']));
            $this->assertEquals('someaccesstoken', $this->injector->session['oauthio']['auth']['blabla']['access_token']);
            $this->assertEquals($this->token, $this->injector->session['oauthio']['auth']['blabla']['state']);
            $this->assertEquals('blabla', $this->injector->session['oauthio']['auth']['blabla']['provider']);
        } else {
            $this->fail('OAuth::auth() does not exist');
        }
    }
}
