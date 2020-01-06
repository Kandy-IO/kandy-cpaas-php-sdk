<?php

namespace cpaassdk;


use cpaassdk\Api;

class NotificationChannel {
  public $client = null;

  public function __construct(Api $client) {
    $this->client = $client;
  }

  public function channels($params=null) {
    $uri = '/cpaas/notificationchannel/v1/'.$this->client->user_id."/channels";
    $url = $this->client->_root.$uri;

    $response = $this->client->_request('GET', $url);

    if ($this->client->check_if_error($response)) {
      $response = $this->client->build_error_response($response);
      
      return $response;
    }
    $response = $response->getBody();
    
    return $response;
  }

  public function channel($params=null) {

    $uri = '/cpaas/notificationchannel/v1/'.$this->client->user_id."/channels/".$params['channel_id'];
    $url = $this->client->_root.$uri;

    $response = $this->client->_request('GET', $url);

    if ($this->client->check_if_error($response)) {
      $response = $this->client->build_error_response($response);
      
      return $response;
    }
    $response = $response->getBody();
    
    return $response;
  }

  public function create_channel($params=null) {
    $webhook_url = $params['webhook_url'];
    $options = array('body' => array());
    $options['body']['notificationChannel']['channelData'] = array('x-webhookURL' => $webhook_url);
    $options['body']['notificationChannel']['channelType'] = 'webhooks';
    $options['body']['notificationChannel']['clientCorrelator'] = $this->client->client_correlator;
    
    $uri = '/cpaas/notificationchannel/v1/'.$this->client->user_id."/channels";
    $url = $this->client->_root.$uri;
    $response = $this->client->_request("POST", $url, $options);

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
    $custom_response = array();
    $custom_response['channel_id'] = $response['notificationChannel']['callbackURL'];
    $custom_response['webhook_URL'] = $response['notificationChannel']['channelData']['x-weebhookURL'];
    $custom_response['channel_type'] = $response['notificationChannel']['channelType'];
    
    return $custom_response;
  }
}
?>