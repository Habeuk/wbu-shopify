<?php
namespace Stephane888\WbuShopify\ApiRest\Page;

use Stephane888\WbuShopify\ApiRest\Shopify;
use Stephane888\WbuShopify\ApiRest\Metafields\MetafieldsTrait;

class Page extends Shopify {
  use MetafieldsTrait;

  function __construct($configs)
  {
    parent::__construct($configs);
  }

  /**
   * Permet de recuperer les pates.
   */
  public function getPage($id_page = null)
  {
    $this->path = 'admin/api/' . $this->api_version . '/pages.json';
    if (! empty($id_blog)) {
      $this->path = 'admin/api/' . $this->api_version . '/blogs/' . $id_page . '.json';
    }
    $datas = $this->GetDatas();
    return json_decode($datas, true);
  }

  /**
   * Create a new page
   * @param array $page
   */
  public function addPage($page) {
    $data = [
      "page" => $page
      ];
    $this->path = 'admin/api/' . $this->api_version . '/pages.json';
    $result = json_decode($this->PostDatas(json_encode($data)), true);

    return $result;
  }
}