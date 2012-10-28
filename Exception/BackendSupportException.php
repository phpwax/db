<?php
namespace Wax\Db\Exception;

/**
 *
 * @package PHP-Wax
 **/
 
class BackendSupportException extends \Exception {
  
  public $_code    = "Backend Support Error";
  public $_message = "The selected backend does not support the method you tried to call";
    
	function __construct($message=false, $code=false, $previous=null) {
    if(!$message) $message = $this->_message;
    if(!$code) $code = $this->_code;
  	parent::__construct($message);
  }
  
}

