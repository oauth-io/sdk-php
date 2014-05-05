<?php
namespace OAuth_io;

class OAuth {
    
    private $injector;
    
    /**
     *
     *
     */
    public function __construct() {
        $this->injector = Injector::getInstance();
    }
    
    /**
     *
     *
     */
    public function getVersion() {
        $composer = json_decode(file_get_contents(__DIR__ . '/../../composer.json') , true);
        return $composer["version"];
    }
    
    /**
     *
     *
     */
    public function getOAuthdUrl() {
        return $this->injector->config['oauthd_url'];
    }
    
    /**
     *
     *
     */
    public function setOAuthdUrl($url) {
        $this->injector->config['oauthd_url'] = $url;
    }
    
    /**
     *
     *
     */
    public function initialize($key, $secret) {
        $this->injector->config['app_key'] = $key;
        $this->injector->config['app_secret'] = $secret;
        $this->initSession();
    }
    
    public function getAppKey() {
        return $this->injector->config['app_key'];
    }
    
    public function getAppSecret() {
        return $this->injector->config['app_secret'];
    }
    
    private function initSession() {
        if (!isset($this->injector->session['oauthio'])) {
            $this->injector->session['oauthio'] = array();
        }
        if (!isset($this->injector->session['oauthio']['tokens'])) {
            $this->injector->session['oauthio']['tokens'] = array();
        }
    }
    
    public function generateToken() {
        $unique_token = uniqid('', true);
        array_unshift($this->injector->session['oauthio']['tokens'], $unique_token);
        if (count($this->injector->session['oauthio']['tokens']) > 4) {
            array_splice($this->injector->session['oauthio']['tokens'], 4);
        }
        return $unique_token;
    }
    
    public function auth($code) {
        $request = $this->injector->getRequest();
        $request->setMethod(\HTTP_Request2::METHOD_POST);
        $request->setUrl($this->injector->config['oauthd_url'] . '/auth/token');
        $request->addPostParameter(array(
            'code' => $code,
            'key' => $this->injector->config['app_key'],
            'secret' => $this->injector->config['app_secret']
        ));
        
        $response = $request->send();
        $response = json_decode($response->getBody() , true);
        $this->injector->session['oauthio']['auth'][$response['provider']] = $response;
        return $response;
    }
    
    public function create($provider) {
        if (isset($this->injector->session['oauthio']['auth'][$provider])) {
            $request = new Request();
            $request->initialize($provider);
            return $request;
        } else {
            
            //TODO: throw exception here
            
            
        }
    }
}
