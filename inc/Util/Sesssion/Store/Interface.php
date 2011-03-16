<?php
interface Util_Session_Store_Interface {
    
    public function get($id, $name, $expiry);
    public function write($id, $name, $expiry, $data, $lifetime);
    public function delete($id, $name);
    public function deleteExpired($maxlifetime);
    
}