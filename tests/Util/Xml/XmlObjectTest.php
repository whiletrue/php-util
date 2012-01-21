<?php
require_once 'Util/Xml/XmlObject.php';
require_once 'Util/TestCase.php';

Class XmlObjectTest extends Util_TestCase {
    
    
    protected $_instance;
    protected $_fixture;
     
    
    
    public function setUp() {
        $this->_fixture = $this->_getDomDataFixture('/util/xml/xmlobject.xml');
        $this->assertInstanceOf('DOMDocument', $this->_fixture);
        $this->_instance = new Util_Xml_XmlObject($this->_fixture->documentElement);
        $this->assertInstanceOf('Util_Xml_XmlObject', $this->_instance); 
    }    
    
    /**
     * Test setup
     *
     */
    public function testSetup() {
        // has node
        $this->assertTrue($this->_instance->hasNode());
        // test countable
        $this->assertCount(1, $this->_instance);
    }
    
    public function testInterfaces() {
        $this->assertInstanceof('Traversable', $this->_instance);
        $this->assertInstanceof('Countable', $this->_instance);
        $this->assertInstanceof('ArrayAccess', $this->_instance);
    }
     
    public function testValue() {
        $v = '10208030';
        $node = $this->_instance->sh->sh_id;
        $this->assertInstanceof('Util_Xml_XmlObject', $node);
        $this->assertEquals($v, $node->getValue());
        $this->assertEquals($v, (string) $node);
        $this->assertEquals($v, $node->value());
       
    }
    
    public function testValueText() {
        $v = '10208030';
        $node = $this->_instance->sh->sh_id;
        $this->assertInstanceof('Util_Xml_XmlObject', $node);
        $this->assertEquals($v, $node->text());
    }
    
    public function testValueTextEmpty() {
        $node = $this->_instance->sh;
        $this->assertInstanceof('Util_Xml_XmlObject', $node);
        $this->assertEquals('', $node->text());
    }
    
    public function testValueTextCdata() {
        $node = $this->_instance->sh->sh_header;
        $this->assertInstanceof('Util_Xml_XmlObject', $node);
        $this->assertEquals('cdata section test', $node->text());
    }
    
    public function testSingleNode() {
       $node = $this->_instance->sh;
       $this->assertInstanceof('Util_Xml_XmlObject', $node);
       $this->assertEquals('sh', $node->getName());
       $this->assertCount(1, $node);
    }
    
    public function testAttributes() {
        $attr = $this->_instance->getAttributes();
        $this->assertArrayHasKey('method', $attr);
        $this->assertEquals('export', $attr['method']);    
    }
    
    public function testAttribute() {
        $attr = $this->_instance->getAttribute('method');
        $this->assertEquals('export', $attr);
    }
    
    public function testNodeset() {
        $nodel = $this->_instance->sh->bt->entry;
        $this->assertInstanceof('Util_Xml_XmlObject', $nodel);
        $this->assertEquals('entry', $nodel->getName());
        $this->assertCount(3, $nodel);
    }
    
    public function testNodesetIterator() {
        $nodel = $this->_instance->sh->bt->entry;
        $ids = array('10208131', '10208132', '10208031');
        // firstchild
        $this->assertEquals($ids[0], $nodel->bt_id->getValue());
        // iterate
        foreach($nodel as $i => $node) {
            $this->assertEquals($ids[$i], $node->bt_id->getValue());
        }
        $nodel->rewind();
        $this->assertEquals($ids[0], $nodel->bt_id->getValue());
    }
    
    public function testAdd() {
        $parent = $this->_instance->sh;
        $this->assertInstanceof('Util_Xml_XmlObject', $parent);
        
        $child = $parent->add('foo', 'bar');
        $this->assertInstanceof('Util_Xml_XmlObject', $child);
        $this->assertEquals('foo', $child->getName());
        $this->assertEquals('bar', $child->getValue());
        $this->assertEquals('bar', $child->text());
        
        $child2 = $this->_instance->sh->foo;
        $this->assertInstanceof('Util_Xml_XmlObject', $child);
    }
    
    public function testAddMagic() {
        $parent = $this->_instance->sh;
        $this->assertInstanceof('Util_Xml_XmlObject', $parent);
        $this->assertEquals('sh', $parent->getName());
        
        $parent->bar = 'foo';
        $child = $parent->bar;
        $this->assertInstanceof('Util_Xml_XmlObject', $child);
        $this->assertEquals('bar', $child->getName());
        $this->assertEquals('foo', $child->getValue());
        $this->assertEquals('foo', $child->text());
        
        $child2 = $this->_instance->sh->bar;
        $this->assertInstanceof('Util_Xml_XmlObject', $child2);
        $this->assertEquals('bar', $child2->getName());
        $child2->setValue('new value');
        
        $this->assertEquals('new value', $child2->getValue());
        $this->assertEquals('new value', (string) $this->_instance->sh->bar);
        $this->assertEquals('new value', $this->_instance->sh->bar->text());
    }
    
    public function testIterator() {
        $nl = $this->_instance->sh->bt->entry;
        $this->assertInstanceof('Util_Xml_XmlObject', $nl);
        $this->assertEquals('entry', $nl->getName());
        $this->assertCount(3, $nl);
        
        for($i=0; $i<3; $i++) {
            $this->assertEquals($i, $nl->key());
            $this->assertTrue($nl->valid());
            $nl->next();
        }
        
        $nl->next();
        $this->assertEquals(4, $nl->key());
        $this->assertFalse($nl->valid());
        
        $nl->rewind();
        $this->assertEquals(0, $nl->key());
    }
    
    public function testIsset() {
        $this->assertTrue(isset($this->_instance->sh));
        $this->assertTrue(isset($this->_instance->sh->bt));
        $this->assertTrue(isset($this->_instance->sh->bt->entry));
        $this->assertTrue(isset($this->_instance->sh->bt->entry[0]));
        $this->assertTrue(isset($this->_instance->sh->bt->entry[1]));
        $this->assertTrue(isset($this->_instance->sh->bt->entry[2]));
        $this->assertFalse(isset($this->_instance->sh->bt->entry[3]));
        $this->assertFalse(isset($this->_instance->sh->foo));
    }
    
    public function testArrayAccessGet() {
        $node = $this->_instance->sh->bt;
        $this->assertInstanceof('Util_Xml_XmlObject', $node);
        $node2 = $node['entry'];
        $this->assertInstanceof('Util_Xml_XmlObject', $node2);
        $this->assertEquals('entry', $node2->getName());
        $this->assertCount(3, $node2);
    }
    
    public function testArrayAccessGetInt() {
        $node = $this->_instance->sh->bt->entry;
        $this->assertInstanceof('Util_Xml_XmlObject', $node);
        $this->assertEquals('entry', $node->getName());
        $node2 = $node[0];
        $this->assertInstanceof('Util_Xml_XmlObject', $node2);
        $this->assertEquals('entry', $node2->getName());
    }
    
    public function testArrayAccessSet() {
        $node = $this->_instance->sh;
        $this->assertInstanceof('Util_Xml_XmlObject', $node);
        $this->assertEquals('sh', $node->getName());
        
        $node['foo'] = 'bar';
        $this->assertInstanceof('Util_Xml_XmlObject', $this->_instance->sh->foo);
        $this->assertEquals('foo', $this->_instance->sh->foo->getName());
        $this->assertEquals('bar', $this->_instance->sh->foo->getValue());
    }
    
    public function testArrayAccessSetInt() {
        $node = $this->_instance->sh;
        $this->assertInstanceof('Util_Xml_XmlObject', $node);
        $this->assertEquals('sh', $node->getName());
        $this->assertInstanceof('Util_Xml_XmlObject', $node[0]);
        $this->assertEquals('sh', $node[0]->getName());
        $this->assertCount(1, $node);
        $this->assertNull($node->offsetSet(1, 'foob'));
        $this->assertNull($node->item(1));
    }
    
    
    
}
