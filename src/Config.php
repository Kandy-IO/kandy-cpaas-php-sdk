<?php

namespace CpaasSdk;

class Config {
  public $client_id = null;
  public $client_secret = null;
  public $base_url = null;
  public $email = null;
  public $password = null;
  public $client_correlator = null;


  public function __construct($config = []) {
    if (!$config['base_url']) {
      throw new \InvalidArgumentException('Missing base_url in config.');
    }

    if (!$config['client_id']) {
      throw new \InvalidArgumentException('Missing client_id, mandatory value.');
    }

    if (!$config['client_secret'] && (!$config['email'] || !$config['password'])) {
      throw new \InvalidArgumentException('Missing client_secret or email/password. Either one has to be present for authentication.');
    }

    $this->client_id = $config['client_id'];
    $this->base_url = $config['base_url'];
    $this->client_secret = $config['client_secret'];
    $this->email = $config['email'];
    $this->password = $config['password'];
    $this->client_correlator = $config['client_id'].'-php';
  }
}
?>