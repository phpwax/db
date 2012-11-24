<?php
namespace Wax\Db\Backends;
use Wax\Db\Backend;
use Wax\Db\Configuration;
use Wax\Db\Exception\DBException;
use \ORM as Query;

class SQLBackend extends Backend {
  
  use Configuration;
  
  public $logger      = false;
  public $table       = false;
  public $primary_key = 'id';
  public $db          = false;
  public $settings    = false;
  
  
  public function __construct($settings) {
    $this->configure($settings);
    $this->setup_db();
  }
  
  public function setup_db() {
    $settings = $this->settings("db");
    if(isset($settings['socket']) && strlen($settings['socket'])>2) {
			$dsn="{$settings['type']}:unix_socket={$settings['socket']};dbname={$settings['name']}"; 
		} else {
			$dsn="{$settings['type']}:host={$settings['host']};port={$settings['port']};dbname={$settings['name']}";
		}
    $settings["logging"]= true;
    $settings["connection_string"]=$dsn;
    $this->table = $settings['table'];
    if(isset($settings['primary_key'])) $this->primary_key = $settings['primary_key'];
    Query::configure($settings);
  }

  
  public function db() {
    if(!isset($this->table)) throw new \Exception("Database Error", "Database queries require a table to be set");
    return Query::for_table($this->table);
  }
  
  
   
  public function all($query) {
    $query = $this->build_query($query);
    return $query->find_many();
  }
  
  public function first($query=[]) {
    $query = $this->build_query($query);
    return $query->find_one();
  }
  
  public function last_query() {
    return Query::get_last_query();
  }
  
  public function sync($options) {
    
  }
  
  public function save($options) {
    if(!isset($options['data'])) throw new \Exception("Database Error", "Database saves require a data array to be passed");
    
    if(array_key_exists($this->primary_key, $options['data'])) {
      $existing = $this->db()->find_one($options['data'][$this->primary_key]);
    } else $existing = false;
    if($existing) {
      $existing->hydrate($options['data']);
      return $existing->save();
    } else {
      $query = $this->db()->create($options['data']);
      $result = $query->save();
    }   
  }
  
  public function delete($options) {
    if(!isset($options['table'])) throw new \Exception("Database Error", "Database deletes require a table to be passed");
    
  }

  
  public function group_delete($options) {

  }
  
  
  public function group_update($options) {
    
  }
  
  /**
   * Protected internal methods
   *
   * @return Finder object
   **/
  protected function build_query($query) {
    $finder = $this->db();
    
    if(isset($query['raw'])) return $finder->rawQuery($query['raw']);
    
    /*** Select Columns ******/
    if(isset($query['select'])) {
      foreach($query['select'] as $sel) {
        $finder = $finder->select($sel);
      }
    }
    
    /*** Filters ******/
    if(isset($query['filter'])) {
      foreach($query['filter'] as $fil) {
        $finder = $finder->where(array_shift($fil),$fil);
      }
    }
    
    /*** Ordering ******/
    if(isset($query['order'])) {
      $orders = explode(",", $query['order']);
      foreach($orders as $order_frag) {
        if(stripos($order_frag, "ASC")  !==0) $finder = $finder->order_by_asc(trim(str_replace("ASC", "", $order_frag)));
        elseif(stripos($order_frag, "DESC") !==0) $finder = $finder->order_by_desc(trim(str_replace("DESC","",$order_frag)));
        else $finder->order_by_asc($order_frag);
      }
    }
    
    /*** Offest Limit ******/
    if(isset($query['limit'])) $finder = $finder->limit($query['limit']);
    if(isset($query['offset'])) $finder = $finder->offset($query['offset']);
    
    return $finder;
  }
  
  
  

}