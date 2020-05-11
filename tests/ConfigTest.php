<?php

namespace CpaasSdkTest;

use PHPUnit\Framework\TestCase;

use CpaasSdk\Config;

class ConfigTest extends TestCase {
  public $client = null;

  public function testConstructorShouldRaiseErrorWithEmptyParams() {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Missing base_url in config.');

    $config = new Config();
  }

  public function testConstructorShouldRaiseErrorWithOnlyBaseUrl() {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Missing client_id, mandatory value.');

    $config = new Config([
      'base_url' => 'test-base-url'
    ]);
  }

  public function testConstructorShouldRaiseErrorWithoutClientSecret() {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Missing client_secret or email/password. Either one has to be present for authentication.');

    $config = new Config([
      'base_url' => 'test-base-url',
      'client_id' => 'test-client-id'
    ]);
  }

  public function testConstructorShouldRaiseErrorWithoutPassword() {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Missing client_secret or email/password. Either one has to be present for authentication.');

    $config = new Config([
      'base_url' => 'test-base-url',
      'client_id' => 'test-client-id',
      'email' => 'email@test.com'
    ]);
  }

  public function testConstructorShouldCreateValidObjectWithProjectCredentials() {
    $params = [
      'base_url' => 'test-base-url',
      'client_id' => 'test-client-id',
      'client_secret' => 'test-client-secret'
    ];

    $config = new Config($params);

    $this->assertEquals($config->base_url, $params['base_url']);
    $this->assertEquals($config->client_id, $params['client_id']);
    $this->assertEquals($config->client_secret, $params['client_secret']);
    $this->assertEquals($config->client_correlator, $params['client_id'].'-php');
  }

  public function testConstructorShouldCreateValidObjectWithAccountCredentials() {
    $params = [
      'base_url' => 'test-base-url',
      'client_id' => 'test-client-id',
      'email' => 'email@test.com',
      'password' => 'test-password'
    ];

    $config = new Config($params);

    $this->assertEquals($config->base_url, $params['base_url']);
    $this->assertEquals($config->client_id, $params['client_id']);
    $this->assertEquals($config->email, $params['email']);
    $this->assertEquals($config->password, $params['password']);
    $this->assertEquals($config->client_correlator, $params['client_id'].'-php');
  }
}
