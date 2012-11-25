<?php
namespace Wax\Db\Exception;

/**
 *
 * @package PHP-Wax
 **/
 
class SqlException extends \Exception {
    
	function __construct( $message, $code, $query_error = false ) {
	  if($query_error) $message .= " <pre>$query_error</pre>";
  	parent::__construct( $message, $code);
  }
  
}

