<?php

require 'DocBlock.php';
  
class DocBlockTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->docBlock = new DocBlock();
    }

    protected function tearDown()
    {
        unset($this->docBlock);
    }

    public function testGetShortDescription()
    {
        $this->docBlock->parse(file_get_contents(__DIR__ . '/_testdata/docblock'));
        $this->assertEquals('the heading', $this->docBlock->getShortDescription());
    }

    public function testGetShortDescriptionFromDocBlockWithoutBody()
    {
        $this->docBlock->parse(file_get_contents(__DIR__ . '/_testdata/heading'));
        $this->assertEquals('the heading', $this->docBlock->getShortDescription());
    }

    public function testGetLongDescription()
    {
        $this->docBlock->parse(file_get_contents(__DIR__ . '/_testdata/docblock'));
        $this->assertEquals('the body', $this->docBlock->getLongDescription());
    }

    public function testGetMultilineBody()
    {
        $this->docBlock->parse(file_get_contents(__DIR__ . '/_testdata/multiline_body'));
        $this->assertEquals('this body spans multiple lines', $this->docBlock->getLongDescription());
    }

    public function testGetParams()
    {
        $this->docBlock->parse(file_get_contents(__DIR__ . '/_testdata/docblock'));
        $this->assertEquals('string $foo The foo parameter', $this->docBlock->getParam(0));
        $this->assertEquals('int $bar Number of bars', $this->docBlock->getParam(1));
    }

    public function testGetReturn()
    {
        $this->docBlock->parse(file_get_contents(__DIR__ . '/_testdata/docblock'));
        $this->assertEquals('null', $this->docBlock->getReturn());
    }

    public function testGetThrows()
    {
        $this->docBlock->parse(file_get_contents(__DIR__ . '/_testdata/throws'));
        $this->assertEquals(array('SomeException The description', 'AnotherException The other description'), $this->docBlock->getThrows());
    }
        
    /**
     * Make sure that getThrows() also finds @exception tags.
     */        
    public function testGetException()
    {
        $this->docBlock->parse(file_get_contents(__DIR__ . '/_testdata/exception'));
        $this->assertEquals(array('SomeException The description', 'AnotherException The other description'), $this->docBlock->getThrows());
    }
    
    public function testParseFullVarDocBlock()
    {
        $this->docBlock->parse(file_get_contents(__DIR__ . '/_testdata/var_full'));
        $this->assertEquals('string', $this->docBlock->getVar());
        $this->assertEquals('the heading', $this->docBlock->getShortDescription());
        $this->assertEquals('the body', $this->docBlock->getLongDescription());
    }

    public function testParseVarOnlyDocBlock()
    {
        $this->docBlock->parse(file_get_contents(__DIR__ . '/_testdata/var_only'));
        $this->assertEquals('string', $this->docBlock->getVar());
        $this->assertEquals('', $this->docBlock->getShortDescription());
        $this->assertEquals('', $this->docBlock->getLongDescription());
    }

    public function testParseVarDocBlockWithoutBody()
    {
        $this->docBlock->parse(file_get_contents(__DIR__ . '/_testdata/var_no_body'));
        $this->assertEquals('string', $this->docBlock->getVar());
        $this->assertEquals('the heading', $this->docBlock->getShortDescription());
        $this->assertEquals('', $this->docBlock->getLongDescription());
    }
}
?>
