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
        $this->injector = $this->getMockBuilder('OAuth_io\Injector')->getMock();
        OAuth_io\Injector::setInstance($this->injector);
        $this->request_mock = $this->getMockBuilder('OAuth_io\HttpWrapper')->setMethods(array())->getMock();
        $this->injector->expects($this->any())->method('getRequest')->will($this->returnValue($this->request_mock));
        
        $this->injector->session = array();
        $this->oauth = new OAuth();
        
        $this->oauth->initialize('somekey', 'somesecret');
        $this->token = $this->oauth->generateStateToken();
        
        $response = (object)array(
            'body' => (object)array(
                'access_token' => 'someaccesstoken',
                'state' => $this->token,
                'provider' => 'someprovider'
            )
        );
        
        $this->request_mock->expects($this->at(0))->method('make_request')->will($this->returnValue($response));
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
            $this->assertTrue(method_exists($request_object, 'me'));
        } else {
            $this->fail('$oauth->create() does not exist');
        }
    }
    
    public function testRequestObjectGetSendsAGetHttpRequest() {
        if (method_exists($this->oauth, 'create')) {
            
            $request_object = $this->oauth->create('someprovider');
            
            $this->request_mock->expects($this->at(0))->method('make_request')->will($this->returnCallback(function ($params) {
                
                $this->assertEquals('https://oauth.io/request/someprovider/%2Fsome_address', $params['url']);

                $this->assertEquals('GET', $params['method']);
                
                $this->assertTrue(isset($params['headers']));
                $this->assertTrue(isset($params['headers']['oauthio']));
                
                $oauthio = array();
                parse_str($params['headers']['oauthio'], $oauthio);
                
                $this->assertEquals('somekey', $oauthio['k']);
                $this->assertEquals('someaccesstoken', $oauthio['access_token']);
                
                return (object)array(
                    'body' => (object)array(
                        'username' => 'Jean-Bernard'
                    )
                );
            }));
            $response = $request_object->get('/some_address');
            
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
            
            $this->request_mock->expects($this->at(0))->method('make_request')->will($this->returnCallback(function ($params) {
                
                $this->assertEquals('https://oauth.io/request/someprovider/%2Fsome_address', $params['url']);

                $this->assertEquals('POST', $params['method']);

                $body = $params['body'];
                $this->assertEquals('Hello World', $body['message']);
                
                $this->assertTrue(isset($params['headers']));
                $this->assertTrue(isset($params['headers']['oauthio']));
                
                $oauthio = array();
                parse_str($params['headers']['oauthio'], $oauthio);
                
                $this->assertEquals('somekey', $oauthio['k']);
                $this->assertEquals('someaccesstoken', $oauthio['access_token']);

                return (object)array(
                    'body' => (object)array(
                        'result' => 'true'
                    )
                );
            }));
            
            $response = $request_object->post('/some_address', $fields);
            
            $this->assertTrue(is_array($response));
            $this->assertEquals('true', $response['result']);
        } else {
            $this->fail('$oauth->create() does not exist');
        }
    }
    
    public function testRequestObjectPutSendsAPutHttpRequest() {
        if (method_exists($this->oauth, 'create')) {
            $request_object = $this->oauth->create('someprovider');
            
            $fields = array(
                'message' => 'Hello World'
            );
            
            $this->request_mock->expects($this->at(0))->method('make_request')->will($this->returnCallback(function ($params) {
                
                $this->assertEquals('https://oauth.io/request/someprovider/%2Fsome_address', $params['url']);

                $this->assertEquals('PUT', $params['method']);

                $body = $params['body'];
                $this->assertEquals('Hello World', $body['message']);
                
                $this->assertTrue(isset($params['headers']));
                $this->assertTrue(isset($params['headers']['oauthio']));
                
                $oauthio = array();
                parse_str($params['headers']['oauthio'], $oauthio);
                
                $this->assertEquals('somekey', $oauthio['k']);
                $this->assertEquals('someaccesstoken', $oauthio['access_token']);

                return (object)array(
                    'body' => (object)array(
                        'result' => 'true'
                    )
                );
            }));
            
            $response = $request_object->put('/some_address', $fields);
            
            $this->assertTrue(is_array($response));
            $this->assertEquals('true', $response['result']);
        } else {
            $this->fail('$oauth->create() does not exist');
        }
    }
    
    public function testRequestObjectPatchSendsAPatchHttpRequest() {
        if (method_exists($this->oauth, 'create')) {
            
            $request_object = $this->oauth->create('someprovider');
            
            $fields = array(
                'message' => 'Hello World'
            );
            
            $this->request_mock->expects($this->at(0))->method('make_request')->will($this->returnCallback(function ($params) {
                
                $this->assertEquals('https://oauth.io/request/someprovider/%2Fsome_address', $params['url']);

                $this->assertEquals('PATCH', $params['method']);

                $body = $params['body'];
                $this->assertEquals('Hello World', $body['message']);
                
                $this->assertTrue(isset($params['headers']));
                $this->assertTrue(isset($params['headers']['oauthio']));
                
                $oauthio = array();
                parse_str($params['headers']['oauthio'], $oauthio);
                
                $this->assertEquals('somekey', $oauthio['k']);
                $this->assertEquals('someaccesstoken', $oauthio['access_token']);

                return (object)array(
                    'body' => (object)array(
                        'result' => 'true'
                    )
                );
            }));
            $response = $request_object->patch('/some_address', $fields);
            
            $this->assertTrue(is_array($response));
            $this->assertEquals('true', $response['result']);
        } else {
            $this->fail('$oauth->create() does not exist');
        }
    }
    
    public function testRequestObjectDelSendsADeleteHttpRequest() {
        if (method_exists($this->oauth, 'create')) {
            $request_object = $this->oauth->create('someprovider');
            
            $this->request_mock->expects($this->at(0))->method('make_request')->will($this->returnCallback(function ($params) {
                
                $this->assertEquals('https://oauth.io/request/someprovider/%2Fsome_address', $params['url']);

                $this->assertEquals('DELETE', $params['method']);

                $this->assertTrue(isset($params['headers']));
                $this->assertTrue(isset($params['headers']['oauthio']));
                
                $oauthio = array();
                parse_str($params['headers']['oauthio'], $oauthio);
                
                $this->assertEquals('somekey', $oauthio['k']);
                $this->assertEquals('someaccesstoken', $oauthio['access_token']);

                return (object)array(
                    'body' => (object)array(
                        'result' => 'true'
                    )
                );
            }));
            $response = $request_object->del('/some_address');
            
            $this->assertTrue(is_array($response));
            $this->assertEquals('true', $response['result']);
        } else {
            $this->fail('$oauth->create() does not exist');
        }
    }

    public function testRequestObjectMeSendsAGetHttpRequestToTheMeEndpoint() {
        if (method_exists($this->oauth, 'create')) {
            $request_object = $this->oauth->create('someprovider');
            
            $this->request_mock->expects($this->at(0))->method('make_request')->will($this->returnCallback(function ($params) {
                
                $this->assertEquals('https://oauth.io/auth/someprovider/me', $params['url']);

                $this->assertEquals('GET', $params['method']);

                $this->assertEquals('name', $params['qs'][0]);

                $this->assertTrue(isset($params['headers']));
                $this->assertTrue(isset($params['headers']['oauthio']));
                
                $oauthio = array();
                parse_str($params['headers']['oauthio'], $oauthio);
                
                $this->assertEquals('somekey', $oauthio['k']);
                $this->assertEquals('someaccesstoken', $oauthio['access_token']);

                return (object)array(
                    'body' => (object)array(
                        'name' => 'Jean-René Dupont'
                    )
                );
            }));
            $response = $request_object->me(array('name'));
            
            $this->assertTrue(is_array($response));
            $this->assertEquals('Jean-René Dupont', $response['name']);
        } else {
            $this->fail('$oauth->create() does not exist');
        }
    }

    public function testCreateThrowsAnExceptionWhenTheUserIsNotAuthenticatedOnTheAskedProvider() {
        if (method_exists($this->oauth, 'create')) {
            $passed = false;
            try {
                $request_object = $this->oauth->create('someprovider2');    
            } catch (OAuth_io\NotAuthenticatedException $e) {
                $passed = true;
            }

            $this->assertTrue($passed);
        } else {
            $this->fail('$oauth->create() does not exist');
        }
    }
}
