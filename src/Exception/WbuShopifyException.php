<?php

namespace Stephane888\WbuShopify\Exception;

use LogicException;

class WbuShopifyException extends LogicException {
  protected array $errors = [];
  
  function __construct($message = null, $code = null, $previous = null) {
    parent::__construct($message, $code, $previous);
  }
  
  public function setErrors(array $errors) {
    $this->errors = $errors;
  }
  
  public function getErrors() {
    return $this->errors;
  }
  
}