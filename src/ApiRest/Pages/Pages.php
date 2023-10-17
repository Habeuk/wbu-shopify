<?php

namespace Stephane888\WbuShopify\ApiRest\Blog;

use Stephane888\WbuShopify\ApiRest\Shopify;
use Stephane888\WbuShopify\ApiRest\Metafields\MetafieldsTrait;

class Blog extends Shopify {
  use MetafieldsTrait;

  function __construct($configs) {
    parent::__construct($configs);
  }

  /**
   * Permet de recuperer les pages.
   */
  public function getPages($id_page = null) {
    $this->path = 'admin/api/' . $this->api_version . '/pages.json';
    if (!empty($id_page)) {
      $this->path = 'admin/api/' . $this->api_version . '/pages/' . $id_page . '.json';
    }
    $datas = $this->GetDatas();
    return json_decode($datas, true);
  }

  /**
   *
   * @param integer $id_page
   * @return mixed
   */
  public function getMetafields($id_page) {
    $this->path = 'admin/api/' . $this->api_version . '/pages/' . $id_page . '/metafields.json';
    return $this->LoadMetafiels();
  }

  /**
   *
   * @param integer $id_page
   * @return mixed
   */
  public function getPagesWithMetafields($id_page = null) {
    $pages = $this->getPages($id_page);
    foreach ($pages as $key => $page) {
      $pages[$key]['metafields'] = $this->getMetafields($page['id']);
    }
    return $pages;
  }
}
