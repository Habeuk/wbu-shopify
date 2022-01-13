<?php
namespace Stephane888\WbuShopify\ApiRest;

class Shopify extends CurlShopify {

  protected $api_version = '2020-07';

  protected $has_error = false;

  /**
   * Return la premiere erreur rencontrer.
   *
   * @var string
   */
  protected $error_msg = '';

  function __construct($configs)
  {
    parent::__construct($configs);
  }

  /**
   * definit la version d'api.
   */
  public function setApiVersion($value)
  {
    $this->api_version = $value;
  }

  /**
   * Permet de determiner s'il ya une erreur;
   */
  protected function ValidResult($result)
  {
    if (! empty($result['errors'])) {
      $this->has_error = true;
      $this->error_msg = $this->getErrorString($result['errors']);
    }
  }

  public function checkHasError()
  {
    return $this->has_error;
  }

  public function getErrorMsg()
  {
    return $this->error_msg;
  }

  private function getErrorString($errors)
  {
    if (\is_array($errors)) {
      $errors = reset($errors);
      if (\is_array($errors)) {
        $errors = reset($errors);
        $this->getErrorString($errors);
      }
      return $errors;
    } else {
      return $errors;
    }
  }
}