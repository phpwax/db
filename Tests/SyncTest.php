<?php
namespace Wax\Db\Tests;
use Wax\Db\Sync\MysqlSync;
use Wax\Db\Backends\SQLBackend;

class SyncTest extends \PHPUnit_Framework_TestCase {
  
  public $settings = [];
  
  public $schema = [
    'id'        =>['maxlenth'=>60,'data_type'=>'string','primary'=>true,'col_name'=>'id'],
    'testchar'  =>['maxlenth'=>255,'data_type'=>'string','col_name'=>'testchar'],
    'testtext'  =>['data_type'=>'text','col_name'=>'testtext'],
    'testfloat' =>['data_type'=>'float','col_name'=>'testfloat',"null"=>false,"default"=>0.5],
    'testdate'  =>['data_type'=>'date_and_time','col_name'=>'testdate']
  ];


  public function setup() {
    $this->settings["db"]['type'] = $GLOBALS['db_type'];
    $this->settings["db"]['host'] = $GLOBALS['db_host'];
    $this->settings["db"]['username'] = $GLOBALS['db_username'];
    $this->settings["db"]['password'] = $GLOBALS['db_password'];
    $this->settings["db"]['name'] = $GLOBALS['db_name'];
    $this->settings["db"]['port'] = $GLOBALS['db_port'];
    $backend = new SQLBackend($this->settings);
    $this->settings["driver"] = $backend::$db;
  }
  
  public function teardown() {
    $engine = new MysqlSync($this->settings);
    $engine->drop_table("synctest");
    
  }
  
  public function test_create_table() {
    $engine = new MysqlSync($this->settings);
    $engine->create_table("synctest",$this->schema);
    $tables = $engine->view_tables();
    $this->assertContains("synctest", $tables);
  }
  
  public function test_delete_table() {
    $engine = new MysqlSync($this->settings);
    $engine->drop_table("synctest");
    $tables = $engine->view_tables();
    $this->assertNotContains("synctest", $tables);
  }
  
  public function test_logging() {
    $engine = new MysqlSync($this->settings);
    $logger = new MockLogger;
    $engine->set_logger($logger);
    $engine->create_table("synctest",$this->schema);
    $this->assertEquals(2, count($logger->messages, 1));
    $engine->drop_table("synctest");
    $this->assertEquals(3, count($logger->messages, 1));
  }
  
  public function test_sync_table() {
    $engine = new MysqlSync($this->settings);
    $engine->sync_table(["table"=>"synctest", "schema"=>$this->schema]);
    $tables = $engine->view_tables();
    $this->assertContains("synctest", $tables);
    
    $columns = $engine->view_columns("synctest");
    $this->assertEquals(1, count($columns));
    
  }
  
  public function test_sync_columns() {
    $engine = new MysqlSync($this->settings);
    $engine->sync_table(["table"=>"synctest", "schema"=>$this->schema]);
    
    $engine->sync_columns(["table"=>"synctest", "schema"=>$this->schema]);
    $columns = $engine->view_columns("synctest");
    $this->assertEquals(5, count($columns));
    $this->assertEquals('varchar', $columns[0]["DATA_TYPE"]);
    $this->assertEquals('PRI', $columns[0]["COLUMN_KEY"]);
    $this->assertEquals(255, $columns[0]["CHARACTER_MAXIMUM_LENGTH"]);
    
  }
  
  public function test_column_alterations() {
    $engine = new MysqlSync($this->settings);
    $engine->sync_table(["table"=>"synctest", "schema"=>$this->schema]);
    $engine->sync_columns(["table"=>"synctest", "schema"=>$this->schema]);
    
    // First run the test on the standard schema
    $columns = $engine->view_columns("synctest");
    foreach($columns as $col) if($col["COLUMN_NAME"]=="testfloat") $test = $col;
    $this->assertEquals("float", $test["DATA_TYPE"]);
    $this->assertEquals("NO", $test["IS_NULLABLE"]);
    $this->assertEquals(0.5, $test["COLUMN_DEFAULT"]);
    
    // Now change the schema and re-test
    $this->schema['testfloat']['null']=true;
    $this->schema['testfloat']['default']=0.9;
    $engine->sync_columns(["table"=>"synctest", "schema"=>$this->schema]);
    $columns = $engine->view_columns("synctest");
    foreach($columns as $col) if($col["COLUMN_NAME"]=="testfloat") $new_test = $col;
    
    $this->assertEquals("YES", $new_test["IS_NULLABLE"]);
    $this->assertEquals(0.9, $new_test["COLUMN_DEFAULT"]);
    
  }
  
  public function test_column_type_changes() {
    $engine = new MysqlSync($this->settings);
    $engine->sync_table(["table"=>"synctest", "schema"=>$this->schema]);
    $this->schema['testtext']['data_type']="string";
    
    $engine->sync_columns(["table"=>"synctest", "schema"=>$this->schema]);
    $columns = $engine->view_columns("synctest");
    foreach($columns as $col) if($col["COLUMN_NAME"]=="testtext") $test = $col;
    $this->assertEquals('varchar', $test["DATA_TYPE"]);
  }
  
  
}