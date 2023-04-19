<?php

namespace Stephane888\WbuShopify\ApiRest\Fulfillment;

use Stephane888\Debug\ExceptionDebug;

/**
 * Permet de contruire un object valide pour traiter une commande.
 * le format pour la version 2023-04
 * {
 * "fulfillment": {
 * "message": "The package was shipped this morning.",
 * "notify_customer": false,
 * "tracking_info": {
 * "number": 1562678,
 * "url": "https://www.my-shipping-company.com",
 * "company": "my-shipping-company"
 * },
 * "line_items_by_fulfillment_order": [{
 * "fulfillment_order_id": 1046000784,
 * "fulfillment_order_line_items": [{
 * "id": 1058737493,
 * "quantity": 1
 * }]
 * }]
 * }
 * }
 *
 * @author stephane
 *        
 */
trait FulfillementArgument {
  /**
   *
   * @var array
   */
  private $fulfillment = [];
  
  public function SetRawFulfillmentArg(array $fulfillment) {
    $this->fulfillment = $fulfillment;
    $this->validDataFulfillment();
  }
  
  protected function validDataFulfillment() {
    if (!empty($this->fulfillment)) {
      if (empty($this->fulfillment['line_items_by_fulfillment_order']))
        throw ExceptionDebug::exception('line_items_by_fulfillment_order est requis', $this->fulfillment);
      else {
        foreach ($this->fulfillment['line_items_by_fulfillment_order'] as $line_items) {
          if (empty($line_items['fulfillment_order_id']))
            throw ExceptionDebug::exception('fulfillment_order_id est requis', $this->fulfillment);
          // si elle est definie elle doit respecter les conditions.
          if (!empty($line_items['fulfillment_order_line_items'])) {
            foreach ($line_items['fulfillment_order_line_items'] as $item) {
              if (empty($item['id']) || empty($item['quantity']))
                throw ExceptionDebug::exception(' Les proprietes "id et quantity" sont requise ', $this->fulfillment);
            }
          }
        }
      }
    }
    return true;
  }
  
  /**
   * Recupere la arguments valides.
   *
   * @return array
   */
  protected function getFulfillmentArg() {
    if (empty($this->fulfillment))
      throw ExceptionDebug::exception(' Les arguments pour le traitement de commande ne sont pas definit ', $this->fulfillment);
    return $this->fulfillment;
  }
  
}