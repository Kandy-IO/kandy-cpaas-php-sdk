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
   * Configure the SDK with client_id, client_secret or email/password.
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
   * @param array $config Configuration.
   * <pre>
   * <ul>
   * <li><b><samp>$config['client_id']</samp></b> <samp>string</samp> <p>Private project key / Account client ID. If Private project key is used then client_secret is mandatory. If account client ID is used then email and password are mandatory.</p></li>
   * <li><b><samp>$config['base_url']</samp></b> <samp>string</samp> <p>URL of the server to be used.</p></li>
   * <li><b><samp>$config['client_secret']</samp></b> <samp>string</samp> <i>optional</i> <p>Private project secret.</p>
   * <li><b><samp>$config['email']</samp></b> <samp>string</samp>  <i>optional</i> <p>Account login email.</p>
   * <li><b><samp>$config['email']</samp></b> <samp>string</samp>  <i>optional</i> <p>Account login password.</p>
   * </ul>
   * </pre>
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