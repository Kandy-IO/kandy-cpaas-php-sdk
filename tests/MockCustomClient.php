<?php

namespace CpaasSdkTest;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

class MockCustomClient {
  public function getGuzzleMockClient() {
    $string = json_encode($body);
    $mock = new MockCustomHandler();
    $handler = HandlerStack::create($mock);
    $client = new Client(['handler' => $handler]);
    return $client;
  }
}