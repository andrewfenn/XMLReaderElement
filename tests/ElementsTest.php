<?php
use Sabre\Xml\XMLReaderElement;
use Sabre\Xml\Reader;

class ElementsTest extends \PHPUnit_Framework_TestCase {

    private $reader;
    private $data;

    public function setUp()
    {
        parent::setUp();
        require_once('./vendor/autoload.php');

        $this->reader = new \Sabre\Xml\Reader();

        $input = <<<XML
<?xml version="1.0"?>
<root xmlns="http://sabredav.org/ns">
  <listThingy>
    <elem1 />
    <elem2 />
    <elem3 />
    <elem4 attr="val" />
    <elem5>content</elem5>
    <elem6><subnode /></elem6>
  </listThingy>
  <listThingy />
  <otherThing>
    <elem1>0</elem1>
    <elem2>true</elem2>
    <elem3>false</elem3>
  </otherThing>
</root>
XML;

        $this->reader->xml($input);
        $this->data = (new XMLReaderElement())->parse($this->reader->parse());
    }

    function testSetNamespaceShowsValues() {
        $this->assertCount(3, $this->data->children());
    }

    function testCanFindChildrenXMLTag() {
        $this->assertEquals('content', $this->data->findFirst('elem5')->value);
    }

    function testCanFindChildrenAttribute() {
        $this->assertEquals('val', $this->data->findFirst('elem4')->attributes->attr);
    }

    function testCanTraverseChildren() {
        $this->assertEquals('subnode', $this->data->findFirst('elem6')->children()[0]->name);
    }

    function testCanGetChildWithMagicFunction() {
        $this->assertEquals('subnode', $this->data->findFirst('elem6')->subnode->name);
    }

    function testCanFindFirstAttribute() {
        $this->assertEquals('val', $this->data->findFirst('@attr'));
    }

    function testCanFindAttribute() {
        $arr = $this->data->find('@attr');

        $this->assertCount(1, $arr);
        $this->assertEquals('val', $arr[0]);
    }

    function testCanFindChild() {
        $arr = $this->data->find('elem1');

        $this->assertCount(2, $arr);
    }

    function testChildValues() {
        $elem = $this->data->otherThing;

        $this->assertEquals(0, $elem->elem1->value);
        $this->assertTrue($elem->elem2->value);
        $this->assertFalse($elem->elem3->value);
    }
}
