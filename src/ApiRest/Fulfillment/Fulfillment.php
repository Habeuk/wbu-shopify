<?php
namespace Stephane888\WbuShopify\ApiRest\Fulfillment;

use Stephane888\WbuShopify\ApiRest\Shopify;

class Fulfillment extends Shopify {

  protected $location_id;

  public $order_id;

  function __construct($configs)
  {
    parent::__construct($configs);
  }

  /**
   * Marque la commande comme traiter.
   */
  protected function Fulfill($dataFulfill)
  {
    if ($this->validDataFulfillment($dataFulfill)) {
      $this->path = 'admin/api/' . $this->api_version . '/orders/' . $this->order_id . '/fulfillments.json';
      $data = [
        'fulfillment' => $dataFulfill
      ];
      $result = json_decode($this->PostDatas(json_encode($data)), true);
      $this->ValidResult($result);
      return $result;
    }
    return false;
  }

  public function PrepareFulfill($tracking_number, $tracking_company, $notify_customer = true)
  {
    $dataFulfill = [
      'notify_customer' => $notify_customer,
      'tracking_company' => $tracking_company,
      'tracking_number' => $tracking_number,
      'location_id' => $this->location_id
    ];
    return $this->Fulfill($dataFulfill);
  }

  protected function validDataFulfillment($dataFulfill)
  {
    $this->has_error = true;
    if (empty($dataFulfill['location_id'])) {
      $this->error_msg = 'La location du magasin doit etre definit';
      return false;
    }
    if (empty($this->order_id)) {
      $this->error_msg = " L'identifiant de la commande doit etre definit ";
      return false;
    }
    $this->has_error = false;
    return true;
  }

  public function setLocationId($val)
  {
    $this->location_id = $val;
  }
}