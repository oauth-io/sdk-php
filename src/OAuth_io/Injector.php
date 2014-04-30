<?php
namespace OAuth_io;
require_once __DIR__ . '/../../vendor/autoload.php';

class Injector {
    
    private $session;
    
    public function __construct() {
        if (isset($_SESSION)) $this->session = $_SESSION;
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
