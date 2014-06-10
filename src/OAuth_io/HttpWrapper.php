<?php
namespace OAuth_io;

class HttpWrapper {
    public function __create() {
    }
    
    private function array_map_recursive($callback, $array) {
        foreach ($array as $key => $value) {
            if (is_object($array[$key])) {
                $array[$key] = array_map_recursive($callback, $array[$key]);
            } else {
                $array[$key] = call_user_func($callback, $array[$key]);
            }
        }
        return $array;
    }
    
    public function make_request($options) {
        $injector = Injector::getInstance();
        
        $url = $options['url'];
        $method = $options['method'];
        $headers = $options['headers'];
        $body = isset($options['body']) ? $options['body'] : '';
        $response = null;
        if (isset($options['qs'])) {
            $qs = http_build_query($options['qs']);
            $url.= '?' . $qs;
        }
        $url = str_replace('%2C', ',', $url);

        \Unirest::verifyPeer($injector->ssl_verification);
        if ($options['method'] == 'GET') {
            $response = \Unirest::get($url, $headers);
        }
        
        if ($options['method'] == 'POST') {
            $response = \Unirest::post($url, $headers, $body);
        }
        
        if ($options['method'] == 'PUT') {
            $response = \Unirest::put($url, $headers, $body);
        }
        
        if ($options['method'] == 'DELETE') {
            $response = \Unirest::delete($url, $headers);
        }
        
        if ($options['method'] == 'PATCH') {
            $response = \Unirest::patch($url, $headers, $body);
        }

        return $response;
    }
}
