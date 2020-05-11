<?php

namespace CpaasSdk\Resources;

use CpaasSdk\Api;
use CpaasSdk\Resources\NotificationChannel;

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
  public $notification_channel = null;

  /**
   * @ignore
   */

  public $types = ['SMS' => 'sms'];

  /**
   * @ignore
   */

  public function __construct(Api $client) {
    $this->client = $client;
  }

  /**
   * Send a new outbound message
   *
   * @param array $params Single parameter to hold all options.
   * <pre>
   * <ul>
   *
   * <li><b><samp>$params['type']</samp></b>                <samp>string</samp>   <p>Type of conversation.Possible value(s) - sms. Check conversation->types for more options.</p></li>
   * <li><b><samp>$params['sender_address']</samp></b>      <samp>string</samp>   <p>Sender address information, basically the from address. E164 formatted DID number passed as a value, which is owned by the user. If the user wants to let CPaaS uses the default assigned DID number, then this field should have "default" as it's value.</p></li>
   * <li><b><samp>$params['destination_address']</samp></b> <samp>string</samp>   <p>Indicates which DID number(s) used as destination for this SMS.</p>
   * <li><b><samp>$params['message']</samp></b>             <samp>string</samp>   <p>SMS text message.</p>
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

    if ($message_type == $this->types['SMS']) {
      $options = array('body'=> array());
      $options['body']['outboundSMSMessageRequest'] = array();
      $options['body']['outboundSMSMessageRequest']['address'] = $destination_address;
      $options['body']['outboundSMSMessageRequest']['clientCorrelator'] = $this->client->config->client_correlator;
      $options['body']['outboundSMSMessageRequest']['outboundSMSTextMessage'] =  array();
      $options['body']['outboundSMSMessageRequest']['outboundSMSTextMessage']['message'] = $message;

      $uri = $sender_address."/requests";
      $uri = "/cpaas/smsmessaging/v1/".$this->client->user_id."/outbound/".$uri;
      $url = $this->client->_root.$uri;
      $response = $this->client->_request('POST', $url, $options);

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
      $response = json_decode($response, TRUE);
      $custom_response = array();
      $custom_response['message'] = $response['outboundSMSMessageRequest']['outboundSMSTextMessage']['message'];
      $custom_response['sender_address'] = $response['outboundSMSMessageRequest']['senderAddress'];
      $custom_response['delivery_info'] = $response['outboundSMSMessageRequest']['deliveryInfoList']['deliveryInfo'];
      return $custom_response;
    }
  }

  /**
   * Gets all messages.
   *
   * @param array $params Single parameter to hold all options.
   * <pre>
   * <ul>
   * <li><b><samp>$params['type']</samp></b>                   <samp>string</samp> <p>Type of conversation. Possible value(s) - sms. Check conversation->types for more options.</p></li>
   * <li><b><samp>$params['remote_address']</samp></b>         <samp>string</samp> <i>optional</i> <p>Remote address information, information while retrieving the SMS history, basically the destination telephone number that user exchanged SMS before. E164 formatted DID number passed as a value.</p></li>
   * <li><b><samp>$params['local_address]</samp></b>           <samp>string</samp> <i>optional</i> <p>Local address information while retrieving the SMS history, basically the source telephone number that user exchanged SMS before.</p></li>
   * <li><b><samp>$params['query']</samp></b>                  <samp>array</samp>  <i>optional</i> <p>To hold all query related parameter.</p></li>
   * <li><b><samp>$params['query']['name']</samp></b>          <samp>string</samp> <i>optional</i> <p>Performs search operation on first_name and last_name fields.</p></li>
   * <li><b><samp>$params['query']['first_name']</samp></b>    <samp>string</samp> <i>optional</i> <p>Performs search for the first_name field of the directory items.</p></li>
   * <li><b><samp>$params['query']['last_name']</samp></b>     <samp>string</samp> <i>optional</i> <p>Performs search for the last_name field of the directory items.</p></li>
   * <li><b><samp>$params['query']['user_name']</samp></b>     <samp>string</samp> <i>optional</i> <p>Performs search for the user_name field of the directory items.</p></li>
   * <li><b><samp>$params['query']['phone_number']</samp></b>  <samp>string</samp> <i>optional</i> <p>Performs search for the fields containing a phone number, like businessPhoneNumber, homePhoneNumber, mobile, pager, fax.</p></li>
   * <li><b><samp>$params['query']['order']</samp></b>         <samp>string</samp> <i>optional</i> <p>Ordering the contact results based on the requested sortBy value, order query parameter should be accompanied by sortBy query parameter.</p></li>
   * <li><b><samp>$params['query']['sort_by']</samp></b>       <samp>string</samp> <i>optional</i> <p>SortBy value is used to detect sorting the contact results based on which attribute. If order is not provided with that, ascending order is used.</p></li>
   * <li><b><samp>$params['query']['max']</samp></b>           <samp>int</samp>    <i>optional</i> <p>Maximum number of contact results that has been requested from CPaaS for this query.</p></li>
   * <li><b><samp>$params['query']['next']</samp></b>          <samp>string</samp> <i>optional</i> <p>Pointer for the next chunk of contacts, should be gathered from the previous query results.</p></li>
   * </ul>
   * </pre>
   */

  public function get_messages($params=null) {
    $message_type = $params['type'];
    $remote_address = $params['remote_address'];
    $local_address = $params['local_address'];
    $query = $params['query'];

    if ($message_type == $this->types['SMS']) {
      $options = ['query' => $query];
      $uri = 'remoteAddresses';
      if ($remote_address) {
        $uri = $uri."/".$remote_address;
      }
      if ($local_address) {
        $uri = "/".$uri."/localAddresses/".$local_address;
      }
      $uri = "/cpaas/smsmessaging/v1/".$this->client->user_id.$uri;
      $url = $this->client->_root.$uri;
      $response = $this->client->_request('GET', $url, $options);

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
   * <li><b><samp>$params['type']</samp></b>           <samp>string</samp>   <p>Type of conversation. Possible value(s) -  sms. Check conversation->types for more options.</p></li>
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

    if ($message_type == $this->types['SMS']) {
      $uri = '/remoteAddresses/'.$remote_address.'/localAddresses/'.$local_address.'/messages/'.$message_id.'/status';
      $uri = "/cpaas/smsmessaging/v1/".$this->client->user_id.$uri;
      $url = $this->client->_root.$uri;
      $response = $this->client->_request('GET', $url);

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
   * <li><b><samp>$params['type']</samp></b>                        <samp>string</samp> <p>Type of conversation. Possible value(s) - sms. Check conversation->types for more options.</p></li>
   * <li><b><samp>$params['remote_address']</samp></b>              <samp>string</samp> <p>Remote address information, information while retrieving the SMS history, basically the destination telephone number that user exchanged SMS before. E164 formatted DID number passed as a value.</p></li>
   * <li><b><samp>$params['local_address']</samp></b>               <samp>string</samp> <p>Local address information while retrieving the SMS history, basically the source telephone number that user exchanged SMS before.</p></li>
   * <li><b><samp>$params['query']</samp></b>                       <samp>array</samp>  <i>optional</i><p>To hold all query related parameter.</p></li>
   * <li><b><samp>$params['query']['max']</samp></b>                <samp>int</samp>    <i>optional</i><p>Maximum number of contact results that has been requested from CPaaS for this query.</p></li>
   * <li><b><samp>$params['query']['next']</samp></b>               <samp>string</samp> <i>optional</i><p>Filters the messages or threads having messages that are not received by the user yet.</p></li>
   * <li><b><samp>$params['query']['last_message_time']</samp></b>  <samp>string</samp> <i>optional</i><p>Filters the messages or threads having messages that are sent/received after provided Epoch time.</p></li>
   * </ul>
   * </pre>
   */

  public function get_messages_in_thread($params=null) {
    $message_type = $params['type'];
    $remote_address = $params['remote_address'];
    $local_address = $params['local_address'];
    $query = $params['query'];

    if ($message_type == $this->types['SMS']) {
      $options = ['query' => $query];
      $uri = '/remoteAddresses/'.$remote_address.'/localAddresses/'.$local_address.'/messages';
      $uri = "/cpaas/smsmessaging/v1/".$this->client->user_id.$uri;
      $url = $this->client->_root.$uri;
      $response = $this->client->_request('GET', $url, $options);

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
   * <li><b><samp>$params['type']</samp></b>           <samp>string</samp>   <p>Type of conversation. Possible value(s) -  sms. Check conversation->types for more options.</p></li>
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

    if ($message_type == $this->types['SMS']) {
      $uri = '/remoteAddresses/'.$remote_address.'/localAddresses/'.$local_address.'/messages/'.$message_id;
      $uri = "/cpaas/smsmessaging/v1/".$this->client->user_id.$uri;
      $url = $this->client->_root.$uri;
      $response = $this->client->_request('DELETE', $url);

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
   * Read all active subscriptions
   *
   * @param array $params Single parameter to hold all options.
   * <pre>
   * <ul>
   * <li><b><samp>$params['type']</samp></b>           <samp>string</samp>   <p>Type of conversation. Possible value(s) - sms. Check conversation->types for more options.</p></li>
   * </ul>
   * </pre>
   */

  public function get_subscriptions($params=null) {
    $message_type = $params['type'];

    if ($message_type == $this->types['SMS']) {
      $uri = "/cpaas/smsmessaging/v1/".$this->client->user_id."/inbound/subscriptions";
      $url = $this->client->_root.$uri;
      $response = $this->client->_request('GET', $url);

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
      $response = json_decode($response, TRUE);
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
   * <li><b><samp>$params['type']</samp></b>             <samp>string</samp>   <p>Type of conversation. Possible value(s) -  sms. Check conversation->types for more options.</p></li>
   * <li><b><samp>$params['subscription_id']</samp></b>  <samp>string</samp>   <p>Resource ID of the subscription.</p></li>
   * </ul>
   * </pre>
   */

  public function get_subscription($params=null) {
    $message_type = $params['type'];
    $subscription_id = $params['subscription_id'];

    if ($message_type == $this->types['SMS']) {
      $uri = $subscription_id;
      $uri = "/cpaas/smsmessaging/v1/".$this->client->user_id."/inbound/subscriptions/".$uri;
      $url = $this->client->_root.$uri;
      $response = $this->client->_request('GET', $url);

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
      $response = json_decode($response, TRUE);
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
   * <li><b><samp>$params['type']</samp></b>                 <samp>string</samp>   <p>Type of conversation. Possible value(s) -  sms. Check conversation->types for more options.</p></li>
   * <li><b><samp>$params['webhook_url']</samp></b>          <samp>string</samp>   <p>HTTPS URL that is present in your application server which is accessible from the public web where the notifications should be sent to. Note: Should be a <code>POST</code> endpoint.</p></li>
   * <li><b><samp>$params['destination_address']</samp></b>  <samp>string</samp>   <i>optional</i> <p>The address that incoming messages are received for this subscription. If does not exist, CPaaS uses the default assigned DID number to subscribe against. It is suggested to provide the intended E164 formatted DID number within this parameter.</p></li>
   * </ul>
   * </pre>
   */

  public function subscribe($params=null) {
    $message_type = $params['type'];
    $destination_address = $params['destination_address'];

    if ($message_type == $this->types['SMS']) {
      # create a notifyURL with webhookURL
      $notification_channel = new NotificationChannel($this->client);
      $channel = $notification_channel->create_channel($params);
      $options = array('body' => array());
      $options['body']['subscription'] = array();
      $options['body']['subscription']['callbackReference'] = ['notifyURL' => $channel['channel_id']];
      $options['body']['subscription']['clientCorrelator'] = $this->client->config->client_correlator;
      $options['body']['subscription']['destinationAddress'] = $destination_address;

      $uri = "/cpaas/smsmessaging/v1/".$this->client->user_id."/inbound/subscriptions";
      $url = $this->client->_root.$uri;
      $response = $this->client->_request("POST", $url, $options);

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
      $response = json_decode($response, TRUE);
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
   * <li><b><samp>$params['type']</samp></b>             <samp>string</samp>   <p>Type of conversation. Possible value(s) -  sms. Check conversation->types for more options.</p></li>
   * <li><b><samp>$params['subscription_id']</samp></b>  <samp>string</samp>   <p>Resource ID of the subscription.</p></li>
   * </ul>
   * </pre>
   */

  public function unsubscribe($params=null) {
    $message_type = $params['type'];
    $subscription_id = $params['subscription_id'];

    if ($message_type == $this->types['SMS']) {
      $uri = "/cpaas/smsmessaging/v1/".$this->client->user_id."/inbound/subscriptions/".$subscription_id;
      $url = $this->client->_root.$uri;
      $response = $this->client->_request('DELETE', $url);

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
      $response = json_decode($response, TRUE);
      $custom_response = array();
      $custom_response['subscription_id'] = $subscription_id;
      $custom_response['success'] = true;
      $custom_response['message'] = "Unsubscribed from ".$message_type." conversation notification";
      return $custom_response;
    }
  }
}
?>