<?php

namespace CpaasSdk;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use \Datetime;

class Api extends Helper {

  public $_root = null;
  public $_version = '1.1.1';
  // api config section.
  public $config = null;
  public $user_id = null;
  public $access_token = null;
  public $id_token = null;
  public $access_token_parsed = null;
  public $id_token_parsed = true;
  public $client = null;

  public function __construct($config, $mock_http_client=null) {
    $this->config = $config;
    $this->_root = $config->base_url;

    if (!$mock_http_client) {
      $this->http_client = new Client();
    } else {
      $this->http_client = $mock_http_client;
    }

    $this->auth_token();
  }

  public function _request($verb, $url, $params=array(), $with_token=true) {
    $headers = (array_key_exists('headers', $params)) ? $params['headers'] : null;
    $request_headers = $this->compose_headers($headers, $with_token);

    // guzzle section.
    $request_options = array();
    $request_options['http_errors'] = false;
    $request_options['headers'] = $request_headers;

    if(array_key_exists('query', $params)) {
      $request_options['query'] = $params['query'];
    }

    if (array_key_exists('body', $params)) {
      switch($request_headers['Content-Type']) {
        case 'application/x-www-form-urlencoded':
          $request_options['form_params'] = $params['body'];
        break;
        case 'application/json':
          $request_options['body'] = json_encode($params['body']);
        break;
      }
    }

    switch($verb) {
      case 'GET':
        $response = $this->http_client->get($url, $request_options);
      break;
      case 'POST':
        $response = $this->http_client->post($url, $request_options);
      break;
      case 'PUT':
        $response = $this->http_client->put($url, $request_options);
      break;
      case 'DELETE':
        $response = $this->http_client->delete($url, $request_options);
    }

    return $response;
  }

  public function auth_token() {
    if($this->token_expired()){
      $tokens = $this->tokens();
      $this->set_token($tokens);
    }

    return $this->access_token;
  }

  public function tokens() {
    $options = array('body'=> array(), 'headers'=> array());

    $options['body']['client_id'] = $this->config->client_id;
    $options['body']['scope'] = 'openid';

    if (!$this->config->client_secret) {
      $options['body']['grant_type'] = 'password';
      $options['body']['username'] = $this->config->email;
      $options['body']['password'] = $this->config->password;
    } else {
      $options['body']['grant_type'] = 'client_credentials';
      $options['body']['client_secret'] = $this->config->client_secret;
    }

    $options['headers']['Content-Type'] = 'application/x-www-form-urlencoded';

    $uri = "/cpaas/auth/v1/token";
    $url = $this->_root.$uri;
    $response = $this->_request("POST", $url, $options, false);
    $tokens = $response->getBody();

    return $tokens;
  }

  public function set_token($tokens=null) {
    if ($tokens) {
      $tokens = json_decode($tokens, TRUE);
      $this->access_token = $tokens['access_token'];
      $this->id_token = $tokens['id_token'];
      list($header, $access_payload, $signature) = explode (".", $this->access_token);
      $parsed_access_token = json_decode(base64_decode($access_payload), TRUE);
      list($header, $id_payload, $signature) = explode (".", $this->id_token);
      $parsed_id_token = json_decode(base64_decode($id_payload), TRUE);
      $user_id = $parsed_id_token['preferred_username'];
      $this->access_token_parsed = $parsed_access_token;
      $this->id_token_parsed = $parsed_id_token;
      $this->user_id=$user_id;
    } else {
      $this->access_token = null;
      $this->id_token = null;
      $this->user_id = null;
      $this->access_token_parsed = null;
      $this->id_token_parsed = null;
    }
  }

  public function compose_headers($headers=null, $with_token=true) {
    $base_header = [
      'Content-Type'=>'application/json',
      'X-Cpaas-Agent'=>'php-sdk/'.$this->_version
    ];

    if ($headers) {
      foreach($headers as $k => $v) {
        $base_header[$k] = $v;
      }
    }

    if ($with_token) {
      $base_header['Authorization'] =  'Bearer '.$this->auth_token();
    }

    return $base_header;
  }

  public function token_expired() {
    $date = new Datetime();
    if (!$this->access_token) {
      return true;
    }

    $min_buffer = $this->access_token_parsed['exp'] - $this->access_token_parsed['iat'];
    $expires_in = $this->access_token_parsed['exp'] - intval($date->getTimestamp()) - $min_buffer;

    return $expires_in < 0;
  }
}
?>