<?php
require_once __DIR__ . '/../vendor/autoload.php';

use OAuth_io\OAuth;
use HTTP\Request2;
use HTTP\Request2\Adapter\Mock;

class RequestsTest extends PHPUnit_Framework_TestCase
{
    protected $oauth;
    protected $result;
    protected $session;
    protected $http;
    
    protected function setUp() {
        if (method_exists('OAuth', 'initialize') && method_exists('OAuth', 'generateToken') && method_exists('OAuth', 'authenticate')) {
            $this->oauth = new OAuth();
            $this->oauth->initialize('somekey', 'somesecret');
            $token = $this->oauth->generateToken(array());
            
            $this->http = $this->getMockBuilder('OAuth_io\CurlRequest')->getMock();
            
            $response = array('access_token' => 'someaccesstoken', 'state' => $token, 'provider' => 'someprovider');
            $this->http->expects($this->once())->method('execute')->will($this->returnValue(json_encode($response)));
            
            $this->oauth = new OAuth();
            $this->oauth->initialize('somekey', 'somesecret', $this->http);
            $this->result = $this->oauth->authenticate('somecode');
        }
    }
    
    public function testRequestObjectContainsGetPostPutDeleteAndPatchMethods() {
        if (method_exists($this->oauth, 'create')) {
            $request_object = $this->oauth->create('someprovider');
            $this->assertTrue(!is_null($request_object));
            $this->assertTrue(method_exists($request_object, 'get'));
            $this->assertTrue(method_exists($request_object, 'post'));
            $this->assertTrue(method_exists($request_object, 'put'));
            $this->assertTrue(method_exists($request_object, 'delete'));
            $this->assertTrue(method_exists($request_object, 'patch'));
        } else {
            $this->fail('$oauth->create() does not exist');
        }
    }
    
    public function testRequestObjectGetSendsAGetHttpRequest() {
        if (method_exists($this->oauth, 'create')) {
            $request_object = $this->oauth->create('someprovider');
            $response = array('username' => 'Jean-Bernard');
            
            $http->expects($this->once())->method('setOption')->with(CURLOPT_URL, 'https://oauth.io/request/someprovider/' + urlencode('/some_adress'));
            $http->expects($this->once())->method('setOption')->with(CURLOPT_HTTPHEADER, array('oauthio' => http_build_query(array('k' => 'somekey', 'tokens' => array('access_token' => 'someaccesstoken',)))));
            $this->http->expects($this->once())->method('execute')->will($this->returnValue(json_encode($response)));
            
            $response = $request_object->get('/some_adress');
        } else {
            $this->fail('$oauth->create() does not exist');
        }
    }
}
