<?php
namespace Wax\Db;
use Wax\Db\Exception\BackendSupportException;
use Wax\Behaviours\Configurable;
use Wax\Behaviours\Loggable;

/**
 * An abstract definition of a storage backend.
 * Functionality provides enough hooks to receive data and schema from model.
 *
 * @package wax/db
 * @author Ross Riley
 **/
abstract class Backend {
  
  use Configurable;
  use Loggable;
  
  public $query = [
    "filter"         => [],
    "order"          => false,
    "limit"          => false,
    "offset"         => 0,
    "raw"            => false,
  	"having"         => false,
  	"select"         => []
  ];
  
  
  public $settings   = false;
  
  public function __construct($settings=[]) {
    $this->configure($settings);
  }

  
  public function __call($method, $params) {
    if(isset($this->query[$method])) {
      if(is_array($this->query[$method])) $this->query[$method][]=$params;
      elseif(count($params)==1) $this->query[$method]=$params[0];
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
   * The following methods are the standard backend interface.
   *
   * method all()
   * Returns an array of results.
   *
   * @param Array $query required
   * @return Array
   **/
  public function all() {}
    
  /**
   * Returns an associative array of a single piece of data.
   *
   * @param Array $query required
   * @return Array
   **/
  public function first($query=[]) {}
    
  /**
   * Returns an associative array of a single piece of data.
   * A shortcut method for key based lookup.
   *
   * @param String $key required
   * @return Array
   **/
  public function find($key) {}
    
  /**
   * Returns an associative array of a single piece of data.
   * A shortcut method for key based lookup.
   *
   * @param Array $options['data'] required
   * @return mixed
   **/
  public function save($options) {}

} 