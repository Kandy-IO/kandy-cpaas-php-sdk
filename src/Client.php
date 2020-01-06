<?php

namespace cpaassdk;

require '../vendor/autoload.php';

require 'Api.php';
require 'Resources/Twofactor.php';
require 'Resources/Conversation.php';
require 'Resources/Notification.php';

use cpaassdk\Twofactor;
use cpaassdk\Conversation;
use cpaassdk\Api;
use cpaassdk\Notification;

/**
  * Configure the SDK with client_id, client_secret and base_url.
  * Example:
  * <code>
  * $client = new Client(
  *             'private project key',
  *             'private project secret',
  *             'base url');
  * </code>
  */

class Client {

  /**
   * @ignore
   */
  public $twofactor = null;
  /**
   * @ignore
   */
  public $conversation = null;
  /**
   * @ignore
   */
  public $notification = null;
  /**
   * @ignore
   */
  public $client = null;

  /**
   * @param string $client_id Private project key.
   * @param string $client_secret Private project secret.
   * @param string $base_url URL of the server to be used.
   */

  public function __construct($client_id, $client_secret, $base_url) {
    $this->client = new Api($client_id, $client_secret, $base_url);
    $this->conversation = new Conversation($this->client);
    $this->twofactor = new Twofactor($this->client);
    $this->notification = new Notification($this->client);
  }

}
?>