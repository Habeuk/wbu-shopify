<?php

namespace Stephane888\WbuShopify\ApiRest\Order;

use Stephane888\WbuShopify\ApiRest\Shopify;
use Stephane888\WbuShopify\ApiRest\Metafields\MetafieldsTrait;

class Order extends Shopify {
  use MetafieldsTrait;
  
  function __construct($configs) {
    parent::__construct($configs);
  }
  
  /**
   * Permet de recuperer les commandes à patir d'une requetes entierement
   * personnalisé.
   *
   * @param String $query
   *        example : admin/api/2020-10/orders.json
   * @return mixed
   */
  public function getOrdersQuerries($query) {
    $this->path = $query;
    $datas = $this->GetDatas();
    return json_decode($datas, true);
  }
  
  /**
   * Recupere une commande.
   *
   * @param String $order_id
   *        id de la commande
   * @return mixed
   */
  public function getOrder($order_id) {
    $this->path = 'admin/api/' . $this->api_version . '/orders/' . $order_id . '.json';
    $datas = $this->GetDatas();
    return json_decode($datas, true);
  }
  
  public function getOrdersCustomer($id_customer) {
    $this->path = 'admin/api/' . $this->api_version . '/orders.json?status=any&customer_id=' . $id_customer;
    $datas = $this->GetDatas();
    return json_decode($datas, true);
  }
  
  /**
   *
   * @param string $order_id
   * @param array $newOrder
   * @return mixed
   */
  public function UpdateOrder($order_id, array $newOrder) {
    $this->path = '/admin/api/' . $this->api_version . '/orders/' . $order_id . '.json';
    return $this->PutDatas(json_encode($newOrder));
  }
  
  /**
   *
   * @param integer $order_id
   * @param object $arg
   */
  public function CancelOrder($order_id, $arg) {
    $this->path = 'admin/api/' . $this->api_version . '/orders/' . $order_id . '/cancel.json';
    return $this->PostDatas($arg);
  }
  
  public function DeleteOrder($order_id) {
    $this->path = 'admin/api/' . $this->api_version . '/orders/' . $order_id . '.json';
    $datas = $this->DeleteDatas();
    return $datas;
    // return json_decode($datas, true);
  }
  
}