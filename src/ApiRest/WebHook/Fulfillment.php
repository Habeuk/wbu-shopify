<?php
namespace Stephane888\WbuShopify\ApiRest\WebHook;

/**
 * Cette classe retourne un object commande.
 *
 * @author stephane
 *
 */
use Stephane888\WbuShopify\ApiRest\WebHook\Traits\Order as TraitOrder;

class Fulfillment extends WebHook {
  use TraitOrder;

  protected $fulfillment;

  protected $order;

  protected $id_order;

  function __construct($SHOPIFY_APP_SECRET = null, $entity = null)
  {
    parent::__construct($SHOPIFY_APP_SECRET, $entity);
  }

  /**
   * Get order
   */
  public function getFulfillment()
  {
    $this->getEntity();
    if (! empty($this->entity) && isset($this->entity['order_number'])) {
      $this->order = $this->entity;
      $this->id_order = $this->entity['id'];
      $this->fulfillment = $this->entity['fulfillments'];
    }
    return $this->fulfillment;
  }

  /**
   * Retournées les skus d'une commande
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