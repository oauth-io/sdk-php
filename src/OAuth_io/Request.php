<?php
namespace OAuth_io;

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
            throw new NotAuthenticatedException('The user is not authenticated for that provider');
        } else {
            $prov_data = $this->injector->session['oauthio']['auth'][$this->provider];
            $requester = $this->injector->getRequest();

            $tokens = array();
            
            $headers = array(
                'k' => $this->injector->config['app_key']
            );

            if (isset($prov_data['access_token'])) {
                $headers['access_token'] = $prov_data['access_token'];
            }
            if (isset($prov_data['oauth_token']) && isset($prov_data['oauth_token_secret'])) {
                $headers['oauth_token'] = $prov_data['oauth_token'];
                $headers['oauth_token_secret'] = $prov_data['oauth_token_secret'];
                $headers['oauthv1'] = '1';
            }

            $response = $requester->make_request(array(
                'method' => $method,
                'url' => $this->injector->config['oauthd_url'] . '/request/' . $this->provider . '/' . urlencode($url),
                'headers' => array('oauthio' => http_build_query($headers)),
                'body' => is_array($body_fields) ? $body_fields : null
            ));
        }
        return $response;
    }

    private function makeMeRequest($filters) {
        if (!isset($this->injector->session['oauthio']['auth'][$this->provider])) {
            throw new \Exception('Error');
        } else {
            $prov_data = $this->injector->session['oauthio']['auth'][$this->provider];
            $requester = $this->injector->getRequest();

            $tokens = array();
            
            $headers = array(
                'k' => $this->injector->config['app_key']
            );

            if (isset($prov_data['access_token'])) {
                $headers['access_token'] = $prov_data['access_token'];
            }
            if (isset($prov_data['oauth_token']) && isset($prov_data['oauth_token_secret'])) {
                $headers['oauth_token'] = $prov_data['oauth_token'];
                $headers['oauth_token_secret'] = $prov_data['oauth_token_secret'];
                $headers['oauthv1'] = '1';
            }

            $response = $requester->make_request(array(
                'method' => 'GET',
                'url' => $this->injector->config['oauthd_url'] . '/auth/' . $this->provider . '/me',
                'headers' => array('oauthio' => http_build_query($headers)),
                'qs' => is_array($filters) ? $filters : null
            ));
        }
        return $response;
    }
    
    public function get($url) {
        return (array) $this->makeRequest('GET', $url)->body;
    }
    
    public function post($url, $fields) {
        return (array) $this->makeRequest('POST', $url, $fields)->body;
    }
    
    public function put($url, $fields) {
        return (array) $this->makeRequest('PUT', $url, $fields)->body;
    }
    
    public function del($url) {
        return (array) $this->makeRequest('DELETE', $url)->body;
    }
    
    public function patch($url, $fields) {
        return (array) $this->makeRequest('PATCH', $url, $fields)->body->data;
    }

    public function me($filters=null) {
        $body = $this->makeMeRequest($filters)->body->data;
        return (array) $body;
    }
}