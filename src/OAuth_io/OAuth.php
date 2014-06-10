<?php
namespace OAuth_io;

class OAuth {
    
    private $injector;
    private $initialized = false;
    
    /**
     *
     *
     */
    public function __construct() {
        $this->injector = Injector::getInstance();
    }
    
    /**
     *
     */
    public function setSslVerification($ssl_verification) {
        $this->injector->ssl_verification = $ssl_verification;
    }
    
    /**
     *
     */
    public function setSession(&$session) {
        if (is_array($session)) {
            $this->injector->session = & $session;
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
        $this->initialized = true;
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
    
    public function generateStateToken() {
        $unique_token = sha1(uniqid('', true));
        array_unshift($this->injector->session['oauthio']['tokens'], $unique_token);
        if (count($this->injector->session['oauthio']['tokens']) > 4) {
            array_splice($this->injector->session['oauthio']['tokens'], 4);
        }
        return $unique_token;
    }
    
    public function refreshCredentials($credentials, $force = false) {
        $date = new \DateTime();
        $credentials['refreshed'] = false;
        if (isset($credentials['refresh_token']) && ((isset($credentials['expires']) && $date->getTimestamp() > $credentials['expires']) || $force)) {
            $request = $this->injector->getRequest();
            $response = $request->make_request(array(
                'method' => 'POST',
                'url' => $this->injector->config['oauthd_url'] . '/auth/refresh_token/' . $credentials['provider'],
                'body' => http_build_query(array(
                    'token' => $credentials['refresh_token'],
                    'key' => $this->injector->config['app_key'],
                    'secret' => $this->injector->config['app_secret']
                )) ,
                'headers' => array(
                    'Content-Type' => 'application/x-www-form-urlencoded'
                )
            ));
            $refreshed = json_decode(json_encode($response->body) , true);
            
            foreach ($refreshed as $k => $v) {
                $credentials[$k] = $v;
            }
            $credentials['refreshed'] = true;

        }
        return $credentials;
    }

    public function auth($provider, $options = array()) {
        
        // $options can contain code, credentials, or nothing. If nothing --> session call
        if (!$this->initialized) {
            throw new NotInitializedException('You must initialize the OAuth instance.');
        }
        if (isset($options['code'])) {
            $request = $this->injector->getRequest();
            $response = $request->make_request(array(
                'method' => 'POST',
                'url' => $this->injector->config['oauthd_url'] . '/auth/access_token',
                'body' => http_build_query(array(
                    'code' => $options['code'],
                    'key' => $this->injector->config['app_key'],
                    'secret' => $this->injector->config['app_secret']
                )) ,
                'headers' => array(
                    'Content-Type' => 'application/x-www-form-urlencoded'
                )
            ));
            $credentials = json_decode(json_encode($response->body) , true);
            if (isset($credentials['expires_in'])) {
                $date = new \DateTime();
                $credentials['expires'] = $date->getTimestamp() + $credentials['expires_in'];
            }
            
            if (isset($credentials['provider'])) {
                $this->injector->session['oauthio']['auth'][$credentials['provider']] = $credentials;
            }
        } else if (isset($options['credentials'])) {
            $credentials = $options['credentials'];
        } else {
            if (isset($this->injector->session['oauthio']['auth'][$provider])) {
                $credentials = $this->injector->session['oauthio']['auth'][$provider];
            } else {
                throw new NotAuthenticatedException('The user is not authenticated for that provider');
            }
        }
        $credentials = $this->refreshCredentials($credentials, isset($options['force_refresh']) ? $options['force_refresh'] : false);
        $request_object = new RequestObject($credentials);
        
        return $request_object;
    }
}
