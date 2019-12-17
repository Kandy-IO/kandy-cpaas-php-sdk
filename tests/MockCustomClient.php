<?php

namespace cpaassdk;

require 'MockCustomHandler.php';

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

use cpaassdk\MockCustomHandler;

class MockCustomClient {

  public function getGuzzleMockClient() {
    $string = json_encode($body);
    $mock = new MockCustomHandler();
    $handler = HandlerStack::create($mock);
    $client = new Client(['handler' => $handler]);
    return $client;
  }  
}