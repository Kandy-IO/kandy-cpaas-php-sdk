<?php

namespace cpaassdk;

require 'NotificationChannel.php';

use cpaassdk\ClientConfig;
use cpaassdk\NotificationChannel;

/**
  * CPaaS conversation.
  */

class Conversation {

  /**
   * @ignore
   */

  public $client = null;
  
  /**
   * @ignore
   */
  public $notificaton_channel = null;
  
  /**
   * @ignore
   */

  public $msg_type = ['SMS' => 'sms'];

  /**
   * @ignore
   */

  public function __construct(ClientConfig $client) {
    $this->client = $client;
  }

  /**
   * Send a new outbound message  
   *
   * @param array $params Single parameter to hold all options.
   * <pre>
   * <ul>
   *
   * <li><b><samp>$params['type']</samp></b>                <samp>string</samp>   <p>Type of conversation.Possible values - SMS.Check conversation.types for more options.</p></li>
   * <li><b><samp>$params['sender_address']</samp></b>      <samp>string</samp>   <p>Sender address information, basically the from address.E164 formatted DID number passed as a value, which is owned by the user. If the user wants to let CPaaS uses the default assigned DID number, this field can either has "default" value or the same value as the user_id.</p></li>
   * <li><b><samp>$params['destination_address']</samp></b> <samp>string</samp>    
   * <li><b><samp>$params['message']</samp></b>             <samp>string</samp>     
   * </ul>
   * </pre>
  */

  public function create_message($params = null) {
    $message_type = $params['type'];
    $destination_address = $params['destination_address'];
    if (!is_array($destination_address)) {
      $destination_address = [$destination_address];
    }
    $message = $params['message'];
    $sender_address = $params['sender_address'];

    if ($message_type == $this->msg_type['SMS']) {
      $options = array('body'=> array());
      $options['body']['outboundSMSMessageRequest'] = array();
      $options['body']['outboundSMSMessageRequest']['address'] = $destination_address;
      $options['body']['outboundSMSMessageRequest']['clientCorrelator'] = $this->client->client_correlator;
      $options['body']['outboundSMSMessageRequest']['outboundSMSTextMessage'] =  array();
      $options['body']['outboundSMSMessageRequest']['outboundSMSTextMessage']['message'] = $message;
      
      $uri = $sender_address."/requests";
      $uri = $this->client->routes('conversation.create_message', $uri);
      $url = $this->client->_root.$uri;
      $response = $this->client->_request('POST', $url, $options['body']);
      
      // TODO: refactor and move common section to a helper class.
      // check if test response
      if ($this->client->check_if_test($response)) {
        return $response;
      }
      // check if error response
      if ($this->client->check_if_error($response)) {
        $response = $this->client->build_error_response($response);
        return $response;
      }

      $response = $response->getBody();
      $custom_response = array();
      $custom_response['message'] = $response['outboundSMSMessageRequest']['outboundSMSTextMessage']['message'];
      $custom_response['sender_address'] = $response['outboundSMSMessageRequest']['senderAddress'];
      $custom_response['delivery_info'] = $response['outboundSMSMessageRequest']['deliveryInfoList']['deliveryInfo'];
      return $custom_response;
    }
  }

