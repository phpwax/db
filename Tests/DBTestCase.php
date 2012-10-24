<?php

class DBTestCase extends \PHPUnit_Framework_TestCase {


  public function testInit() {
    $adapter = new MysqlAdapter();
    $this->assertEquals('foo', $constraint->property1);
    
  }

}