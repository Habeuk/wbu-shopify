<?php

namespace Stephane888\WbuShopify\ApiRest\Fulfillment;

use Stephane888\WbuShopify\ApiRest\Shopify;

class Fulfillment extends Shopify {
  use FulfillementArgument;
  /**
   *
   * @deprecated no use.
   */
  protected $location_id;
  public $order_id;
  
  function __construct($configs, $order_id) {
    $this->order_id = $order_id;
    parent::__construct($configs);
  }
  
  /**
   * Marque la commande comme traiter.
   */
  protected function Fulfill() {
    // $this->path = 'admin/api/' . $this->api_version . '/orders/' .
    // $this->order_id . '/fulfillments.json';
    $this->path = 'admin/api/' . $this->api_version . '/fulfillments.json';
    $data = [
      'fulfillment' => $this->getFulfillmentArg()
    ];
    $result = json_decode($this->PostDatas(json_encode($data)), true);
    $this->ValidResult($result);
    return $result;
  }
  
  /**
   *
   * @param string $tracking_number
   * @param string $tracking_company
   * @param array $fulfillments
   * @param boolean $notify_customer
   * @return boolean|mixed
   */
  public function PrepareFulfill($tracking_number, $tracking_company, int $fulfillment_order_id, $notify_customer = true) {
    $fulfillment['notify_customer'] = $notify_customer;
    $fulfillment['tracking_info'] = [
      'company' => $tracking_company,
      'number' => $tracking_number
    ];
    
    $fulfillment['line_items_by_fulfillment_order'][] = [
      'fulfillment_order_id' => $fulfillment_order_id,
      'fulfillment_order_line_items' => []
    ];
    $this->SetRawFulfillmentArg($fulfillment);
    return $this->Fulfill();
  }
  
  /**
   * Retourne les traitements d'une commande.
   */
  public function getFulfillmentsOrder() {
    $this->path = 'admin/api/' . $this->api_version . '/orders/' . $this->order_id . '/fulfillment_orders.json';
    $result = json_decode($this->GetDatas(), true);
    $this->ValidResult($result);
    return $result;
  }
  
  /**
   *
   * @deprecated no use
   * @param unknown $val
   */
  public function setLocationId($val) {
    $this->location_id = $val;
  }
  
}