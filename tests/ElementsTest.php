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

        $input = <<<BLA
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
    <elem1 />
    <elem2 />
    <elem3 />
  </otherThing>
</root>
BLA;

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
}
