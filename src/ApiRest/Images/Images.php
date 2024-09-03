<?php
namespace Stephane888\WbuShopify\ApiRest\Images;

use Stephane888\WbuShopify\ApiRest\Shopify;

class Images extends Shopify {

  protected $themeId;

  function __construct($configs, $themeId)
  {
    $this->themeId = $themeId;
    parent::__construct($configs);
  }

  /**
   *
   * @param mixed $url_image
   * @param mixed $fileName
   * @param mixed $url
   * @return mixed
   */
  public function SendFile(string $imageBase64, string $fileName)
  {
    $img = [
      'asset' => [
        "key" => "assets/$fileName",
        "attachment" => $imageBase64
      ]
    ];
    $this->path = "/admin/api/" . $this->api_version . "/themes/" . $this->themeId . "/assets.json";
    $re_img = $this->PutDatas(json_encode($img));
    return json_decode($re_img);
  }

  /**
   *
   * @param string $key
   */
  public function DeleteImage(string $key)
  {
    $this->path = "/admin/api/" . $this->api_version . "/themes/" . $this->themeId . "/assets.json?asset[key]=" . $key;
    return json_decode($this->DeleteDatas());
  }
}