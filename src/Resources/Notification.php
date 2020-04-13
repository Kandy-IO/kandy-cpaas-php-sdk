<?php

namespace CpaasSdk\Resources;

use CpaasSdk\Api;

/**
  * CPaaS notification helper methods.
  */
class Notification {

  /**
   * @ignore
   */
  public $client = null;

  /**
   * @ignore
   */
  public function __construct(Api $client) {
    $this->client = $client;
  }

  /**
    * Parse inbound sms notification received in webhook. It parses the notification and returns simplified version of the response.
    *
    * @param array $notification JSON received in the subscription webhook.
  */

  public function parse($notification=null) {
    $toplevel_key = array_keys($notification)[0];
    switch($toplevel_key) {
      case 'smsSubscriptionCancellationNotification':
        $custom_response = array();
        $custom_response['subscription_id'] = $this->client->id_from_url($notification[$toplevel_key]['link'][0]['href']);
        $custom_response['notification_id'] =  $notification[$toplevel_key]['id'];
        $custom_response['notification_datetime'] = $notification[$toplevel_key]['dateTime'];
        $custom_response['type'] = 'subscriptionCancel';
        return $custom_response;
        break;
      case 'smsEventNotification':
        $custom_response = array();
        $custom_response['subscription_id'] = $this->client->id_from_url($notification[$toplevel_key]['link'][0]['href']);
        $custom_response['notification_id'] =  $notification[$toplevel_key]['id'];
        $custom_response['notification_datetime'] = $notification[$toplevel_key]['dateTime'];
        $custom_response['type'] = 'event';
        $custom_response['event_details'] = array();
        $custom_response['event_details']['event_description'] = $notification[$toplevel_key]['eventDescription'];
        $custom_response['event_details']['type'] = $notification[$toplevel_key]['eventType'];
        return $custom_response;
        break;
      case 'outboundSMSMessageNotification':
        $outboundSMSMessage = $notification[$toplevel_key]['outboundSMSMessage'];
        $custom_response = array();
        $custom_response = $outboundSMSMessage;
        $custom_response['notification_id'] = $notification[$toplevel_key]['id'];
        $custom_response['notification_date_time'] = $notification[$toplevel_key]['dateTime'];
        $custom_response['type'] = 'outbound';
        return $custom_response;
        break;
      case 'inboundSMSMessageNotification':
        $inboundSMSMessage = $notification[$toplevel_key]['inboundSMSMessage'];
        $custom_response = array();
        $custom_response = $inboundSMSMessage;
        $custom_response['notification_id'] = $notification[$toplevel_key]['id'];
        $custom_response['notification_date_time'] = $notification[$toplevel_key]['dateTime'];
        $custom_response['type'] = 'inbound';
        return $custom_response;
        break;
    }
  }
}
?>