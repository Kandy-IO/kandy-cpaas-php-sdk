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
   * $client = new Client([
   *  'client_id': 'private project key',
   *  'client_secret': 'private project secret',
   *  'base_url': 'base url'
   * ]);
   *
   * // or
   *
   * $client = new Client([
   *  'client_id': 'account client ID',
   *  'email': 'account email',
   *  'password': 'account password',
   *  'base_url': 'base url'
   * ]);
   *
   * </code>
   * </pre>
   *
   * @param string $config['client_id'] Private project key / Account client ID. If Private project key is used then client_secret is mandatory. If account client ID is used then email and password are mandatory.
   * @param string $config['base_url'] URL of the server to be used.
   * @param string $config['client_secret'] <i>optional</i> Private project secret.
   * @param string $config['email'] <i>optional</i> Account login email.
   * @param string $config['password'] <i>optional</i> Account login password.
   */

  public function __construct($config) {
    $creds = new Config($config);

    $this->client = new Api($creds);
    $this->conversation = new Conversation($this->client);
    $this->twofactor = new Twofactor($this->client);
    $this->notification = new Notification($this->client);
  }

}
?>