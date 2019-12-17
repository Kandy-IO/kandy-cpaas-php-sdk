<?php

namespace cpaassdk;


use cpaassdk\ClientConfig;

/**
* CPaaS provides Authentication API where a two-factor authentication (2FA) flow can be implemented by using that. Sections below describe two sample use cases, two-factor authentication via SMS and two-factor authentication via e-mail.
*/

class Twofactor {

  /**
   * @ignore
   */
  public $client = null;

  /**
   * @ignore
   */

  public function __construct(ClientConfig $client) {
    $this->client = $client;
  }

  /**
   * Create a new authentication code  
   *
   * @param array $params Single parameter to hold all options.
   * <pre>
   * <ul>
   * <li><b><samp>$params['destination_address']</samp></b>        <samp>string</samp> <p>Destination address of the authentication code being sent. For sms type authentication codes, it should contain a E164 phone number.</p></li>
   * <li><b><samp>$params['message']</samp></b>                    <samp>string</samp> <p>Message text sent to the destination, containing the placeholder for the code within the text. CPaaS requires to have *{code}* string within the text in order to generate a code and inject into the text. For email type code, one usage is to have the *{code}* string located within the link in order to get a unique link.</p></li>
   * <li><b><samp>$params['method']</samp></b>                     <samp>string</samp> <p>Type of the authentication code delivery method, sms and email are supported types. Possible values: sms, email</p></li>
   * <li><b><samp>$params['expiry']</samp></b>                     <samp>int</samp>    <p>Lifetime duration of the code sent in seconds. This can contain values between 30 and 3600 seconds.</p></li>
   * <li><b><samp>$params['subject']</samp></b>                    <samp>string</samp> <p>When the method is passed as email then subject becomes a mandatory field to pass. The value passed becomes the subject line of the 2FA code email that is sent out to the destinationAddress.</p></li>   
   * <li><b><samp>$params['length']</samp></b>                     <samp>int</samp>    <p>Length of the authentication code tha CPaaS should generate for this request. It can contain values between 4 and 10.</p></li>
   * <li><b><samp>$params['type']</samp></b>                       <samp>string</samp> <p>Type of the code that is generated. If not provided, default value is numeric. Possible values: numeric, alphanumeric, alphabetic</p></li>
   * </ul>
   * </pre>
   */

  public function send_code(array $params=null) {
    
    $destination_address = $params['destination_address'];
    $expiry = array_key_exists('expiry', $params) ? $params['expiry'] : 120;
    $length = array_key_exists('length', $params) ? $params['length'] : 6;
    if (!is_array($destination_address)) {
      $destination_address = [$destination_address];
    }
    $message = $params['message'];
    $subject = $params['subject'];
    $method = array_key_exists('method', $params) ? $params['method'] : 'sms';
    $type = array_key_exists('type', $params) ? $params['type'] : 'numeric';
    
    $options = array('body'=> array(), 'headers'=> array());
    $options['body']['code'] = array();
    $options['body']['code']['address'] = $destination_address;
    $options['body']['code']['method'] = $method;
    $options['body']['code']['subject'] = $subject;
    $options['body']['code']['format'] = array('length'=> $length, 'type'=> $type);
    $options['body']['code']['expiry'] = $expiry;
    $options['body']['code']['message'] = $message;
  
    $uri = $this->client->routes('tf.sendcode');
    $url = $this->client->_root.$uri;
    $response = $this->client->_request('POST', $url, $options['body']);
    
    // TODO: refactor and move common section to a helper class.
    // check if test response
    if ($this->client->check_if_test($response)) {
      return $response;
    }
    // check if error response
    if ($this->client->check_if_error($response)) {
      $response = $this->client->build_error_response($response);
      return $response;
    }

    $response = $response->getBody();
    $response = json_decode($response, TRUE);
    $code_id = $this->client->id_from_url($response['code']['resourceURL']);
    $custom_response = array();
    $custom_response['code_id'] = $code_id;
    return $custom_response;
  }

  /**
   * Create a new authentication code  
   *
   * @param array $params Single parameter to hold all options.
   * <pre>
   * <ul>
   * <li><b><samp>$params['code_id']</samp></b>            <samp>string</samp>     <p>ID of the authentication code.</p></li>
   * <li><b><samp>$params['verification_code']</samp></b>  <samp>string</samp>     <p>Code that is being verified.</p></li>
   * </ul>
   * </pre>
   */

  public function verify_code($params) {

    $verification_code = $params['verification_code'];
    $code_id = $params['code_id'];

    $options = array('body'=> array());
    $options['body']['code']= ['verify'=> $verification_code];

    $uri = $code_id."/verify";
    $uri = $this->client->routes('tf.verifycode', $uri);
    $url = $this->client->_root.$uri;
    $response = $this->client->_request("PUT", $url, $options['body']);
    
    // TODO: refactor and move common section to a helper class.
    // check if test response
    if ($this->client->check_if_test($response)) {
      return $response;
    }
    // check if error response
    if ($this->client->check_if_error($response)) {
      $response = $this->client->build_error_response($response);
      return $response;
    }

    if ($response->getStatusCode() == 204) {
      $custom_response = ['verified'=> true, 'message'=> 'Success'];
    } else {
      $custom_response = ['verified'=> false, 'message'=> 'Code invalid or expired'];
    }
    return $custom_response;
  }

