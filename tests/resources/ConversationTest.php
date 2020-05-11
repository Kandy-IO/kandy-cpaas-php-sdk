<?php

namespace CpaasSdkTest\Resources;

use PHPUnit\Framework\TestCase;

use CpaasSdk\Api;
use CpaasSdk\Resources\Conversation;
use CpaasSdkTest\MockCustomClient;

class ConversationTest extends TestCase {

  public $client = null;
  public $conversation = null;

  public function setup() {
    $config = [
      'client_id' => 'test-client-id',
      'client_secret' => 'test-client-secret',
      'base_url' => 'https://test-base.com'
    ];

    $mock = new MockCustomClient();
    $mock_client = $mock->getGuzzleMockClient();
    $this->client = new Api($config, $mock_client);
    $this->conversation = new Conversation($this->client);
  }

  public function testCreateMessage() {
    $params = [
      'type' => 'sms',
      'destination_address' => 'test-destination-address',
      'sender_address'=>'test-sender-address',
      'message' => 'this is a test message'
    ];
    $create_message_response = $this->conversation->create_message($params);
    $create_message_response = json_decode($create_message_response->getBody(), TRUE);
    $this->assertNotNull($create_message_response);
    $this->assertEquals($create_message_response['url'], '/cpaas/smsmessaging/v1/test-user/outbound/test-sender-address/requests');
  }

  public function testGetMessages() {
    $params = [
      'type' => 'sms',
      'remote_address' => 'test-remote-address',
      'local_address'=>'test-local-address',
      'query' => ['max'=> 10, 'new' =>'test']
    ];
    $get_message_response = $this->conversation->get_messages($params);
    $get_message_response = json_decode($get_message_response->getBody(), TRUE);
    $this->assertNotNull($get_message_response);
    $this->assertEquals($get_message_response['url'], '/cpaas/smsmessaging/v1/test-user/remoteAddresses/test-remote-address/localAddresses/test-local-address');
  }

  public function testGetStatus() {
    $params = [
      'type' => 'sms',
      'remote_address' => 'test-remote-address',
      'local_address'=>'test-local-address',
      'message_id'=> 'test-message-id'
    ];
    $get_status_response = $this->conversation->get_status($params);
    $get_status_response = json_decode($get_status_response->getBody(), TRUE);
    $this->assertNotNull($get_status_response);
    $this->assertEquals($get_status_response['url'], '/cpaas/smsmessaging/v1/test-user/remoteAddresses/test-remote-address/localAddresses/test-local-address/messages/test-message-id/status');
  }

  public function testDeleteMessage() {
    $params = [
      'type' => 'sms',
      'remote_address' => 'test-remote-address',
      'local_address'=>'test-local-address',
      'message_id'=> 'test-message-id'
    ];
    $delete_message_response = $this->conversation->delete_message($params);
    $delete_message_response = json_decode($delete_message_response->getBody(), TRUE);
    $this->assertNotNull($delete_message_response);
    $this->assertEquals($delete_message_response['url'], '/cpaas/smsmessaging/v1/test-user/remoteAddresses/test-remote-address/localAddresses/test-local-address/messages/test-message-id');
  }

  public function testUnsubscribe() {
    $params = [
      'type' => 'sms',
      'subscription_id'=> 'test-subscription-id'
    ];
    $unsubscribe_response = $this->conversation->unsubscribe($params);
    $unsubscribe_response = json_decode($unsubscribe_response->getBody(), TRUE);
    $this->assertNotNull($unsubscribe_response);
    $this->assertEquals($unsubscribe_response['url'], '/cpaas/smsmessaging/v1/test-user/inbound/subscriptions/test-subscription-id');

  }

  // TODO: Fix this. Internally it call NotificationChannel.create_channel and the response data is used for further request.
  // Need to return proper mock data for this method to continue.
  // public function testSubscribe() {
  //   $params = [
  //     'type' => 'sms',
  //     'destination_address'=> 'test-destination-address'
  //   ];
  //   $subscribe_response = $this->conversation->subscribe($params);
  //   $subscribe_response = json_decode($subscribe_response->getBody(), TRUE);
  //   $this->assertNotNull($subscribe_response);
  //   $this->assertEquals($subscribe_response['url'], '/cpaas/smsmessaging/v1/test-user/inbound/subscriptions');
  // }
}
