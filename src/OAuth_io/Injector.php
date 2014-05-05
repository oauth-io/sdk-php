<?php
namespace OAuth_io;

class Injector {
    
    public $session;
    public $config = array(
        'oauthd_url' => 'https://oauth.io',
        'app_key' => '',
        'app_secret' => ''
    );

    private static $instance = null;

    public static function getInstance() {
        if (!is_object(self::$instance)) {
            self::$instance = new Injector();
        }
        return self::$instance;
    }

    public static function setInstance($instance) {
        self::$instance = $instance;
    }


    public function __construct() {
        if (isset($_SESSION)) $this->session = &$_SESSION;
    }
    
    public function getRequest() {
        return new \HTTP_Request2();
    }
    
    public function getSession() {
        return $this->session;
    }

    public function setSession(&$session) {
    	$this->session = &$session;
    }

}
