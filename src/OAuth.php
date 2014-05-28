<?php
namespace OAuth_io;

class OAuth
{
    private $injector;
    private $initialized = false;

    /**
     *
     *
     */
    public function __construct(&$session = null, $ssl_verification = true)
    {
        $this->injector = Injector::getInstance();
        if (is_array($session)) {
            $this->injector->session = & $session;
        }
        $this->injector->ssl_verification = $ssl_verification;
    }

    /**
     *
     *
     */
    public function getVersion()
    {
        $composer = json_decode(file_get_contents(__DIR__ . '/../../composer.json') , true);

        return $composer["version"];
    }

    /**
     *
     *
     */
    public function getOAuthdUrl()
    {
        return $this->injector->config['oauthd_url'];
    }

    /**
     *
     *
     */
    public function setOAuthdUrl($url)
    {
        $this->injector->config['oauthd_url'] = $url;
    }

    /**
     *
     *
     */
    public function initialize($key, $secret)
    {
        $this->injector->config['app_key'] = $key;
        $this->injector->config['app_secret'] = $secret;
        $this->initSession();
        $this->initialized = true;
    }

    public function getAppKey()
    {
        return $this->injector->config['app_key'];
    }

    public function getAppSecret()
    {
        return $this->injector->config['app_secret'];
    }

    private function initSession()
    {
        if (!isset($this->injector->session['oauthio'])) {
            $this->injector->session['oauthio'] = array();
        }
        if (!isset($this->injector->session['oauthio']['tokens'])) {
            $this->injector->session['oauthio']['tokens'] = array();
        }
    }

    public function generateStateToken()
    {
        $unique_token = sha1(uniqid('', true));
        array_unshift($this->injector->session['oauthio']['tokens'], $unique_token);
        if (count($this->injector->session['oauthio']['tokens']) > 4) {
            array_splice($this->injector->session['oauthio']['tokens'], 4);
        }

        return $unique_token;
    }

    public function auth($code)
    {
        if (!$this->initialized) {
            throw new Exception\NotInitializedException('You must initialize the OAuth instance.');
        }
        $request = $this->injector->getRequest();
        $response = $request->make_request(array(
            'method' => 'POST',
            'url' => $this->injector->config['oauthd_url'] . '/auth/access_token',
            'body' => http_build_query(array(
                'code' => $code,
                'key' => $this->injector->config['app_key'],
                'secret' => $this->injector->config['app_secret']
            )) ,
            'headers' => array(
                'Content-Type' => 'application/x-www-form-urlencoded'
            )
        ));
        $result = $response->body;

        if (isset($result->provider)) {
            $this->injector->session['oauthio']['auth'][$result->provider] = json_decode(json_encode($result) , true);
        }

        return json_decode(json_encode($result) , true);
    }

    public function create($provider)
    {
        if (!$this->initialized) {
            throw new Exception\NotInitializedException('You must initialize the OAuth instance.');
        }
        if (isset($this->injector->session['oauthio']['auth'][$provider])) {
            $request = new Request();
            $request->initialize($provider);

            return $request;
        } else {
            throw new Exception\NotAuthenticatedException('The user is not authenticated for that provider');
        }
    }
}
