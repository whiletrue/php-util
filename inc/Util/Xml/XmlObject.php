<?php
/**
 * Utility class that provides object oriented access
 * to a DOM xml structure.
 * 
 * @author <silvan@etoy.com>
 */
class Util_Xml_XmlObject implements Countable, ArrayAccess {
    
    protected $_node;
    protected $_name;    
    protected $_xp;
    
    
    /**
     * Constructor
     * 
     * @param   DOMElement $element
     * @access  public
     */
    public function __construct(DOMElement $element=null) {
        if (null !== $element) {
            $this->loadDomElement($element); 
        }
    }
    
    
    /**
     * magic method for isset() / empty()
     * 
     * @param   string $key
     */
    public function __isset($key) {
        $nl = $this->xpath($key);
        if ($nl instanceof DOMNodeList && $nl->length > 0) {
            return true;
        }
        return false;
    }
    
    
    /**
     * Method to add/update propertiy 
     */
    public function set($key, $value) {
        $n = $this->get($key);
        if ($n instanceof self) {
            $n->setValue($value);
            return $n;
        } else if (null === $n) {
            $child = $this->add($key, $value);
            return $child;
        }
        
        return null;
    }
    
    
    /**
     * Magic method to add/update property
     */
    public function __set($key, $value) {
        return $this->set($key, $value);
    }
    
    
    /**
     * Add child
     * 
     * @see     Util_Xml_XmlObject::addChild()
     * @access  public
     */
    public function add($name, $value, Util_Xml_XmlObject $sibling=null) {
        return $this->addChild($name, $value, $sibling);
    }
    
    
    /**
     * Add child
     * 
     * Appends new node to current and returns
     * XmlObject instance of the created child.
     *
     * @param   string    $name
     * @param   mixed     $value
     * @return  Util_Xml_XmlObject
     * @access  protected
     */
    protected function addChild($name, $value, Util_Xml_XmlObject $sibling=null) {
        $siblingNode = (null !== $sibling) ? $sibling->getNode() : null;
        $childNode = $this->addChildNode($name, $value, $siblingNode);
        $child = new self();
        $child->loadDomElement($childNode);
        return $child;
    }
    
    
    /**
     * Append a new child node to the current
     * 
     * 
     * @param $name
     * @param $value
     * @return DOMElement
     */
    protected function addChildNode($name, $value=null, DOMElement $sibling=null) {
        if ($this->hasNode()) {
            $node = $this->getNode();
            $child = $node->ownerDocument->createElement($name);
            if (null !== $value) {
                $child->nodeValue = htmlspecialchars($value, ENT_COMPAT, 'UTF-8', FALSE);
            }
            if (null === $sibling) {
                $node->appendChild($child);
            } else {
                $node->insertBefore($child, $sibling->nextSibling);
            }
            return $child;
        }
    }
    
    
    /**
     * Retrieve value according to key
     * 
     * @param   string  $key
     * @param   mixed   $default
     * @return  mixed
     * @access  public
     */
    public function get($key, $default=null) {
        $nl = $this->xpath($key);
        if ($nl instanceof DOMNodeList && $nl->length > 0) {
            if ($nl->length === 1) {
                return self::create($nl->item(0));
            } else {
                $obj = array();
                foreach($nl as $node) {
                    $obj[] = self::create($node);
                }
                return $obj;
            }
        }
        
        return new self();
    }
    
    /**
     * Magic method to retrieve property
     */
    public function __get($key) {
        return $this->get($key);
    }
    
    
    protected function xpath($xpath) {
        if ($this->hasNode()) {
            $xp = $this->getXPath();
            if (null === $xp) {
                return null;
            }
        
            $nl = $xp->query($xpath, $this->_node);
            return $nl;
        }
    }
    
    protected function resetXPath() {
        return ($this->_xp = null);
    }
    
    
    protected function getXPath() {
        if ($this->hasNode()) {
            if (!$this->_xp instanceof DOMXPath) {
                $this->_xp = new DOMXPath($this->_node->ownerDocument);
            }
            return $this->_xp;
        }
        return null;
    }
    
    /**
     * set value
     * 
     * @access  public
     */
    public function setValue($value) {
        if (is_scalar($value) && $this->hasNode()) {
            $v = htmlspecialchars($value, ENT_COMPAT, 'UTF-8', FALSE);
            $this->getNode()->nodeValue = $v;
        }
        return true;
    }
    
