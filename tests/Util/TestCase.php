<?php
Class Util_TestCase extends PHPUnit_Framework_TestCase {
    
    
    protected function _getDataFixture($path) {
        $f = TEST_BASEDIR.'/fixtures/data'.$path;
        if (!file_exists($f)) {
            return null;
        }
        return file_get_contents($f);
    }
    
    protected function _getDomDataFixture($path) {
        $xml = $this->_getDataFixture($path);
        if (null === $xml) {
            return null;
        }
        $dom = new DOMDocument();
        if ($dom->loadXML($xml)) {
            return $dom;
        }
        return null;
    }
    
}