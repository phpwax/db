<?php
namespace Wax\Db\Tests;
use Wax\Db\Sync\MysqlSync;

class SyncTest extends \PHPUnit_Framework_TestCase {
  
  public $settings = [];
  
  public $schema_1 = [
    'id'        =>['maxlenth'=>60,'data_type'=>'string','primary'=>true,'col_name'=>'id'],
    'testchar'  =>['maxlenth'=>255,'data_type'=>'string','col_name'=>'testchar'],
    'testtext'  =>['data_type'=>'text','col_name'=>'testtext'],
    'testfloat' =>['data_type'=>'float','col_name'=>'testfloat'],
    'testdate'  =>['data_type'=>'date_and_time','col_name'=>'testdate']
  ];


  public function setup() {
    $this->settings["db"]['type'] = $GLOBALS['db_type'];
    $this->settings["db"]['host'] = $GLOBALS['db_host'];
    $this->settings["db"]['username'] = $GLOBALS['db_username'];
    $this->settings["db"]['password'] = $GLOBALS['db_password'];
    $this->settings["db"]['name'] = $GLOBALS['db_name'];
    $this->settings["db"]['port'] = $GLOBALS['db_port'];
  }
  
  public function teardown() {

  }
  
  public function test_create_table() {
    $engine = new MysqlSync($this->settings);
    $engine->create_table("synctest",$this->schema_1);
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
    $engine->create_table("synctest",$this->schema_1);
    $this->assertEquals(2, count($logger->messages, 1));
    $engine->drop_table("synctest");
    $this->assertEquals(3, count($logger->messages, 1));
    
  }
  
}