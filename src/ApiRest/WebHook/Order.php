<?php
namespace Stephane888\WbuShopify\ApiRest\WebHook;

use Stephane888\WbuShopify\ApiRest\WebHook\Traits\Order as TraitOrder;

class Order extends WebHook {
  use TraitOrder;

  protected $order;

  protected $id_order;

  function __construct($SHOPIFY_APP_SECRET = null, $entity = null)
  {
    parent::__construct($SHOPIFY_APP_SECRET, $entity);
  }
}