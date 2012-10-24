<?php
namespace Wax\Db\Tests;
use Wax\Db\MysqlAdapter;

class DBAdapterTest extends \PHPUnit_Framework_TestCase {


  public function testInit() {
    $adapter = new MysqlAdapter();
    $this->assertEquals('foo', "foo");
    
  }

}