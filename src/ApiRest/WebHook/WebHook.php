<?php
namespace Stephane888\WbuShopify\ApiRest\WebHook;

class WebHook {

  protected $SHOPIFY_APP_SECRET;

  /**
   * contenu dans le header
   *
   * @var string
   */
  protected $hmac_header;

  /**
   * contenu dans le header
   *
   * @var string
   */
  protected $ShopifyTopic;

  /**
   * contenu dans le header
   *
   * @var string
   */
  protected $ShopDomain;

  protected $entity;

  function __construct($SHOPIFY_APP_SECRET = null, $entity = null)
  {
    $this->SHOPIFY_APP_SECRET = $SHOPIFY_APP_SECRET;
    $this->entity = $entity;
  }

  public function setEntity($entity)
  {
    $this->entity = $entity;
  }

  /**
   *
   * @param string $SHOPIFY_APP_SECRET
   */
  public function setShopifyAppSecret($SHOPIFY_APP_SECRET)
  {
    $this->SHOPIFY_APP_SECRET = $SHOPIFY_APP_SECRET;
  }

  public function getServerInformation()
  {
    $this->hmac_header = (isset($_SERVER['HTTP_X_SHOPIFY_HMAC_SHA256'])) ? $_SERVER['HTTP_X_SHOPIFY_HMAC_SHA256'] : null;
    $this->ShopifyTopic = (isset($_SERVER['HTTP_X-Shopify-Topic'])) ? $_SERVER['HTTP_X-Shopify-Topic'] : null;
    $this->ShopDomain = (isset($_SERVER['HTTP_X-Shopify-Shop-Domain'])) ? $_SERVER['HTTP_X-Shopify-Shop-Domain'] : null;
  }

  /**
   *
   * @return boolean
   */
  public function getEntity()
  {
    $this->entity = file_get_contents('php://input');
    $this->getServerInformation();

    if (! empty($this->entity) && $this->verify_webhook_key()) {
      $this->entity = json_decode($this->entity, true);
      return true;
    } else {
      $this->entity = [
        'status' => 0,
        'verify_webhook_key' => ($this->verify_webhook_key()) ? 1 : 0,
        'SHOPIFY_APP_SECRET' => $this->SHOPIFY_APP_SECRET,
        'hmac_header' => $this->hmac_header,
        'entity' => $this->entity
      ];
    }
    return false;
  }

  /**
   *
   * @return boolean
   */
  private function verify_webhook_key()
  {
    $calculated_hmac = base64_encode(hash_hmac('sha256', $this->entity, $this->SHOPIFY_APP_SECRET, true));
    if (! empty($this->hmac_header)) {
      return hash_equals($this->hmac_header, $calculated_hmac);
    } else {
      return false;
    }
  }
}