<?php

namespace OAuth_io;

require_once __DIR__ . '/../../vendor/autoload.php';

class Request {

	private $injector;

	public function __construct($params = array()) {
		$this->injector = new Injector();


		if (isset($params['injector'])) {
			$this->injector = $params['injector'];
		}
	}

	public function get() {

	}

	public function post() {

	}

	public function put() {

	}

	public function del() {

	}

	public function patch() {

	}
}