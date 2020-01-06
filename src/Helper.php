<?php

namespace cpaassdk;

class Helper {

  public function id_from_url($url) {
    $url_array = explode('/', $url);
    return end($url_array);
  }

  public function find_msgid_containing_obj($response, $parent_key=null) {
    $key = 'messageId';
    if (array_key_exists($key, $response)) {
      $response['name'] = $parent_key;
      return $response;
    }
    foreach($response as $k => $v) {
      if(is_array($v)) {
        $response = $this->find_msgid_containing_obj($v, $k);
        if ($response) {
          return $response;
        }
      }
    }
  }

  public function parse_response($response) {
    return array_values($response)[0];
  }

  public function check_if_error($response) {
    if ($response->getStatusCode() >= 400) {
        return true;
    } else {
      return false;
    }
  }

  public function check_if_test($response) {
    $resp = $response->getBody();
    $resp = json_decode($resp, TRUE);
    if (is_array($resp)) {
      if (array_key_exists('test_request', $resp)) {
        return true;
      } else {
        return false;
      }
    } else {
      return false;
    }
  }

  public function build_error_response($response) {
    if ($response->getStatusCode() >= 400) {
      $resp = $response->getBody();
      $resp = json_decode($resp, TRUE);
      $error_obj = $this->find_msgid_containing_obj($resp);
      if ($error_obj) {
        $error_message = $error_obj['text'];
        foreach($error_obj['variables'] as $key=>$value) {
          $error_message = preg_replace('/%[0-9]/', $value, $error_message, 1);
        }
        $error_array = array();
        $error_array['name'] = $error_obj['name'];
        $error_array['exception_id'] = $error_obj['messageId'];
        $error_array['message'] = $error_message;
        return $error_array;
      } else {
        $error_array = array();
        $error_array['name'] = 'RequestError';
        $error_array['exception_id'] = '';
        $error_array['message'] = $resp;
        return $error_array;
      }
    }
  }
}