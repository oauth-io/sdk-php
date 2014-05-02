<?php
require_once __DIR__ . '/../vendor/autoload.php';

use OAuth_io\OAuth;
use OAuth_io\Injector;
use HTTP\Request2;
use HTTP\Request2\Adapter\Mock;

class AuthTest extends PHPUnit_Framework_TestCase {
    protected $oauth;
    protected $token;
    protected $adapter_mock;
    protected $session;
    protected $injector;
    
    protected function setUp() {
        $this->adapter_mock = new HTTP_Request2_Adapter_Mock();
        $this->injector = $this->getMockBuilder('OAuth_io\Injector')->getMock();
        OAuth_io\Injector::setInstance($this->injector);
        $this->request_mock = $this->getMockBuilder('\HTTP_Request2')->setMethods(null)->getMock();
        $this->request_mock->setConfig(array(
            'adapter' => $this->adapter_mock
        ));
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
            $response = array(
                'access_token' => 'someaccesstoken',
                'state' => $this->token,
                'provider' => 'some_provider'
            );
            
            $this->adapter_mock->addResponse("HTTP/1.1 200 OK\r\n" . "Content-Type: application/json\r\n" . "\r\n" . json_encode($response) , "https://oauth.io/auth/token", 'https://oauth.io/auth/token');
            $result = $this->oauth->auth('somecode');
            $this->assertEquals(http_build_query($fields) , $this->request_mock->getBody());
            $this->assertEquals('POST', $this->request_mock->getMethod());
            $this->assertTrue(is_array($result));
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
            $response = array(
                'access_token' => 'someaccesstoken',
                'state' => $this->token,
                'provider' => 'blabla'
            );
            
            $this->adapter_mock->addResponse("HTTP/1.1 200 OK\r\n" . "Content-Type: application/json\r\n" . "\r\n" . json_encode($response) , "https://oauth.io/auth/token", 'https://oauth.io/auth/token');
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
