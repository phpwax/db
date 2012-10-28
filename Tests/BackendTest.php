<?php
namespace Wax\Db\Tests;
use Wax\Db\Backend;

class BackendTest extends \PHPUnit_Framework_TestCase {


  public function setup() {
    
  }
  
  public function teardown() {
    
  }
  
  public function test_initialisation() {
    $backend = new Backend;
  }
  
  public function test_writes_and_reads() {
    $backend = new Backend;
    $backend->limit(5);
    $this->assertEquals($backend->limit, 5);
    $backend->order("test");
    $this->assertEquals($backend->order, "test");
    $backend->offset(5);
    $this->assertEquals($backend->offset, 5);
    $backend->filter("test", ['opt'=>1,'opt'=>2]);
    $this->assertEquals(count($backend->filter), 1);
    $backend->raw("raw_query");
    $this->assertEquals($backend->raw, "raw_query");
    $backend->select("a,b,c");
    $this->assertEquals($backend->select, "a,b,c");
  }

}