  /**
   * Send a new outbound message  
   *
   * @param array $params Single parameter to hold all options.
   * <pre>
   * <ul>
   * <li><b><samp>$params['type']</samp></b>                   <samp>string</samp> <p>Type of conversation. Possible values - SMS. Check conversation.types for more options.</p></li>
   * <li><b><samp>$params['remote_address']</samp></b>         <samp>string</samp> <p>Remote address information, information while retrieving the SMS history, basically the destination telephone number that user exchanged SMS before. E164 formatted DID number passed as a value.</p></li>
   * <li><b><samp>$params['local_address]</samp></b>           <samp>string</samp> <p>Local address information while retrieving the SMS history, basically the source telephone number that user exchanged SMS before.</p></li>
   * <li><b><samp>$params['query']</samp></b>                  <samp>JSON</samp>   <p>To hold all query related parameter.</p></li>
   * <li><b><samp>$params['query']['name']</samp></b>          <samp>string</samp> <p>Performs search operation on first_name and last_name fields.</p></li>
   * <li><b><samp>$params['query']['first_name']</samp></b>    <samp>string</samp> <p>Performs search for the first_name field of the directory items.</p></li>
   * <li><b><samp>$params['query']['last_name']</samp></b>     <samp>string</samp> <p>Performs search for the last_name field of the directory items.</p></li>
   * <li><b><samp>$params['query']['user_name']</samp></b>     <samp>string</samp> <p>Performs search for the user_name field of the directory items.</p></li>
   * <li><b><samp>$params['query']['phone_number']</samp></b>  <samp>string</samp> <p>Performs search for the fields containing a phone number, like businessPhoneNumber, homePhoneNumber, mobile, pager, fax.</p></li>
   * <li><b><samp>$params['query']['order']</samp></b>         <samp>string</samp> <p>Ordering the contact results based on the requested sortBy value, order query parameter should be accompanied by sortBy query parameter.</p></li>
   * <li><b><samp>$params['query']['sort_by']</samp></b>       <samp>string</samp> <p>SortBy value is used to detect sorting the contact results based on which attribute. If order is not provided with that, ascending order is used.</p></li>
   * <li><b><samp>$params['query']['max']</samp></b>           <samp>int</samp>    <p>Maximum number of contact results that has been requested from CPaaS for this query.</p></li>
   * <li><b><samp>$params['query']['next']</samp></b>          <samp>string</samp> <p>Pointer for the next chunk of contacts, should be gathered from the previous query results.</p></li>
   * </ul>
   * </pre>
   */

  public function get_messages($params=null) {
    $message_type = $params['type'];
    $remote_address = $params['remote_address'];
    $local_address = $params['local_address'];
    $query = $params['query'];

    if ($message_type == $this->msg_type['SMS']) {
      $options = ['query' => $query];
      $uri = 'remoteAddresses';
      if ($remote_address) {
        $uri = $uri."/".$remote_address;
      }
      if ($local_address) {
        $uri = "/".$uri."/localAddresses/".$local_address;
      }
      $uri = $this->client->routes('conversation.get_messages', $uri);
      $url = $this->client->_root.$uri;
      $response = $this->client->_request('GET', $url, $options['query']);
      
      // TODO: refactor and move common section to a helper class.
      // check if test response
      if ($this->client->check_if_test($response)) {
        return $response;
      }
      // check if error response
      if ($this->client->check_if_error($response)) {
        $response = $this->client->build_error_response($response);
        return $response;
      }
      
      return $this->client->parse_response($response->getBody());
    }
  }

  /**
   * Read a conversation message status
   * 
   * @param array $params Single parameter to hold all options.
   * <pre>
   * <ul>
   * <li><b><samp>$params['type']</samp></b>           <samp>string</samp>   <p>Type of conversation. Possible values -  SMS. Check conversation.types for more options.</p></li>
   * <li><b><samp>$params['remote_address']</samp></b> <samp>string</samp>   <p>Remote address information, information while retrieving the SMS history, basically the destination telephone number that user exchanged SMS before. E164 formatted DID number passed as a value.</p></li>
   * <li><b><samp>$params['local_address']</samp></b>  <samp>string</samp>   <p>Local address information while retrieving the SMS history, basically the source telephone number that user exchanged SMS before.</p></li>
   * <li><b><samp>$params['message_id']</samp></b>     <samp>string</samp>   <p>Identification of the SMS message.</p></li>
   * </ul>
   * </pre>
   */

