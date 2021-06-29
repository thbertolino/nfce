<?php
class Registry {

   private $registry = array();
   private static $instance = array();

   public static function getInstance($connectionName = 'database') {
      if(!isset(self::$instance[$connectionName])) {
         self::$instance[$connectionName] = new Registry();
      }
      return self::$instance[$connectionName];
   }     

   public function set($key, $value) {
      if (isset($this->registry[$key])) {
         throw new Exception("There is already an entry for key " . $key);
      }
      $this->registry[$key] = $value;
   }

   public function get($key) {
      if (!isset($this->registry[$key])) {
      return false;
      //throw new Exception("There is no entry for key " . $key);
      }
      return $this->registry[$key];
   }
}