  /**
   * Resending the authentication code via same code resource, invalidating the previously sent code.
   *
   * @param array $params Single parameter to hold all options.
   * <pre>
   * <ul>
   * <li><b><samp>$params['code_id']</samp></b>                    <samp>string</samp> <p>Id of the authentication code.</p></li>
   * <li><b><samp>$params['destination_address']</samp></b>        <samp>string</samp> <p>Destination address of the authentication code being sent. For sms type authentication codes, it should contain a E164 phone number.</p></li>
   * <li><b><samp>$params['message']</samp></b>                    <samp>string</samp> <p>Message text sent to the destination, containing the placeholder for the code within the text. CPaaS requires to have *{code}* string within the text in order to generate a code and inject into the text. For email type code, one usage is to have the *{code}* string located within the link in order to get a unique link.</p></li>
   * <li><b><samp>$params['method']</samp></b>                     <samp>string</samp> <p>Type of the authentication code delivery method, sms and email are supported types. Possible values: sms, email</p></li>
   * <li><b><samp>$params['expiry']</samp></b>                     <samp>int</samp>    <p>Lifetime duration of the code sent in seconds. This can contain values between 30 and 3600 seconds.</p></li>
   * <li><b><samp>$params['subject']</samp></b>                    <samp>string</samp> <p>When the method is passed as email then subject becomes a mandatory field to pass. The value passed becomes the subject line of the 2FA code email that is sent out to the destinationAddress.</p></li>
   * <li><b><samp>$params['length']</samp></b>                     <samp>int</samp>    <p>Length of the authentication code tha CPaaS should generate for this request. It can contain values between 4 and 10.</p></li>
   * <li><b><samp>$params['type']</samp></b>                       <samp>string</samp> <p>Type of the code that is generated. If not provided, default value is numeric. Possible values: numeric, alphanumeric, alphabetic</p></li>
   * </ul>
   * </pre>
   */

  public function resend_code($params=null) {
    
    $destination_address = $params['destination_address'];
    $code_id = $params['code_id'];
    $expiry = array_key_exists('expiry', $params) ? $params['expiry'] : 120;
    $length = array_key_exists('length', $params) ? $params['length'] : 6;
    if (!is_array($destination_address)) {
      $destination_address = [$destination_address];
    }
    $message = $params['message'];
    $subject = $params['subject'];
    $method = array_key_exists('method', $params) ? $params['method'] : 'sms';
    $type = array_key_exists('type', $params) ? $params['type'] : 'numeric';
    
    $options = array('body'=> array(), 'headers'=> array());
    $options['body']['code'] = array();
    $options['body']['code']['expiry'] = $expiry;
    $options['body']['code']['message'] = $message;
    $options['body']['code']['subject'] = $subject;
    $options['body']['code']['address'] = $destination_address;
    $options['body']['code']['method'] = $method;
    $options['body']['code']['format'] = array('length'=> $length, 'type'=> $type);

    $uri = $this->client->routes('tf.resendcode', $code_id);
    $url = $this->client->_root.$uri;
    $response = $this->client->_request('PUT', $url, $options['body']);
    
    // TODO: refactor and move common section to a helper class.
    // check if test response
    if ($this->client->check_if_test($response)) {
      return $response;
    }
    // check if error response
    if ($this->client->check_if_error($response)) {
      $response = $this->client->build_error_response($response);
      return $response;
    }

    $response = $response->getBody();
    $response = json_decode($response, TRUE);
    $code_id = $this->client->id_from_url($response['code']['resourceURL']);
    $custom_response = array();
    $custom_response['code_id'] = $code_id;
    return $custom_response;
  }

  /**
   * Delete authentication code resource.  
   *
   * @param array $params Single parameter to hold all options.
   * <pre>
   * <ul>
   * <li><b><samp>$params['code_id']</samp></b>   <samp>string</samp>  <p>ID of the authentication code.</p></li>
   * </ul>
   * </pre>
   */  

  public function delete_code($params=null) {
    $code_id = $params['code_id'];
    $uri = $this->client->routes('tf.deletecode', $code_id);
    $url = $this->client->_root.$uri;
    $response = $this->client->_request('DELETE', $url);
    
    // TODO: refactor and move common section to a helper class.
    // check if test response
    if ($this->client->check_if_test($response)) {
      var_dump($response);
      return $response;
    }
    // check if error response
    if ($this->client->check_if_error($response)) {
      $response = $this->client->build_error_response($response);
      return $response;
    }

    $custom_response = array();
    $custom_response['code_id'] = $code_id;
    $custom_response['success'] = true;
    return $custom_response;
  }
}
