<?php

namespace CpaasSdkTest;

use PHPUnit\Framework\TestCase;

use CpaasSdk\Api;

class ClientTest extends TestCase {
  public $client = null;

  public function setUp() {
    $mock = new MockCustomClient();
    $mock_client = $mock->getGuzzleMockClient();
    $this->client = new Api($client_id, $client_secret, $base_url, $mock_client);
  }

  public function testClientCredentials() {
    $this->assertNotNull($this->client->access_token);
    $this->assertNotNull($this->client->user_id);
    $this->assertNotNull($this->client->id_token);
  }

  public function testComposeHeaders() {
    $headers = $this->client->compose_headers();
    $this->assertEquals($headers['Content-Type'], 'application/json');
    $this->assertEquals($headers['X-Cpaas-Agent'], 'php-sdk/'.$this->client->_version);
  }

  public function tearDown() {
    $this->client = null;
  }
}