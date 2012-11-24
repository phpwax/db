<?php
namespace Wax\Db;
use Wax\Db\Exception\BackendSupportException;

/**
 * A mainly abstract definition of a db backend.
 * Functionality provides enough hooks to receive data and schema from model.
 *
 * @package wax/db
 * @author Ross Riley
 **/
class Backend {
  
  public $query = [
    "filter"         => [],
    "order"          => false,
    "limit"          => false,
    "offset"         => 0,
    "raw"            => false,
  	"is_paginated"   => false,
  	"having"         => false,
  	"select"         => []
  ];
  
  
  public $settings   = false;
  
  public function __construct($settings=[]) {
    $this->settings = $settings;
  }

  
  public function __call($method, $params) {
    if(isset($this->query[$method])) {
      if(count($params)==1) $this->query[$method]=$params[0];
      elseif(is_array($this->query[$method])) $this->query[$method][]=$params;
      else $this->query[$method]=$params;
      return $this;
    } else {
      throw new BackendSupportException;
    }
    
  }
  
  public function __get($name) {
    if(isset($this->query[$name])) return $this->query[$name];
  }
  
  /**
   * This method is protected and is the single point of query execution in the class
   * All it should look up is the connection and the query paramters.
   *
   * It returns an array of results.
   *
   * @return Array
   **/
  protected function exec() {
    
  }

} 