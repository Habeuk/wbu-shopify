<?php
namespace Stephane888\WbuShopify\ApiRest\Prodcuts;

use Stephane888\WbuShopify\ApiRest\Shopify;
use Stephane888\WbuShopify\ApiRest\Metafields\MetafieldsTrait;

class Product extends Shopify {
  use MetafieldsTrait;

  function __construct($configs)
  {
    parent::__construct($configs);
  }

  /**
   * Permet de recuperer les produits.
   */
  public function getProducts($path = null)
  {
    if (! $path)
      $this->path = 'admin/api/' . $this->api_version . '/products.json';
    $datas = $this->GetDatas();
    return json_decode($datas, true);
  }

  /**
   * Permet de recuperer le produit.
   */
  public function getProduct($productid, $path = null)
  {
    if (! $path)
      $this->path = 'admin/api/' . $this->api_version . '/products/' . $productid . '.json';
    $datas = $this->GetDatas();
    return json_decode($datas, true);
  }

  /**
   *
   * @param integer $id_blog
   * @return mixed
   */
  public function getMetafields($productid)
  {
    $this->path = 'admin/api/' . $this->api_version . '/products/' . $productid . '/metafields.json';
    return $this->LoadMetafiels();
  }
}