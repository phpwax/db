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
    
  }
  
  public function test_basic_query() {
    $backend = new SQLBackend($this->settings);
    $query = [
      'filter'  =>[['value','test']],
      'limit'   => 5,
      'offset'  => 1,
      'order'   => 'value ASC',
    ];
    
    $expected = "SELECT * FROM `testing` WHERE `value` = 'test' ORDER BY `value` ASC LIMIT 5 OFFSET 1";
    $result = $backend->all($query);

    $this->assertEquals($expected, $backend->last_query());    
  }
  
  public function test_save_new() {
    $backend = new SQLBackend($this->settings);
    $save_data = ['data'=>['key'=>'Test1','value'=>'10'] ];
    $expected = "INSERT INTO `testing` (`key`, `value`) VALUES ('Test1', '10')";
    $res = $backend->save($save_data);
    $this->assertEquals($expected, $backend->last_query());    


  }
  
  public function test_save_existing() {
    $backend = new SQLBackend($this->settings);
    $save_data = ['data'=>['id'=>55,'key'=>'Test1','value'=>'10'] ];
    $res = $backend->save($save_data);


  }
  
  
  public function test_delete() {
    
  }

  
  

}