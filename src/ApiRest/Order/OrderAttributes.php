<?php

namespace Stephane888\WbuShopify\ApiRest\Order;

/**
 * Class permettant de manipulers les attributs et autre données de l'object
 * commande.
 *
 * @author stephane
 *        
 */
class OrderAttributes {
  private $order = null;
  
  /**
   *
   * @param array $order
   */
  function __construct($order) {
    $this->order = $order;
  }
  
  /**
   *
   * @param string $key
   */
  public function getAttributes($key) {
    $order = $this->order;
    foreach ($order['note_attributes'] as $val) {
      if (isset($val['name']) && $val['name'] == $key) {
        return $val['value'];
      }
    }
    return false;
  }
  
}