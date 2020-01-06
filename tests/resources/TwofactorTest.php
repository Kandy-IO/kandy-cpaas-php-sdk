<?php

namespace cpaassdk;

require '../src/Client.php';
require '../tests/MockCustomClient.php';
require '../src/Resources/Twofactor.php';

use PHPUnit\Framework\TestCase;

use cpaassdk\Api;
use cpaassdk\Twofactor;
use cpaassdk\MockCustomClient;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;


class TwofactorTest extends TestCase {
  
  public $client = null;
  public $twofactor = null;


  public function setup() {
    $mock = new MockCustomClient();
    $mock_client = $mock->getGuzzleMockClient();
    $this->client = new Api($client_id, $client_secret, $mock_client);
    $this->twofactor = new Twofactor($this->client);
  }

  public function testSendCode() {
    $params = [
      'message'=>'Your verification code {code}',
      'destination_address'=>'+12059002006',
      'sender_address'=>'+15202241139'
    ];
    $send_code = $this->twofactor->send_code($params);
    $send_code = json_decode($send_code->getBody(), TRUE);
    $this->assertNotNull($send_code);
    $this->assertEquals($send_code['url'], '/cpaas/auth/v1/test-user/codes');
  }

  public function testResendCode() {
    $params = [
      'message'=>'Your verification code {code}',
      'destination_address'=>'+12059002006',
      'sender_address'=>'+15202241139',
      'code_id'=>'test-code-id'
    ];
    $resend_code = $this->twofactor->resend_code($params);
    $resend_code = json_decode($resend_code->getBody(), TRUE);
    $this->assertNotNull($resend_code);
    $this->assertEquals($resend_code['url'], '/cpaas/auth/v1/test-user/codes/test-code-id');
  }

  public function testVerifyCode() {
    $params = [
      'destination_address'=>'+12059002006',
      'sender_address'=>'+15202241139',
      'verification_code' => '1111',
      'code_id' => 'test-code-id'
    ];
    $verify_response = $this->twofactor->verify_code($params);
    $verify_response = json_decode($verify_response->getBody(), TRUE);
    $this->assertNotNull($verify_response);
    $this->assertEquals($verify_response['url'], '/cpaas/auth/v1/test-user/codes/test-code-id/verify');
  }

  public function testdeleteCode() {
    $params = [
      'code_id' => 'test-code-id'
    ];
    $delete_response = $this->twofactor->delete_code($params);
    $delete_response = json_decode($delete_response->getBody(), TRUE);
    $this->assertNotNull($delete_response);
    $this->assertEquals($delete_response['url'], '/cpaas/auth/v1/test-user/codes/test-code-id');
  }
}
