<?php
require_once __DIR__ . '/../vendor/autoload.php';

use OAuth_io\OAuth;

class RequestsTest extends PHPUnit_Framework_TestCase {
    protected $oauth;
    protected $token;
    protected $adapter_mock;
    protected $session;
    protected $injector;
    
    protected function setUp() {
        $this->adapter_mock = new \HTTP_Request2_Adapter_Mock();
        $this->injector = $this->getMockBuilder('OAuth_io\Injector')->getMock();
        OAuth_io\Injector::setInstance($this->injector);
        $this->request_mock = $this->getMockBuilder('\HTTP_Request2')->setMethods(null)->getMock();
        $this->request_mock->setConfig(array(
            'adapter' => $this->adapter_mock
        ));
        $this->injector->expects($this->any())->method('getRequest')->will($this->returnValue($this->request_mock));
        
        $this->session = array();
        $this->injector->session = & $this->session;
        $this->oauth = new OAuth();
        
        $this->oauth->initialize('somekey', 'somesecret');
        $this->token = $this->oauth->generateToken();
        $this->adapter_mock->addResponse("HTTP/1.1 200 OK\r\n" . "Content-Type: application/json\r\n" . "\r\n" . json_encode(array(
            'access_token' => 'someaccesstoken',
            'state' => $this->token,
            'provider' => 'someprovider'
        )) , "https://oauth.io/auth/token");
        $result = $this->oauth->auth('somecode');
    }
    
    public function testRequestObjectContainsGetPostPutDeleteAndPatchMethods() {
        if (method_exists($this->oauth, 'create')) {
            $request_object = $this->oauth->create('someprovider');
            $this->assertTrue(!is_null($request_object));
            $this->assertTrue(method_exists($request_object, 'get'));
            $this->assertTrue(method_exists($request_object, 'post'));
            $this->assertTrue(method_exists($request_object, 'put'));
            $this->assertTrue(method_exists($request_object, 'del'));
            $this->assertTrue(method_exists($request_object, 'patch'));
        } else {
            $this->fail('$oauth->create() does not exist');
        }
    }
    
    public function testRequestObjectGetSendsAGetHttpRequest() {
        if (method_exists($this->oauth, 'create')) {
            $request_object = $this->oauth->create('someprovider');
            
            $this->adapter_mock->addResponse("HTTP/1.1 200 OK\r\n" . "Content-Type: application/json\r\n" . "\r\n" . json_encode(array(
                'username' => 'Jean-Bernard'
            )));
            
            $response = $request_object->get('/some_adress');
            $headers = $this->request_mock->getHeaders();
            $val = $headers['oauthio'];
            $headers['oauthio'] = array();
            parse_str($val, $headers['oauthio']);
            $this->assertTrue(isset($headers['oauthio']['tokens']));
            
            $this->assertEquals('someaccesstoken', $headers['oauthio']['tokens']['access_token']);
            $this->assertEquals('somekey', $headers['oauthio']['k']);
            
            $this->assertEquals('GET', $this->request_mock->getMethod());
            $this->assertTrue(is_array($response));
            $this->assertEquals('Jean-Bernard', $response['username']);
        } else {
            $this->fail('$oauth->create() does not exist');
        }
    }
    
    public function testRequestObjectPostSendsAPostHttpRequest() {
        if (method_exists($this->oauth, 'create')) {
            $request_object = $this->oauth->create('someprovider');
            
            $fields = array(
                'message' => 'Hello World'
            );
            
            $this->adapter_mock->addResponse("HTTP/1.1 200 OK\r\n" . "Content-Type: application/json\r\n" . "\r\n" . json_encode(array(
                'result' => 'true'
            )));
            
            $response = $request_object->post('/some_adress', $fields);
            $headers = $this->request_mock->getHeaders();
            $val = $headers['oauthio'];
            $headers['oauthio'] = array();
            parse_str($val, $headers['oauthio']);
            $this->assertTrue(isset($headers['oauthio']['tokens']));
            
            $this->assertEquals('someaccesstoken', $headers['oauthio']['tokens']['access_token']);
            $this->assertEquals('somekey', $headers['oauthio']['k']);
            
            $this->assertEquals(http_build_query($fields) , $this->request_mock->getBody());
            
            $this->assertEquals('POST', $this->request_mock->getMethod());
            $this->assertTrue(is_array($response));
            $this->assertEquals('true', $response['result']);
        } else {
            $this->fail('$oauth->create() does not exist');
        }
    }
    
