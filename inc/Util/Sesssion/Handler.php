<?php
Class Util_Session_Handler {
    
    
    protected $_name;
    protected $_lifetime;
    
    protected $_store;
    
    public function __construct($store, $name='PHPSESSION', $lifetime=null) {
        $this->setStore($store);
        $this->setName($name);
        
        if (null === $lifetime) {
            $lifetime = $this->getIniLifetime();   
        }
        
        $this->setLifetime($lifetime);
    }
    
    public function setStore(Util_Session_Store_Interface $store) {
        return ($this->_store = $store);
    }
    
    public function setName($name) {
        return ($this->_name = $name);   
    }
    
    public function getName() {
        return $this->_name;
    }
    
    public function getIniLifetime() {
        return (int) ini_get('session.gc_maxlifetime');
    }
    
    public function setLifetime($lt) {
        return ($this->_lifetime = $lt);
    }
    
    public function getLifetime() {
        return $this->_lifetime;
    }
    
    
    public function getExpiredTime($ts=null) {
        $ts = (null === $ts) ? time() : $ts;
        return ($ts - $this->getLifetime());
    }
    
    
    public function start() {
        session_set_save_handler(
            array($this, 'open'),
            array($this, 'close'),
            array($this, 'read'),
            array($this, 'write'),
            array($this, 'destroy'),
            array($this, 'gc')
        );
        session_name($this->getName());
        session_start();
    }
    
    
    /**
     * save_handler open method
     * @param $savePath
     * @param $name
     */
    public function open($savePath, $name) {
        return true;
    }
    
    
    /**
     * Close Sessionhandler
     * save_handler close method
     */
    public function close() {
        return true;
    }
    
    
    /**
     * Read Session data
     * save_handler read method
     * @param $id
     */
    public function read($id) {
        $session = $this->_store->get($id, $this->getName(), $this->getExpiredTime());
        return (false === $session) ? false : $session->data;
    }
    
    
    /**
     * Write Session data
     * save_handler write method
     * @param $id
     * @param $data
     */
    public function write($id, $data) {
        return $this->_store->write(
            $id, 
            $this->getName(), 
            $this->getExpiredTime(), 
            $data,
            $this->getLifetime()
        );
    }
    
    
    /**
     * Destroy Session
     * save_handler destroy method
     * @param $id
     */
    public function destroy($id) {
        return $this->_store->delete($id, $this->getName());
    }
    
    
    /**
     * Garbage collection
     * save_handler gc method
     * @param $maxlifetime
     */
    public function gc($maxlifetime) {
        return $this->_store->_deleteExpired($maxlifetime);
    }
    
    
    /**
     * Destructor
     */
    public function __destruct() {
        session_write_close();
    }
    
    
    
    
}