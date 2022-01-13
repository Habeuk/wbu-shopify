<?php
namespace Stephane888\WbuShopify\ApiRest\WebHook\Traits;

trait Order {

  /**
   * Get order.
   */
  public function getOrder()
  {
    $this->getEntity();
    if (! empty($this->entity) && isset($this->entity['order_number'])) {
      $this->order = $this->entity;
      $this->id_order = $this->entity['id'];
    }
    return $this->order;
  }

  /**
   * Retournées les skus d'une commande.
   *
   * @return array
   */
  public function getSkus()
  {
    $results = [];
    if (! empty($this->entity['line_items'])) {
      foreach ($this->entity['line_items'] as $line) {
        $results[] = [
          'sku' => $line['sku'],
          'qte' => $line['quantity']
        ];
      }
    }
    return $results;
  }
}