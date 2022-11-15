<?php

namespace Stephane888\WbuShopify\Exception;

use Stephane888\Debug\ExceptionDebug;

class WbuShopifyException extends ExceptionDebug {
  
  function __construct($message = null, $code = null, $previous = null) {
    parent::__construct($message, null, $code, $previous);
  }
  
}