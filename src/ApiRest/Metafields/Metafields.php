<?php
namespace Stephane888\WbuShopify\ApiRest\Metafields;

use Stephane888\WbuShopify\ApiRest\Shopify;

class Metafields extends Shopify {
  use MetafieldsTrait;

  protected $namespace = '';

  function __construct($configs, $namespace)
  {
    $this->namespace = $namespace;
    parent::__construct($configs);
  }
}