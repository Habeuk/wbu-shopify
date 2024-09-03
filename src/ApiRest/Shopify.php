<?php

namespace Stephane888\WbuShopify\ApiRest;

class Shopify extends CurlShopify {
  /**
   *
   * @var string
   * @see https://shopify.dev/docs/api/usage/versioning
   * @deprecated use self::$ApiVersion
   */
  protected $api_version = '2023-10';
  protected $has_error = false;
  /**
   *
   * @see https://shopify.dev/docs/api/usage/versioning
   * @var string
   */
  protected static $ApiVersion = '2023-10';
  
  /**
   * Return la premiere erreur rencontrer.
   *
   * @var string
   */
  protected $error_msg = '';
  
  function __construct($configs) {
    self::$ApiVersion = self::$ApiVersion;
    parent::__construct($configs);
  }
  
  /**
   * definit la version d'api.
   */
  public function setApiVersion($value) {
    self::$ApiVersion = $value;
  }
  
  public function get() {
    $sting = $this->GetDatas();
    if (!empty($sting))
      return json_decode($sting, true);
    else
      return $sting;
  }
  
  /**
   * Permet de determiner s'il ya une erreur;
   */
  protected function ValidResult($result) {
    if (!empty($result['errors'])) {
      $this->has_error = true;
      $this->error_msg = $this->getErrorString($result['errors']);
    }
    elseif ($this->get_http_code() < 200 || 206 < $this->get_http_code()) {
      $this->has_error = true;
      $this->error_msg = ' code erreur : ' . $this->get_http_code();
    }
  }
  
  public function checkHasError() {
    return $this->has_error;
  }
  
  public function getErrorMsg() {
    return $this->error_msg;
  }
  
  private function getErrorString($errors) {
    if (\is_array($errors)) {
      $errors = reset($errors);
      if (\is_array($errors)) {
        $errors = reset($errors);
        $this->getErrorString($errors);
      }
      return $errors;
    }
    else {
      return $errors;
    }
  }
  
  /**
   *
   * @return string
   */
  static public function getApiVersion() {
    return self::$ApiVersion;
  }
  
}
