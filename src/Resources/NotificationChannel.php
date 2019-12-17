<?php

namespace cpaassdk;


use cpaassdk\ClientConfig;

class NotificationChannel {
  public $client = null;

  public function __construct(ClientConfig $client) {
    $this->client = $client;
  }

  public function channels($params=null) {
    $uri = $this->client->routes('notification.channels');
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

    $uri = $params['channel_id'];
    
    $uri = $this->client->routes('notification.channel', $uri);
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
    $options['body']['notificationChannel']['channelType'] = $this->client->client_correlator;

    $uri = $this->client->routes('notification.create_channel');
    $url = $this->client->_root.$uri;
    
    $response = $this->client->_request("POST", $url, $options['body']);

    // TODO: refactor and move common section to a helper class.
    // check if test response
    if ($this->client->check_if_test($response)) {
      var_dump($response->getBody(), 'this is from the dump');
      return $response;
    }
    // check if error response
    if ($this->client->check_if_error($response)) {
      $response = $this->client->build_error_response($response);
      return $response;
    }

    $response = $response->getBody();
    $custom_response = array();
    $custom_response['channel_id'] = $response['notificationChannel']['callbackURL'];
    $custom_response['webhook_URL'] = $response['notificationChannel']['channelData']['x-weebhookURL'];
    $custom_response['channel_type'] = $response['notificationChannel']['channelType'];
    return $custom_response;
  }
}
?>