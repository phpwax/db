<?php
namespace Wax\DB\Sync;
use Wax\Behaviours\Configurable;
use Wax\Behaviours\Loggable;

class MysqlSync {
 
  use Configurable;
  use Loggable;
  
  public static $_db = false;
  
	public $data_types = [
    'string' => "varchar",
    'text' => "longtext",
    'date' => "date",
    'time' => 'time',
    'date_and_time' => "datetime",
    'integer' => "int",
    'decimal' => "decimal",
    'float' => "float"
  ];    
 
  
  public function __construct($settings) {
    self::defaults([
      'default_db_engine'=>"MyISAM",
      'default_db_charset'=>"utf8",
      'default_db_collate'=>"utf8_unicode_ci"
    ]);
    $this->configure($settings);
    if(!self::$_db && $db = $this->get_setting("driver")) $this->set_db($db);
    elseif(!self::$_db) throw new \Exception(__NAMESPACE__. __CLASS__." requires a database connection.");
  }
  
  static public function set_db($db) {
    self::$_db = $db;
  }
  
  protected function db() {
    return self::$_db;
  }

  
  /**
   * The main engine of the class parameters
   * @param required $options['table']
   * @param required $options['schema']
   *
   **/
  
  public function sync_table($options = []) {
    $schema = $options["schema"];
    $table  = $options["table"];
    
    $tables = $this->view_tables();
    $exists = false;
    foreach($tables as $existing_table) {
      if($table == $existing_table) $exists=true;
    }
    if(!$exists) {
      $this->create_table($table, $schema);
    }
    
    
    $this->log("Table {$options['table']} is synchronised");
  }
  
  /**
   * The main engine of the class parameters
   * @param required $options['table']
   * @param required $options['schema']
   *
   **/
  
  public function sync_columns($options=[]) {
    $schema = $options["schema"];
    $table  = $options["table"];
    $db_cols = $this->view_columns($table);
    
    
    foreach($schema as $field) {
      $col_exists = false;
      $col_changed = false;
      foreach($db_cols as $key=>$col) {
        if($col["COLUMN_NAME"]==$field["col_name"]) {
          $col_exists = true;
          if(isset($field["default"]) && $col["COLUMN_DEFAULT"] != $field["default"]) $col_changed = "default";
          if(isset($field["null"]) && $col["IS_NULLABLE"]=="NO" && $field["null"]==true) $col_changed = "now null";
          if(isset($field["null"]) && $col["IS_NULLABLE"]=="YES" && $field["null"]==false) $col_changed = "now not null";
        }
      }
      if($col_exists==false){
        $this->add_column($field, $table);
      }
      if($col_changed) { 
        $this->alter_column($field, $table);
      }
    }

  }


  public function view_tables() {
    return $this->db()->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);
  }

  public function view_columns($table) {
    $db = $this->get_setting("db")["name"];
    $sql = "SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA ='{$db}' AND TABLE_NAME = '{$table}'";
    return $this->db()->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
  }

  public function create_table($table, $schema) {
    $primary = false;
    foreach($schema as $field) {
      if(isset($field["primary"]) && $field["primary"] ===true) $primary = $field;
    }
    
    $sql = "CREATE TABLE IF NOT EXISTS `{$table}` (";
    $sql .= $this->column_sql($primary);
    
    $engine = $this->get_setting("default_db_engine");
    $charset = $this->get_setting("default_db_charset");
    $collate = $this->get_setting("default_db_collate");
    
    $sql.=") ENGINE=".$engine." DEFAULT CHARSET=".$charset." COLLATE=".$collate;
    $this->db()->exec($sql);
    $this->log("Created table {$table}");
  }

  public function drop_table($table) {
    $sql = "DROP TABLE IF EXISTS `$table`";
    $this->db()->exec($sql);
    $this->log("Removed table ".$table);
  }

  public function column_sql($field) {
    $sql = "`{$field['col_name']}`";
    if(!isset($field["data_type"])) $type = "string";
    else $type = $field["data_type"];
    $sql.=" ".$this->data_types[$type];

    if($type == "string" && !isset($field["maxlength"])) $sql.= "(255) ";
    elseif(isset($field["maxlength"])) $sql.= "(".$field['maxlength'].") ";
    
    if(isset($field["null"]) && $field["null"] !==false) $sql.=" NULL";
    else $sql.=" NOT NULL";
    
    if(isset($field["default"]) || isset($field["database_default"])) {
      $sql.= " DEFAULT ".( isset($field["database_default"]) ? $field["database_default"]: "'".$field["default"]."'");
    }
    if(isset($field["auto"])) $sql.= " AUTO_INCREMENT";
    if(isset($field["primary"])) $sql.=" PRIMARY KEY";
    return $sql;
  }

  public function add_column($field, $table) {
    if(!isset($field["col_name"])) return false;
    $sql = "ALTER TABLE `$table` ADD ";
    $sql.= $this->column_sql($field);
    $this->db()->exec($sql);
    $this->log("Added column {$field['col_name']} to {$table}");    
  }

  public function alter_column($field, $table) {
    if(!isset($field["col_name"])) return false;
    $sql = "ALTER TABLE `$table` MODIFY ";
    $sql.= $this->column_sql($field);
    $this->db()->exec($sql);
    $this->log("Updated column {$field['col_name']} in {$table}");    
  }
  
  
}