  public function get_status($params=null) {
    $message_type = $params['type'];
    $remote_address = $params['remote_address'];
    $local_address = $params['local_address'];
    $message_id = $params['message_id'];

    if ($message_type == $this->msg_type['SMS']) {
      $uri = '/remoteAddresses/'.$remote_address.'/localAddresses/'.$local_address.'/messages/'.$message_id.'/status';
      $uri = $this->client->routes('conversation.get_status', $uri);
      $url = $this->client->_root.$uri;
      $response = $this->client->_request('GET', $url);
      
      // TODO: refactor and move common section to a helper class.
      // check if test response
      if ($this->client->check_if_test($response)) {
        return $response;
      }
      // check if error response
      if ($this->client->check_if_error($response)) {
        $response = $this->client->build_error_response($response);
        return $response;
      }

      return $this->client->parse_response($response->getBody());
    }
  }

  /**
   * Read all messages in a thread  
   *
   * @param array $params Single parameter to hold all options.
   * <pre>
   * <ul>
   * <li><b><samp>$params['type']</samp></b>                        <samp>string</samp> <p>Type of conversation. Possible values - SMS. Check conversation.types for more options.</p></li>
   * <li><b><samp>$params['remote_address']</samp></b>              <samp>string</samp> <p>Remote address information, information while retrieving the SMS history, basically the destination telephone number that user exchanged SMS before. E164 formatted DID number passed as a value.</p></li>
   * <li><b><samp>$params['local_address']</samp></b>               <samp>string</samp> <p>Local address information while retrieving the SMS history, basically the source telephone number that user exchanged SMS before.</p></li>
   * <li><b><samp>$params['query']</samp></b>                       <samp>JSON</samp>   <p>To hold all query related parameter.</p></li>
   * <li><b><samp>$params['query']['max']</samp></b>                <samp>int</samp>    <p>Maximum number of contact results that has been requested from CPaaS for this query.</p></li>
   * <li><b><samp>$params['query']['next']</samp></b>               <samp>string</samp> <p>Filters the messages or threads having messages that are not received by the user yet.</p></li>
   * <li><b><samp>$params['query']['last_message_time']</samp></b>  <samp>string</samp> <p>Filters the messages or threads having messages that are sent/received after provided Epoch time.</p></li>
   * </ul>
   * </pre>
   */

  public function get_messages_in_thread($params=null) {
    $message_type = $params['type'];
    $remote_address = $params['remote_address'];
    $local_address = $params['local_address'];
    $query = $params['query'];

    if ($message_type == $this->msg_type['SMS']) {
      $options = ['query' => $query];
      $uri = '/remoteAddresses/'.$remote_address.'/localAddresses/'.$local_address.'/messages';
      $uri = $this->client->routes('conversation.get_messages_in_thread', $uri);
      $url = $this->client->_root.$uri;
      $response = $this->client->_request('GET', $url, $options['query']);
      
      // TODO: refactor and move common section to a helper class.
      // check if test response
      if ($this->client->check_if_test($response)) {
        return $response;
      }
      // check if error response
      if ($this->client->check_if_error($response)) {
        $response = $this->client->build_error_response($response);
        return $response;
      }

      return $this->client->parse_response($response->getBody());
    }
  }

  /**
   * Delete conversation message
   * 
   * @param array $params Single parameter to hold all options.
   * <pre>
   * <ul>
   * <li><b><samp>$params['type']</samp></b>           <samp>string</samp>   <p>Type of conversation. Possible values -  SMS. Check conversation.types for more options.</p></li>
   * <li><b><samp>$params['remote_address']</samp></b> <samp>string</samp>   <p>Remote address information, information while retrieving the SMS history, basically the destination telephone number that user exchanged SMS before. E164 formatted DID number passed as a value.</p></li>
   * <li><b><samp>$params['local_address']</samp></b>  <samp>string</samp>   <p>Local address information while retrieving the SMS history, basically the source telephone number that user exchanged SMS before.</p></li>
   * <li><b><samp>$params['message_id']</samp></b>     <samp>string</samp>   <p>Identification of the SMS message.</p></li>
   * </ul>
   * </pre>
   */

