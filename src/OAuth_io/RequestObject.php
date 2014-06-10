<?php
namespace OAuth_io;

class RequestObject {
    
    private $injector;
    private $credentials;
    
    public function __construct($credentials = array()) {
        $this->injector = Injector::getInstance();
        $this->credentials = $credentials;
    }

    public function getCredentials() {
        return $this->credentials;
    }

    public function wasRefreshed() {
        return $this->credentials['refreshed'] == true;
    }
    
    private function object_to_array($obj) {
        return json_decode(json_encode($obj), true);
    }
    
    private function makeRequest($method, $url, $body_fields = null) {
        $response = null;
        if (!isset($this->credentials)) {
            throw new NotAuthenticatedException('The user is not authenticated for that provider');
        } else {
            $prov_data = $this->credentials;
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
                'url' => $this->injector->config['oauthd_url'] . '/request/' . $this->credentials['provider'] . '/' . urlencode($url) ,
                'headers' => array(
                    'oauthio' => http_build_query($headers)
                ) ,
                'body' => is_array($body_fields) ? $body_fields : null
            ));
        }
        return $response;
    }
    
    private function makeMeRequest($filters) {
        if (!isset($this->credentials)) {
            throw new \Exception('Error');
        } else {
            $prov_data = $this->credentials;
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
            
            if (is_array($filters)) {
                $filters = array(
                    'filter' => join(',', $filters)
                );
            }
            
            $response = $requester->make_request(array(
                'method' => 'GET',
                'url' => $this->injector->config['oauthd_url'] . '/auth/' . $this->credentials['provider'] . '/me',
                'headers' => array(
                    'oauthio' => http_build_query($headers)
                ) ,
                'qs' => is_array($filters) ? $filters : null
            ));
        }
        return $response;
    }
    
    public function get($url) {
        $response = $this->makeRequest('GET', $url)->body;
        $response = $this->object_to_array($response);
        return $response;
    }
    
    public function post($url, $fields) {
        $response = $this->makeRequest('POST', $url, $fields)->body;
        return $this->object_to_array($response);
    }
    
    public function put($url, $fields) {
        $response = $this->makeRequest('PUT', $url, $fields)->body;
        return $this->object_to_array($response);
    }
    
    public function del($url) {
        $response = $this->makeRequest('DELETE', $url)->body;
        return $this->object_to_array($response);
    }
    
    public function patch($url, $fields) {
        $response = $this->makeRequest('PATCH', $url, $fields)->body;
        return $this->object_to_array($response);
    }
    
    public function me($filters = null) {
        $response = $this->makeMeRequest($filters)->body->data;
        return $this->object_to_array($response);
    }
}
