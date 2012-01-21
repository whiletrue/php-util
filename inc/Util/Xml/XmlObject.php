<?php
/**
 * Utility class that provides object oriented access
 * to a DOM XML structure.
 * 
 * @author <silvan@etoy.com>
 */
class Util_Xml_XmlObject implements Countable, ArrayAccess, Iterator {
    
    protected $_nodes = array();
    
    protected $_key = 0;
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
        if (is_int($key)) {
            return $this->hasNode($key);
        }
        
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
        $node = $this->get($key);
        if ($node instanceof self && $node->getName() === $key) {
            $node->setValue($value);
            return $node;
        }
        return $this->add($key, $value);
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
        if (!$childNode instanceof DOMElement) {
             return null;   
        }
        
        $child = new self($childNode);
        $child->setName($name);
        return $child;
    }
    
    
    /**
     * Append a new child node to the current
     * 
     * //FIXME: support non-scalar element values (arrays, objects)
     * @param $name
     * @param $value
     * @return DOMElement
     */
    protected function addChildNode($name, $value=null, DOMElement $sibling=null) {
         
        $node = $this->getNode();
        if (null === $node) {
            return null;
        }
        
        $child = $node->ownerDocument->createElement($name);
        if (is_scalar($value)) {
            // FIXME: validation
            $child->nodeValue = htmlspecialchars($value, ENT_COMPAT, 'UTF-8', FALSE);
        }
        
        if (null === $sibling) {
            $node->appendChild($child);
        } else {
            $node->insertBefore($child, $sibling->nextSibling);
        }
        return $child;
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
            $obj = self::create();
            $obj->setName($key);
            foreach($nl as $node) {
                $obj->addNode($node);
            }
            return $obj;
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
        $xp = $this->getXPath();
        if (null === $xp) {
            return null;
        }
        $node = $this->getNode();
        if (null === $node) {
            return null;
        }
        $nl = $xp->query($xpath, $node);
        return $nl;
        
    }
    
    protected function resetXPath() {
        return ($this->_xp = null);
    }
    
    
    protected function getXPath() {
        if ($this->_xp instanceof DOMXPath) {
            return $this->_xp;
        }
        $node = $this->getNode();
        if (null === $node) {
            return null;
        }
        $this->_xp = new DOMXPath($node->ownerDocument);
        return $this->_xp;
    }
    
    /**
     * set value
     * 
     * @access  public
     */
    public function setValue($value) {
        if (!is_scalar($value)) {
            return false;
        }
        $node = $this->getNode();
        if (null === $node) {
            return false;
        }
        
        $v = htmlspecialchars($value, ENT_COMPAT, 'UTF-8', FALSE);
        $node->nodeValue = $v;
        return true;
    }
    
    /**
     * get value
     * 
     * @access  public
     * @return  mixed
     */
    public function getValue() {
        $node = $this->getNode();
        if (null === $node) {
            return null;
        }
        
        return $node->nodeValue;
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
        $node = $this->getNode();
        if (null === $node) {
            return null;
        }
        
        if (!$node->hasChildNodes()) {
            return null;
        }
        $text = '';
        $allowedTypes = array(XML_TEXT_NODE, XML_CDATA_SECTION_NODE);
        foreach($node->childNodes as $child) {
            if (in_array($child->nodeType, $allowedTypes)) {
                $text.= $child->nodeValue;
            }
        }
        if (!empty($text)) {
            return $text;
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
    
    
    public function addNode(DOMElement $node) {
        return ($this->_nodes[] = $node);
    }
    
    public function setNode(DOMElement $node, $key) {
        return ($this->_nodes[$key] = $node);
    }
    
    public function item($key) {
        if ($this->hasNode($key)) {
            $obj = new self($this->getNode($key));
            $obj->setName($this->getName());
            return $obj;
        }
        return null;
    }
    
    public function hasNode($key = null) {
        $key = (null === $key) ? $this->key() : $key;
        return isset($this->_nodes[$key]);
    }
    
    public function getNode($key = null) {
        $k = (null === $key) ? $this->key() : $key;
        if (isset($this->_nodes[$k])) {
            return $this->_nodes[$k];
        }
        return null;
    }
    
    
    public function getDomDocument() {
        if (!$this->hasNode()) {
            return null;
        }
        $node = $this->getNode();
        if (null === $node) {
            return null;
        }
        return $node->ownerDocument;
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
        return $this->addNode($element);
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
    public function remove() {
        $node = $this->getNode();
        if (null === $node) {
            return false;
        }
        
        $old = $node->parentNode->removeChild($node);
        return $this->setNode($old, $this->key());
    }
    
    
    /**
     * Create Util_Xml_XmlObject instance from DomElement
     * 
     * @param   DOMElement  $element
     * @return  Util_Xml_XmlObject
     * @access  public
     * @static
     */
    public static function create(DOMElement $element=null) {
        $xmlo = new self($element);
        return $xmlo;
    }
    
    
    /**
     * Check if particular attribute exists
     * 
     * @param   string  $name
     * @return  boolean 
     * @access  public
     */
    public function hasAttribute($name) {
        $node = $this->getNode();
        if (null === $node) {
            return false;
        }
        return $node->hasAttribute($name);
    }
    
    /**
     * get Attribute
     * 
     * @param   string  $name
     * @return  string  
     * @access  public
     */
    public function getAttribute($name) {
        $node = $this->getNode();
        if (null === $node) {
            return null;
        }
        return $node->getAttribute($name);
    }
    
    /**
     * get all attributes
     * 
     * @access  public
     * @return  array
     */
    public function getAttributes() {
        $attr = array();
        $node = $this->getNode();
        if (null === $node) {
            return $attr;
        }
        foreach($node->attributes as $attrib) {
            $attr[$attrib->name] = $attrib->value;
        }
        return $attr;
    }
    
    /**
     * Set attributes
     * 
     * @param   array   $attributes
     * @access  public
     * @return  boolean
     */
    public function setAttributes(array $attributes) {
        $node = $this->getNode();
        if (null === $node) {
            return false;
        }
        foreach($attributes as $name => $value) {
            $node->setAttribute($name, $value);
        }
        return true;
    }
    
	/**
	 * Set Attribute
     *
     * @param   string  $name
     * @param   string  $value
     * @access  public
     * @return  boolean
     */
    public function setAttribute($name, $value) {
        $node = $this->getNode();
        if (null === $node) {
            return false;
        }
        return $node->setAttribute($name, $value);
    }
    
    /**
     * Check if node has attributes
     * 
     * @return boolean
     * @access public
     */
    public function hasAttributes() {
        $node = $this->getNode();
        if (null === $node) {
            return null;
        }
        return $node->hasAttributes();
    }
    
    /**
     * Serialize to xml string
     * 
	 * @return string
     */
    public function toXml() {
        $doc = $this->getDomDocument();
        $node = $this->getNode();
        if ($doc instanceof DOMDocument && $node instanceof DOMElement) {
            return $doc->saveXML($node);
        }
    }
    
    /**
     * Iterator interface
     * @see Iterator::key()
     */
    public function key() {
        return $this->_key;
    }
    /**
     * Iterator interface
     * @see Iterator::current()
     */
    public function current() {
        return $this;
    }
    /**
     * Iterator interface
     * @see Iterator::next()
     */
    public function next() {
        $this->setKey($this->key() + 1);
    }
    /**
     * Iterator interface
     * @see Iterator::rewind()
     */
    public function rewind() {
        $this->setKey(0);
    }
    /**
     * Iterator interface
     * @see Iterator::valid()
     */
    public function valid() {
        return $this->hasNode($this->key());
    }
    /**
     * Set iterator key
     * @param int $key
     */
    public function setKey($key) {
        return ($this->_key = $key);
    }
    
    /**
     * Countable interface
     * @see Countable::count()
     */
    public function count() {
        return sizeof($this->_nodes);
    }
    
    /**
     * ArrayAccess interface
     * @see ArrayAccess::offsetSet()
     */
    public function offsetSet($key, $value) {
        if (is_int($key)) {
            /* 
             * don't allow setting/overwriting
             * internal nodelist atm.
             */
            return null;
        }
        return $this->set($key, $value);
    }
    /**
     * ArrayAccess interface
     * @see ArrayAccess::offsetUnset()
     */
    public function offsetUnset($key) {
        //FIXME:
    }
    /**
     * ArrayAccess interface
     * @see ArrayAccess::offsetGet()
     */
    public function offsetGet($key) {
        if (is_int($key)) {
            return $this->item($key);
        }
        return $this->get($key);
    }
    /**
     * ArrayAccess interface
     * @see ArrayAccess::offsetExists()
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