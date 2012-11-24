<?php
namespace Wax\Db\Tests;
use Wax\Db\Backends\SQLBackend;

class SQLBackendTest extends \PHPUnit_Framework_TestCase {
  
  public $settings = [];


  public function setup() {
    $this->settings["db"]['type'] = $GLOBALS['db_type'];
    $this->settings["db"]['host'] = $GLOBALS['db_host'];
    $this->settings["db"]['username'] = $GLOBALS['db_username'];
    $this->settings["db"]['password'] = $GLOBALS['db_password'];
    $this->settings["db"]['name'] = $GLOBALS['db_name'];
    $this->settings["db"]['port'] = $GLOBALS['db_port'];
    $this->settings['db']['table'] = "testing";
  }
  
  public function teardown() {
    $backend = new SQLBackend($this->settings);
    $backend->truncate();    
  }
  
  public function test_basic_query() {
    $backend = new SQLBackend($this->settings);
    $query = [
      'filter'  =>[['value','10']],
      'limit'   => 5,
      'offset'  => 1,
      'order'   => 'value ASC',
    ];
    
    $expected = "SELECT * FROM `testing` WHERE `value` = '10' ORDER BY `value` ASC LIMIT 5 OFFSET 1";
    $result = $backend->all($query);

    $this->assertEquals($expected, $backend->last_query());    
  }
  
  public function test_save_new() {
    $backend = new SQLBackend($this->settings);
    $save_data = ['data'=>['key'=>'Test1','value'=>'10'] ];
    $expected = "INSERT INTO `testing` (`key`, `value`) VALUES ('Test1', '10')";
    $res = $backend->save($save_data);
    $this->assertEquals($expected, $backend->last_query()); 
    $this->assertNotNull($res["id"]);

  }
  
  public function test_save_existing() {
    $backend = new SQLBackend($this->settings);
    $save_data = ['data'=>['id'=>55,'key'=>'Test1','value'=>'10'] ];
    $res1 = $backend->save($save_data);
    $this->assertEquals($res1["id"], 55); 
    

    $res2 = $backend->find(55);
    $this->assertEquals($res2["value"], "10"); 
    
    
    $save_data = ['data'=>['id'=>55,'key'=>'Test1','value'=>'20'] ];
    $res3 = $backend->save($save_data);

    $existing = $backend->find(55);
    $this->assertEquals("20", $existing["value"]); 
    
    
  }
  
  
  public function test_delete() {
    
  }
  
  public function test_truncate() {
    $backend = new SQLBackend($this->settings);
    $save_data = ['data'=>['id'=>55,'key'=>'Test1','value'=>'10'] ];
    $backend->save($save_data);
    
    $check_new_row = $backend->find(55);
    $this->assertNotEquals(FALSE, $check_new_row);
    
    $backend->truncate();
    $check_new_row = $backend->find(55);
    $this->assertEquals(FALSE, $check_new_row);
    
    
  }
 

  
  

}