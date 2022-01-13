<?php
namespace Stephane888\WbuShopify\ApiRest\Blog;

use Stephane888\WbuShopify\ApiRest\Shopify;
use Stephane888\WbuShopify\ApiRest\Metafields\MetafieldsTrait;

class Blog extends Shopify {
  use MetafieldsTrait;

  function __construct($configs)
  {
    parent::__construct($configs);
  }

  /**
   * Permet de recuperer les blogs.
   */
  public function getBlogs($id_blog = null)
  {
    $this->path = 'admin/api/' . $this->api_version . '/blogs.json';
    if (! empty($id_blog)) {
      $this->path = 'admin/api/' . $this->api_version . '/blogs/' . $id_blog . '.json';
    }
    $datas = $this->GetDatas();
    return json_decode($datas, true);
  }

  /**
   *
   * @param integer $id_blog
   * @return mixed
   */
  public function getMetafields($id_blog)
  {
    $this->path = 'admin/api/' . $this->api_version . '/blogs/' . $id_blog . '/metafields.json';
    return $this->LoadMetafiels();
  }

  /**
   *
   * @param integer $id_blog
   * @return mixed
   */
  public function getBlogsWithMetafields($id_blog = null)
  {
    $blogs = $this->getBlogs($id_blog);
    foreach ($blogs as $key => $blog) {
      $blogs[$key]['metafields'] = $this->getMetafields($blog['id']);
    }
    return $blogs;
  }
}