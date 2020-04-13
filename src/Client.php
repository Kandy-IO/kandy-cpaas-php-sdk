<?php

namespace CpaasSdk;

use CpaasSdk\Resources\Twofactor;
use CpaasSdk\Resources\Conversation;
use CpaasSdk\Resources\Notification;

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
   * Configure the SDK with client_id, client_secret and base_url.
   *
   * <pre>
   * <code>
   * $client = new Client(
   *   'private project key',
   *   'private project secret',
   *   'base url'
   * );
   * </code>
   * </pre>
   *
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