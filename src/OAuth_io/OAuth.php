<?php
namespace OAuth_io;

require_once __DIR__ . '/../../vendor/autoload.php';

class OAuth {
    private $config = array(
        'oauthd_url' => 'https://oauth.io',
        'app_key' => '',
        'app_secret' => ''
    );
    
    private $injector;
    private $session;
    
    /**
     *
     *
     */
    public function __construct($params = null) {
        $this->injector = new Injector();
        if (isset($_SESSION)) $this->session = $_SESSION;
        if (is_array($params)) {
            if (isset($params['injector'])) {
                $this->injector = $params['injector'];
            }
            if (isset($params['session'])) {
            	$this->session = &$params['session'];
            }
        }
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
        return $this->config['oauthd_url'];
    }
    
    /**
     *
     *
     */
    public function setOAuthdUrl($url) {
        $this->config['oauthd_url'] = $url;
    }
    
    /**
     *
     *
     */
    public function initialize($key, $secret) {
        $this->config['app_key'] = $key;
        $this->config['app_secret'] = $secret;
        $this->initSession($this->session);
    }
    
    public function getAppKey() {
        return $this->config['app_key'];
    }
    
    public function getAppSecret() {
        return $this->config['app_secret'];
    }
    
    private function initSession(&$session) {
        if (!isset($session['oauthio'])) {
            $session['oauthio'] = array();
        }
        if (!isset($session['oauthio']['tokens'])) {
            $session['oauthio']['tokens'] = array();
        }
    }
    
    public function generateToken() {
        $unique_token = uniqid('', true);
        array_unshift($this->session['oauthio']['tokens'], $unique_token);
        if (count($this->session['oauthio']['tokens']) > 4) {
            array_splice($this->session['oauthio']['tokens'], 4);
        }
        return $unique_token;
    }
    
    public function auth($code) {
        $request = $this->injector->getRequest();
        $request->setMethod(\HTTP_Request2::METHOD_POST);
        $request->setUrl($this->config['oauthd_url'] . '/auth/token');
        $request->addPostParameter(array(
            'code' => $code,
            'key' => $this->config['app_key'],
            'secret' => $this->config['app_secret']
        ));
        
        $response = $request->send();
        $response = json_decode($response->getBody() , true);
        $this->session['oauthio']['auth'][$response['provider']] = $response;
        return $response;
    }
    
    public function create($provider) {
        if (isset($this->session['oauthio']['auth'][$provider])) {
        } else {
            //TODO: throw exception here
        }
    }
}
