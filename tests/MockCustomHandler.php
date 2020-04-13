<?php

namespace CpaasSdkTest;

use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Promise;
use GuzzleHttp\Psr7;
use \Firebase\JWT\JWT;
use \Datetime;

class MockCustomHandler {
  public function __invoke(RequestInterface $req, array $options) {
    $path = $req->getUri()->getPath();
    switch ($path) {
      case '/cpaas/auth/v1/token':
        $body = $this->getJWTBody();
        $body = json_encode($body);
        break;
      case '/cpaas/notificationchannel/v1/test-user-id/channels':
        $body = ['test_request' => true, 'channel_id' => 'test-channel-id', 'webhook_url' => 'test-webhook-url', 'channel_type' => 'webhooks'];
        $body = json_encode($body);
      default:
        $body = ['test_request' => true, 'url' => $path, 'body' => json_decode($req->getBody(), TRUE)];
        $body = json_encode($body);
    }
    return new Promise\FulfilledPromise(
        new Psr7\Response(200, [], $body)
    );
  }

  public function getJWTBody() {
    $date = new DateTime();
    $exp_timestamp = $date->getTimestamp() + 6*60*60;
    $access_token_payload = ['exp' => $date->getTimestamp()];
    $id_token_payload = ['preferred_username' => 'test-user'];
    $secret = 'test-secret';

    $access_token = JWT::encode($access_token_payload, $secret);
    $id_token = JWT::encode($id_token_payload, $secret);
    $body = ['access_token' => $access_token, 'id_token' => $id_token];
    return $body;
  }
}