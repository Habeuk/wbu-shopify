<?php

namespace Stephane888\WbuShopify\Exception;

use LogicException;

class WbuShopifyException extends LogicException {
	function __construct($message = null, $code = null, $previous = null){
		parent::__construct( $message, $code, $previous );
	}
}