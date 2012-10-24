<?php
namespace Wax\Db\Tests;

class DBAdapterTest extends \PHPUnit_Framework_TestCase {


  public function testInit() {
    $adapter = new MysqlAdapter();
    $this->assertEquals('foo', "foo");
    
  }

}