  public function delete_message($params=null) {
    $message_type = $params['type'];
    $remote_address = $params['remote_address'];
    $local_address = $params['local_address'];
    $message_id = $params['message_id'];

    if ($message_type == $this->msg_type['SMS']) {
      $uri = '/remoteAddresses/'.$remote_address.'/localAddresses/'.$local_address.'/messages/'.$message_id;
      $uri = $this->client->routes('conversation.delete_message', $uri);
      $url = $this->client->_root.$uri;
      $response = $this->client->_request('DELETE', $url);
      
      // TODO: refactor and move common section to a helper class.
      // check if test response
      if ($this->client->check_if_test($response)) {
        return $response;
      }
      // check if error response
      if ($this->client->check_if_error($response)) {
        $response = $this->client->build_error_response($response);
        return $response;
      }

      return $this->client->parse_response($response->getBody());
    }
  }

  /**
   * Single parameter to hold all options.
   * 
   * @param array $params Single parameter to hold all options.
   * <pre>
   * <ul>
   * <li><b><samp>$params['type']</samp></b>           <samp>string</samp>   <p>Type of conversation. Possible values -  SMS. Check conversation.types for more options.</p></li>
   * </ul>
   * </pre>
   */

  public function get_subscriptions($params=null) {
    $message_type = $params['type'];

    if ($message_type == $this->msg_type['SMS']) {
      $uri = $this->client->routes('conversation.get_subscriptions');
      $url = $this->client->_root.$uri;
      $response = $this->client->_request('GET', $url);
      
      // TODO: refactor and move common section to a helper class.
      // check if test response
      if ($this->client->check_if_test($response)) {
        return $response;
      }
      // check if error response
      if ($this->client->check_if_error($response)) {
        $response = $this->client->build_error_response($response);
        return $response;
      }
      
      $response = $response->getBody();
      // check the response if an array object.
      $custom_response = array();
      if (array_key_exists('subscriptionList', $response) && array_key_exists('subscription', $response['subscriptionList'])) {
        foreach($response['subscriptionList']['subscription'] as $subscription) {
          $subscription_item = [
            'notify_url' => $subscription['callbackReference']['notifyURL'],
            'destination_address' => $subscription['destinationAddress'],
            'subscription_id' => $this->client->id_from_url($subscription['resourceURL'])
          ];
          array_push($custom_response, $subscription_item);
        }
      }
      return $custom_response;
    }
  }

  /**
   * Read active subscription.
   * 
   * @param array $params Single parameter to hold all options.
   * <pre>
   * <ul>
   * <li><b><samp>$params['type']</samp></b>             <samp>string</samp>   <p>Type of conversation. Possible values -  SMS. Check conversation.types for more options.</p></li>
   * <li><b><samp>$params['subscription_id']</samp></b>  <samp>string</samp>   <p>Resource ID of the subscription.</p></li>
   * </ul>
   * </pre>
   */

  public function get_subscription($params=null) {
    $message_type = $params['type'];
    $subscription_id = $params['subscription_id'];

    if ($message_type == $this->msg_type['SMS']) {
      $uri = $subscription_id;
      $uri = $this->client->routes('conversation.get_subscription', $uri);
      $url = $this->client->_root.$uri;
      $response = $this->client->_request('GET', $url);
      
      // TODO: refactor and move common section to a helper class.
      // check if test response
      if ($this->client->check_if_test($response)) {
        return $response;
      }
      // check if error response
      if ($this->client->check_if_error($response)) {
        $response = $this->client->build_error_response($response);
        return $response;
      }

      $response = $response->getBody();
      // check the response if an array object.
      
      if (array_key_exists('subscription', $response)) {
        
          $custom_response = [
            'notify_url' => $response['subscription']['callbackReference']['notifyURL'],
            'destination_address' => $response['subscription']['destinationAddress'],
            'subscription_id' => $this->client->id_from_url($response['subscription']['resourceURL'])
          ];
      }
      return $custom_response;
    }
  }

