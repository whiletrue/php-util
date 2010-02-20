<?php
/**
 * Utility Class to handle commandline options 
 * and arguments
 * 
 * @author <silvan@etoy.com>
 */
Class Util_Cli_Getopt {

	
	protected $_opts      = array();
	
	protected $_optdef    = array();
	
	protected $_args      = array();
	
	public function __construct($sOpts='', array $lOpts=array()) {
	   $this->setOpts($sOpts, $lOpts);
	   $this->parseOpts();
	}
	
	protected function setOpts($shortopts='', array $longopts=array()) {
	   $this->setShortOpts($shortopts);
	   $this->setLongOpts($longopts);     
	}
	
	protected function addOptDef(array $def = array()) {
	   $this->_optdef = array_merge($this->_optdef, $def);
	}
	
	protected function addArg($arg) {
	   return ($this->_args[] = $arg);
	}
	
	protected function addArgs(array $args=array()) {
	   $this->_args = array_merge($this->_args, $args);
	}
	
	protected function setShortOpts($so='') {
	   $chars = range('a', 'z');
	   $chr = str_split($so);
	   $so = array();
	   $cc = '';
	   foreach($chr as $i => $c) {
	       if (in_array($c, $chars)) {
	           $so[$c] = '';
	           $cc = $c;
	       } else if ($c === ":") {
	           $so[$cc] .= $c;
	       }
	   }   
	   $this->addOptDef($so);
	}
	
	protected function setLongOpts(array $lo = array()) {
	    $longOpts = array();
		foreach($lo as $i => $opt) {
	       if (preg_match("#(\w+)(\:{1,2})?#", $opt, $matches)) {
	           $m = (isset($matches[2])) ? $matches[2] : '';
	           $longOpts[$matches[1]] = $m;  
	       }
	   }
	   
	   $this->addOptDef($longOpts);
	   return true;
	}	
    
	protected function getArgv() {
	    if (!isset($_SERVER['argv'])) {
            return false;
        }
        return array_slice($_SERVER['argv'], 1);
	}
	
	
    protected function parseOpts() {
        $argv = $this->getArgv();
        if (!$argv) {
            return;
        }
        while(count($argv) > 0) {
        	$tok = array_shift($argv);
            if (substr($tok, 0, 2) === '--') {
                $this->parseLongOpt($tok, $argv);
            } else if (substr($tok,0, 1) === '-') {
                $this->parseShortOpt($tok, $argv);
            } else  {
                // argument
                $this->addArg($tok);
            }
        }
    }
    
    protected function parseNextValue(&$argv) {
        if (strpos($argv[0], '-') !== false && strpos($argv[0], '--') !== false) {
           return false; 
        }
        return $argv[0];
    } 
    
    protected function parseOptValue($opt, &$argv, $parsed='') {
        $cfg = $this->_optdef[$opt];
        if ($cfg === '') {
            return true;
        } else if ($cfg === ':' || $cfg === '::') {
            if (!empty($parsed)) {
                return $parsed;
            }
            
            $v = $this->parseNextValue($argv);
            if (false !== $v) {
            	array_shift($argv);
                return $v;
            }
        }
        return false;
    }
    
    protected function parseLongOpt($str, &$argv) {
        if (preg_match('#--(\w+)[\s\=]?([\w]*)#', $str, $matches) &&
    	    isset($this->_optdef[$matches[1]])) {
            
            $v = $this->parseOptValue($matches[1], $argv, $matches[2]);
            if (false !== $v) {
                $this->setOpt($matches[1], $v);
                return true;
            }
        }
        return false;   
    }	
    
    
    protected function parseShortOpt($str, &$argv) {
        if (preg_match('#-(\w{1})\s?([\w]*)#', $str, $matches) &&
            isset($this->_optdef[$matches[1]])) {
            
            $v = $this->parseOptValue($matches[1], $argv, $matches[2]);
            if (false !== $v) {
                $this->setOpt($matches[1], $v);
                return true;
            }
        }
        return false;
    }
    
    public function getArgs() {
        return $this->_args;
    }
    
    public function setOpt($name, $value) {
        return ($this->_opts[$name] = $value);
    }
    
    public function getOpts() {
        return $this->_opts;
    }
    
    public function getOpt($oname) {
        if (isset($this->_opts[$oname])) {
            return $this->_opts[$oname];
        }
        return Null;
    }
    
    public function hasOpt($oname) {
        return (isset($this->_opts[$oname]));
    }
    
        
}