    /**
     * get value
     * 
     * @access  public
     * @return  mixed
     */
    public function getValue() {
        if ($this->hasNode()) {
            return $this->getNode()->nodeValue;
        } 
        return null;
    }
    
    
    /**
     * get value
     * 
     * @return  mixed
     * @access  public
     * @alias   Util_Xml_XmlObject::getValue()
     */
    public function value() {
        return $this->getValue();
    }
    
    
    /**
     * get text value
     * 
     */
    public function text() {
        $v = $this->value();
        if (is_scalar($v)) {
            return $v;
        }
        return null;
    }
    
    
    /**
     * set name
     * 
     * @access  public
     * @return  boolean
     */
    public function setName($name) {
        return ($this->_name = $name);
    }    
    
    
    /**
     * get name
     * 
     * @access  public
     * @return  string
     */
    public function getName() {
        return $this->_name;    
    }
    
    
    public function setNode(DOMElement $node) {
        return ($this->_node = $node);
    }
    
    
    public function getNode() {
        return $this->_node;
    }

    
    public function hasNode() {
        return ($this->_node instanceof DOMElement);
    }
    
    
    public function getDomDocument() {
        if ($this->hasNode()) {
            return $this->getNode()->ownerDocument;
        }
    }
    
    
    /**
     * load from DomDocument
     * 
     * @param   DOMDocument $dom
     * @access  public
     * @return  boolean
     */
    public function loadDom(DOMDocument $dom) {
        return $this->loadDomElement($dom->documentElement); 
    }
    
    
    /**
     * Load from DOMElement
     * 
     * @param   DOMElment $element
     * @return  boolean
     */
    public function loadDomElement(DOMElement $element) {
        $this->setName($element->nodeName);
        $this->setNode($element);
        $this->resetXPath();
        return true;
    }
    
    /**
     * Removes node from DOM
     * 
     * Removes domnode of this instance if 
     * a parent is available. The removed
     * node becomes the current of this instance
     * and could be reappended later. 
     * DOMNode of this instance. 
     * 
     * @return  boolean
     */
    public function removeNode() {
        $n = $this->getNode();
        if ($n instanceof DOMNode && $n->parentNode instanceof DOMNode)  {
             $old = $n->parentNode->removeChild($n);
             if ($old instanceof DOMNode) {
                return $this->loadDomElement($old);
             }
        }
    	return false;
    }
    
    
    
    /**
     * Create Util_Xml_XmlObject instance from DomElement
     * 
     * @param   DOMElement  $element
     * @return  Util_Xml_XmlObject
     * @access  public
     * @static
     */
    public static function create(DOMElement $element) {
        $xmlo = new self($element);
        return $xmlo;
    }
    
    
    /**
     * set Attribute
     * 
     * @param   string  $name
     * @param   string  $value
     * @access  public
     * @return  boolean
     */
    public function setAttribute($name, $value) {
        if ($this->hasNode()) {
            return ($this->getNode()->setAttribute($name, $value));
        }
        return false;
    }
    
    
    /*
     * checks if attribute exists
     * 
     * @param   string  $name
     * @return  boolean 
     * @access  public
     */
    public function hasAttribute($name) {
        if ($this->hasNode()) {
            return ($this->getNode()->hasAttribute($name));
        }
        return false;
    }
    
    
    /**
     * get Attribute
     * 
     * @param   string  $name
     * @return  string  
     * @access  public
     */
    public function getAttribute($name) {
        if ($this->hasNode()) {
            return ($this->getNode()->getAttribute($name));
        }
        return null;
    }
    
    /**
     * get all attributes
     * 
     * @access  public
     * @return  array
     */
    public function getAttributes() {
        $attr = array();
        if ($this->hasNode()) {
            foreach($this->getNode()->attributes as $attrib) {
                $attr[$attrib->name] = $attrib->value;
            }
        }
        return $attr;
    }
    
    
    /**
     * set attributes
     * 
     * @param   array   $attributes
     * @access  public
     * @return  boolean
     */
    public function setAttributes(array $attributes) {
        if ($this->hasNode()) {
            $node = $this->getNode();
            foreach($attributes as $name => $value) {
                $node->setAttribute($name, $value);
            }
            return true;
        }
        return false;
    }
    
    
    /**
     * checks if attributes exist
     * 
     * @return boolean
     * @access public
     */
    public function hasAttributes() {
        if ($this->hasNode()) {
            return ($this->getNode()->hasAttributes());
        }
        return false;
    }
    
    
    public function toXml() {
        $doc = $this->getDomDocument();
        if ($doc instanceof DOMDocument) {
            return $doc->saveXML($this->getNode());
        }
    }
    
    
    /**
     * Countable interface method
     * 
     * @access  public
     * @return  int
     */
    public function count() {
        $xp = "following-sibling::".$this->getName()."|preceding-sibling::".$this->getName();
        $nl = $this->xpath($xp);
        if ($nl instanceof DOMNodeList) {
            return ($nl->length + 1);    
        }
        return 1;
    }
    
    
    /**
     * ArrayAccess interface method
     * 
     * @access  public
     * @return  boolean
     */
    public function offsetSet($key, $value) {
        return $this->set($key, $value);
    }
    
    /**
     * ArrayAccess  interface method
     * 
     * @access  public
     * @return  boolean
     */
    public function offsetUnset($key) {
    }
    
    /**
     * ArrayAccess interface method
     * 
     * @access  public
     * @return  mixed
     */
    public function offsetGet($key) {
        return $this->get($key);
    }
    
    /**
     * ArrayAccess interface method
     * 
     * @access  public
     * @return  boolean
     */
    public function offsetExists($key) {
        return ($this->__isset($key));
    }
    
    /**
     * Magic method for string conversion
     * 
     * @access  public
     * @return  mixed
     */
    public function __toString() {
        return $this->getValue();
    }
    
    
    
    
    
    
}