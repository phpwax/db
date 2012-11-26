<?php
namespace Wax\Db\Tests;

class MockLogger {

  public $messages = array();
  
  public function log($message, $level) {
    $this->messages[$level][]=$message;
  }
  

}