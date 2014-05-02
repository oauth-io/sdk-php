<?php
namespace OAuth_io;

require_once __DIR__ . '/../../vendor/autoload.php';

class Request {
    
    private $injector;
    private $provider;
    
    public function __construct() {
        $this->injector = Injector::getInstance();
    }
    
    public function initialize($provider) {
        $this->provider = $provider;
    }
    
    private function makeRequest($method, $url, $body_fields = null) {
        $response = null;
        if (!isset($this->injector->session['oauthio']['auth'][$this->provider])) {
        	throw new \Exception('Error');
        } else {
            $prov_data = $this->injector->session['oauthio']['auth'][$this->provider];
            $requester = $this->injector->getRequest();
            $requester->setUrl($this->injector->config['oauthd_url'] . '/request/' . $this->provider . '/' . urlencode($url));
            $requester->setMethod($method);

            $tokens = array();
            
            
            $headers = array(
                'k' => $this->injector->config['app_key']
            );

            if (isset($prov_data['access_token'])) {
            	$tokens['access_token'] = $prov_data['access_token'];
            }
            if (isset($prov_data['oauth_token']) && isset($prov_data['oauth_token_secret'])) {
            	$tokens['oauth_token'] = $prov_data['oauth_token'];
            	$tokens['oauth_token_secret'] = $prov_data['oauth_token_secret'];
            	$headers['oauthv1'] = '1';
            }
            $headers['tokens'] = $tokens;
            
            $requester->setHeader('oauthio', http_build_query($headers));

            if (is_array($body_fields)) {
            	$requester->setBody(http_build_query($body_fields));
            }

            $response = $requester->send();
        }
        return $response;
    }
    
    public function get($url) {
    	return json_decode($this->makeRequest('GET', $url)->getBody(), true);
    }
    
    public function post($url, $fields) {
    	return json_decode($this->makeRequest('POST', $url, $fields)->getBody(), true);
    }
    
    public function put($url, $fields) {
    	return json_decode($this->makeRequest('PUT', $url, $fields)->getBody(), true);
    }
    
    public function del($url) {
    	return json_decode($this->makeRequest('DELETE', $url)->getBody(), true);
    }
    
    public function patch($url, $fields) {
    	return json_decode($this->makeRequest('PATCH', $url, $fields)->getBody(), true);
    }
}
