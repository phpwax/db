<?php
namespace Wax\Db;

trait Configuration{
  protected static $_defaults;
  protected $_settings;
  
  public function settings($key){
    if(isset($this->_settings[$key]) && is_callable($this->_settings[$key])) return call_user_func($this->_settings[$key]);
    if(isset($this->_settings[$key])) return $this->_settings[$key];
    
    if(is_callable(self::$_defaults[$key])) return call_user_func(self::$_defaults[$key]);
    if(isset(self::$_defaults[$key])) return self::$_defaults[$key];
    return false;
  }
  
  static public function defaults($defaults) {
    if(!is_array($defaults) && !$defaults instanceof ArrayAccess) {
      throw new \Exception("Supplied default settings could not be used. Array or Array Object required");
    }
    self::$_defaults = $defaults;
  }
  
  public function configure($settings) {
    if(!is_array($settings) && !$settings instanceof ArrayAccess) {
      throw new \Exception("Supplied settings could not be used. Array or Array Object required");
    }
    $this->_settings = $settings;
  }

}