    public function testRequestObjectPutSendsAPostHttpRequest() {
        if (method_exists($this->oauth, 'create')) {
            $request_object = $this->oauth->create('someprovider');
            
            $fields = array(
                'message' => 'Hello World'
            );
            
            $this->adapter_mock->addResponse("HTTP/1.1 200 OK\r\n" . "Content-Type: application/json\r\n" . "\r\n" . json_encode(array(
                'result' => 'true'
            )));
            
            $response = $request_object->put('/some_adress', $fields);
            $headers = $this->request_mock->getHeaders();
            $val = $headers['oauthio'];
            $headers['oauthio'] = array();
            parse_str($val, $headers['oauthio']);
            $this->assertTrue(isset($headers['oauthio']['tokens']));
            
            $this->assertEquals('someaccesstoken', $headers['oauthio']['tokens']['access_token']);
            $this->assertEquals('somekey', $headers['oauthio']['k']);
            
            $this->assertEquals(http_build_query($fields) , $this->request_mock->getBody());
            
            $this->assertEquals('PUT', $this->request_mock->getMethod());
            $this->assertTrue(is_array($response));
            $this->assertEquals('true', $response['result']);
        } else {
            $this->fail('$oauth->create() does not exist');
        }
    }
    
    public function testRequestObjectPatchSendsAPostHttpRequest() {
        if (method_exists($this->oauth, 'create')) {
            $request_object = $this->oauth->create('someprovider');
            
            $fields = array(
                'message' => 'Hello World'
            );
            
            $this->adapter_mock->addResponse("HTTP/1.1 200 OK\r\n" . "Content-Type: application/json\r\n" . "\r\n" . json_encode(array(
                'result' => 'true'
            )));
            
            $response = $request_object->patch('/some_adress', $fields);
            $headers = $this->request_mock->getHeaders();
            $val = $headers['oauthio'];
            $headers['oauthio'] = array();
            parse_str($val, $headers['oauthio']);
            $this->assertTrue(isset($headers['oauthio']['tokens']));
            
            $this->assertEquals('someaccesstoken', $headers['oauthio']['tokens']['access_token']);
            $this->assertEquals('somekey', $headers['oauthio']['k']);
            
            $this->assertEquals(http_build_query($fields) , $this->request_mock->getBody());
            
            $this->assertEquals('PATCH', $this->request_mock->getMethod());
            $this->assertTrue(is_array($response));
            $this->assertEquals('true', $response['result']);
        } else {
            $this->fail('$oauth->create() does not exist');
        }
    }
    
    public function testRequestObjectDeleteSendsAPostHttpRequest() {
        if (method_exists($this->oauth, 'create')) {
            $request_object = $this->oauth->create('someprovider');
            
            $fields = array(
                'message' => 'Hello World'
            );
            
            $this->adapter_mock->addResponse("HTTP/1.1 200 OK\r\n" . "Content-Type: application/json\r\n" . "\r\n" . json_encode(array(
                'result' => 'true'
            )));
            
            $response = $request_object->del('/some_adress');
            $headers = $this->request_mock->getHeaders();
            $val = $headers['oauthio'];
            $headers['oauthio'] = array();
            parse_str($val, $headers['oauthio']);
            $this->assertTrue(isset($headers['oauthio']['tokens']));
            
            $this->assertEquals('someaccesstoken', $headers['oauthio']['tokens']['access_token']);
            $this->assertEquals('somekey', $headers['oauthio']['k']);
            
            $this->assertEquals('DELETE', $this->request_mock->getMethod());
            $this->assertTrue(is_array($response));
            $this->assertEquals('true', $response['result']);
        } else {
            $this->fail('$oauth->create() does not exist');
        }
    }
}
