<?php
namespace Wax\Db\Exception;

/**
 *
 * @package PHP-Wax
 * @author Ross Riley
 **/
class DBStructureException extends \Exception {
  
  const GENERAL_ERROR = 001;
  const INVALID_COLUMN = 100;
  const INVALID_VALUE = 101;
  const VALUE_TRUNCATED = 102;
  const TABLE_NOT_FOUND = 103;
  
  public $messages = [
    100=>"You tried to access a database property that doesn't exist. We tried syncing your database but it doesn't seem to have worked. Check that your models and schema definitions are setup correctly.",
    101=>"You tried to set a value that is incompatible with the schema definition",
    102=>"The data you have set is of a longer length than the schema definition allows",
    103=>"The database table that you tried to write to does not exist"
  ];
  
	function __construct( $message="Database Structure Exception", $code=self::GENERAL_ERROR, $previous = null ) {
  	parent::__construct( $message, $code, $previous);
    $this->message .= " [$code]". "\n\n".$this->messages[$code]; 
    if($previous) $this->message.="\n\n".$previous->getMessage();
  }
  
  
  public function __to_string() {
    return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
  }
  
}

