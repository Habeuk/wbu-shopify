<?php

namespace Stephane888\WbuShopify\ApiRest\Metafields;

use Stephane888\WbuShopify\ApiRest\Authentification\IntegrationToken;

class MetafieldsToken extends IntegrationToken {
  use MetafieldsTrait;
  protected $namespace = 'habeuk';
  
}