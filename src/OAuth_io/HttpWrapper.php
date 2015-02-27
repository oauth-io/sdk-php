<?php



namespace OAuth_io;
use Unirest\Request as Request;
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

        if (isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json' && is_array($body)) {
            $body = json_encode($body);
        }


        Request::verifyPeer($injector->ssl_verification);
        $response = Request::send($options['method'], $url, $body, $headers);

        return $response;
    }
}
