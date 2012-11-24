<?php
namespace Wax\Db\Exception;
/**
 *
 * @package PHP-Wax
 * @author Ross Riley
 **/
class DBException extends \Exception {
  
  public $help = "<p>The application couldn't initialise a database connection using the following settings:</p>";
  
	function __construct( $message, $code, $db_settings = array() ) {
	  $this->help .= "<p>Check that these settings are correctly configured and try again</p>";
  	parent::__construct( $message. $this->help, $code);
  }
}



