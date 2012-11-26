<?php
namespace Wax\Db\Backends;
use Wax\Db\Backend;
use Wax\Behaviours\Configurable;
use Wax\Db\Exception\DBException;
use Wax\Db\Exception\DBStructureException;
use \ORM as Query;

class SQLBackend extends Backend {
  
  use Configuration;
  
  public $logger      = false;
  public $table       = false;
  public $primary_key = 'id';
  public $db          = false;
  
  
  public function __construct($settings) {
    $this->configure($settings);
    $this->setup_db();
  }
  
  public function setup_db() {
    $settings = $this->get_setting("db");
    if(isset($settings['socket']) && strlen($settings['socket'])>2) {
			$dsn="{$settings['type']}:unix_socket={$settings['socket']};dbname={$settings['name']}"; 
		} else {
			$dsn="{$settings['type']}:host={$settings['host']};port={$settings['port']};dbname={$settings['name']}";
		}
    $settings["logging"]= true;
    $settings["connection_string"]=$dsn;
    $this->table = $settings['table'];
    if(isset($settings['primary_key'])) $this->primary_key = $settings['primary_key'];
    $settings["driver_options"] = array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET SESSION sql_mode='TRADITIONAL'");
    Query::configure($settings);
  }

  
  public function db() {
    if(!isset($this->table)) throw new \Exception("Database Error", "Database queries require a table to be set");
    return Query::for_table($this->table);
  }
  
  
   
  public function all($query=[]) {
    $query = $this->build_query($query);
    return $query->find_many();
  }
  
  public function first($query=[]) {
    $query = $this->build_query($query);
    return $query->find_one();
  }
  
  public function find($key) {
    $finder = $this->db();
    $result = $finder->find_one($key);
    if($result) return $result->as_array();
    return false;
  }
  
  public function last_query() {
    return Query::get_last_query();
  }
  
  public function sync($options) {
    
  }
  
  public function save($options) {
    if(!isset($options['data'])) throw new \InvalidArgumentException("Database saves require a data array to be passed");
    
    if(array_key_exists($this->primary_key, $options['data'])) {
      $existing = $this->db()->find_one($options['data'][$this->primary_key]);
    } else $existing = false;
    
    $success = false;
    try {
      // Two paths, if we have an existing row, update it with new data, otherwise create a new row
      if($existing) {
        foreach($options['data'] as $k=>$v) $existing->$k = $v;
        $saver = $existing;        
      } else $saver = $this->db()->create($options['data']);
      $success = $saver->save();
    } catch (\PDOException $e) {
			switch($e->getCode()) {
		    case "42S02": 
          throw new DBStructureException("Database Schema Error",DBStructureException::TABLE_NOT_FOUND,$e);
          break;

		    case "42S22":
          throw new DBStructureException("Database Schema Error",DBStructureException::INVALID_COLUMN,$e);
          break;
        
        case "22001":
          throw new DBStructureException("Database Schema Error",DBStructureException::VALUE_TRUNCATED,$e);
          break;
        
        case "HY000":
          throw new DBStructureException("Database Schema Error",DBStructureException::INVALID_VALUE,$e);
          break;
        
      }
      die($e->getCode());
      throw $e;
    }
    if($success) return $saver->as_array();
    return false;
  }
  
  public function delete($query) {
    $finder = $this->build_query($query);
    $resultset = $finder->find_many();
    $deleted_objects = [];
    if(count($resultset)) {
      foreach($resultset as $obj) $deleted_objects[] = $obj->delete();
    }
    return count($deleted_objects);
  }
  
  public function truncate() {
    $query = "TRUNCATE `".$this->table."`";
    $pdo = $this->db()->get_db();
    $pdo->exec($query);
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
    
    if(isset($query['raw'])) return $finder->raw_query($query['raw']);
    
    /*** Select Columns ******/
    if(isset($query['select'])) {
      foreach($query['select'] as $sel) {
        $finder = $finder->select($sel);
      }
    }
    
    /*** Filters ******/
    if(isset($query['filter'])) {
      foreach($query['filter'] as $fil) {
        $key = $fil[0];
        $value = $fil[1];
        
        if(isset($fil[2])) $operator = $fil[2];
        else $operator = false;
        
        if(!is_array($fil[1]) && (!$operator || $operator = '=')) $finder = $finder->where($key,$value);
        if(is_array($value)) $finder = $finder->where_in($key,$value);
        
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