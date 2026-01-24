<?php

namespace Stephane888\WbuShopify\ApiRest\Articles;

use Stephane888\WbuShopify\ApiRest\Shopify;
use Stephane888\WbuShopify\ApiRest\Metafields\MetafieldsTrait;

class Articles extends Shopify {
  use MetafieldsTrait;

  function __construct($configs) {
    parent::__construct($configs);
  }

  /**
   * Permet de recuperer les blogs.
   * exemple : $query = "?published_status=published"
   *
   * @param int $id_blog
   * @param string $path
   * @param string $query
   * @return mixed
   */
  public function getArticles($id_blog, $path = null, $query = "?limit=100") {
    if (!$path)
      $this->path = 'admin/api/' . self::$ApiVersion . '/blogs/' . $id_blog . '/articles.json' . $query;
    $datas = $this->GetDatas();
    return json_decode($datas, true);
  }

  /**
   *
   * @param integer $id_blog
   * @return mixed
   */
  public function getMetafields($id_blog, $id_article, $query = "?limit=100") {
    $this->path = 'admin/api/' . self::$ApiVersion . '/blogs/' . $id_blog . '/articles/' . $id_article . '/metafields.json' . $query;
    return $this->LoadMetafiels();
  }
}
