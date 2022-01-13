<?php
namespace Stephane888\WbuShopify\ApiRest\Articles;

use Stephane888\WbuShopify\ApiRest\Shopify;
use Stephane888\WbuShopify\ApiRest\Metafields\MetafieldsTrait;

class Articles extends Shopify {
  use MetafieldsTrait;

  function __construct($configs)
  {
    parent::__construct($configs);
  }

  /**
   * Permet de recuperer les blogs.
   */
  public function getArticles($id_blog, $path = null)
  {
    if (! $path)
      $this->path = 'admin/api/' . $this->api_version . '/blogs/' . $id_blog . '/articles.json';
    $datas = $this->GetDatas();
    return json_decode($datas, true);
  }

  /**
   *
   * @param integer $id_blog
   * @return mixed
   */
  public function getMetafields($id_blog, $id_article)
  {
    $this->path = 'admin/api/' . $this->api_version . '/blogs/' . $id_blog . '/articles/' . $id_article . '/metafields.json';
    return $this->LoadMetafiels();
  }
}