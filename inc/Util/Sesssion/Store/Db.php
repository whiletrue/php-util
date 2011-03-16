<?php 
require_once "Interface.php";

Class Util_Session_Store_Db implements Util_Session_Store_Interface {

    protected $_db;
    
    public function __construct(PDO $db) {
        $this->setDb($db);        
    }
    
    public function setDb(PDO $db) {
        return ($this->_db = $db);
    }
    
    public function delete($id, $name) {
        $q = "DELETE FROM Sessions "
           . "WHERE id = :id "
           . "AND name = :name ";
        $stmt = $this->_db->prepare($q);
        return $stmt->execute(array(
            ':id'   => $id,
            ':name' => $name
        ));
    }
    
    
    public function deleteExpired($maxlifetime, $now=null) {
        $now = (null === $now) ? time() : $now;
        $q = "DELETE FROM Sessions "
        . "WHERE (modified + :maxlifetime) < :now";
        $stmt = $this->_db->prepare($q);
        return $stmt->execute(array(
            ':maxlifetime'  => $maxlifetime,
            ':now'           => $now
        ));
    }
    
    
    public function write($id, $name, $expiry, $data, $lifetime) {
        $session = $this->get($id, $name, $expiry);
        $params = array(
            'id'    => $id,
            'data'  => $data
        );
        if (!$session) {
            $params['lifetime'] = $lifetime;
            $params['name']     = $name;
            return $this->_insert($id, $params);
        } else {
            $params['lifetime'] = $session->lifetime;
            return $this->_update($id, $params);           
        }
        
        return false;    
    }
    
    
    public function get($id, $name, $expiry) {
        $q = "SELECT * FROM Sessions "
           . "WHERE id = :id "
           . "AND name= :name " 
           . "AND (modified + lifetime) > :expiry";
           
        $stmt = $this->_db->prepare($q);
        $stmt->execute(array(
            ':id'       => $id,
            ':name'     => $name,
            ':expiry'   => $expiry
        ));
        
        return $stmt->fetchObject();
    }
    
    
    protected function _update($id, $data) {
        $q = "UPDATE Sessions "
        . "SET lifetime=:lifetime, data=:data, modified=UNIX_TIMESTAMP(NOW()) "
        . "WHERE id=:id ";
        $stmt = $this->_db->prepare($q);
        foreach($data as $name => $value) {
            $stmt->bindValue(':'.$name, $value);
        }    
        return $stmt->execute();
    }
    
    
    protected function _insert($id, $data) {
        $q = "INSERT INTO Sessions "
        . "(`id`, `name`, `lifetime`, `data`, `modified`) "
        . "VALUES (:id, :name, :lifetime, :data, UNIX_TIMESTAMP(NOW())) ";
        $stmt = $this->_db->prepare($q);
        foreach($data as $name => $value) {
            $stmt->bindValue(':'.$name, $value);
        }
        return $stmt->execute();
    }
    
    
}