  /**
   * Create a new subscription.
   * 
   * @param array $params Single parameter to hold all options.
   * <pre>
   * <ul>
   * <li><b><samp>$params['type']</samp></b>                 <samp>string</samp>   <p>Type of conversation. Possible values -  SMS. Check conversation.types for more options.</p></li>
   * <li><b><samp>$params['webhook_url']</samp></b>          <samp>string</samp>   <p>The webhook that has been acquired during SMS API subscription, which the incoming notifications supposed to be sent to.</p></li>
   * <li><b><samp>$params['destination_address']</samp></b>  <samp>string</samp>   <p>The address that incoming messages are received for this subscription. If does not exist, CPaaS uses the default assigned DID number to subscribe against. It is suggested to provide the intended E164 formatted DID number within this parameter.</p></li>
   * </ul>
   * </pre>
   */

  public function subscribe($params=null) {
    $message_type = $params['type'];
    $destination_address = $params['destination_address'];

    if ($message_type == $this->msg_type['SMS']) {
      # create a notifyURL with webhookURL
      $this->notificaton_channel = new NotificationChannel($this->client);
      $channel = $this->notificaton_channel->create_channel($params);
      $channel = json_decode($channel->getBody(), TRUE);

      $options = array('body' => array());
      $options['body']['subscription'] = array();
      $options['body']['subscription']['callbackReference'] = ['notifyURL' => $channel['channel_id']];
      $options['body']['subscription']['clientCorrelator'] = $this->client->client_correlator;
      $options['body']['subscription']['destinationAddress'] = $destination_address;

      $uri = $this->client->routes('conversation.subscribe', $uri);
      $url = $this->client->_root.$uri;
      $response = $this->client->_request("POST", $url, $options['body']);
      
      // TODO: refactor and move common section to a helper class.
      // check if test response
      if ($this->client->check_if_test($response)) {
        return $response;
      }
      // check if error response
      if ($this->client->check_if_error($response)) {
        $response = $this->client->build_error_response($response);
        return $response;
      }

      $response = $response->getBody();
      $custom_response = array();
      $custom_response['webhook_url'] = $params['webhook_url'];
      $custom_response['destination_address'] = $response['subscription']['destinationAddress'];
      $custom_response['subscription_id'] = $this->client->id_from_url($response['subscription']['resourceURL']);

      return $custom_response;
    }
  }

  /**
   * Unsubscription from conversation notification
   * 
   * @param array $params Single parameter to hold all options.
   * <pre>
   * <ul>
   * <li><b><samp>$params['type']</samp></b>             <samp>string</samp>   <p>Type of conversation. Possible values -  SMS. Check conversation.types for more options.</p></li>
   * <li><b><samp>$params['subscription_id']</samp></b>  <samp>string</samp>   <p>Resource ID of the subscription.</p></li>
   * </ul>
   * </pre>
   */

  public function unsubscribe($params=null) {
    $message_type = $params['type'];
    $subscription_id = $params['subscription_id'];

    if ($message_type == $this->msg_type['SMS']) {
      $uri = $subscription_id;
      $uri = $this->client->routes('conversation.unsubscribe', $uri);
      $url = $this->client->_root.$uri;
      $response = $this->client->_request('DELETE', $url);
      
      // TODO: refactor and move common section to a helper class.
      // check if test response
      if ($this->client->check_if_test($response)) {
        return $response;
      }
      // check if error response
      if ($this->client->check_if_error($response)) {
        $response = $this->client->build_error_response($response);
        return $response;
      }

      $response = $response->getBody();
      $custom_response = array();
      $custom_response['subscription_id'] = $subscription_id;
      $custom_response['success'] = true;
      $custom_response['message'] = "Unsubscribed from ".$message_type." conversation notification";
      return $custom_response;
    }
  }
}

?>

