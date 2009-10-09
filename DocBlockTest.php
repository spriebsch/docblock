<?php
/**
 * Copyright (c) 2009 Stefan Priebsch <stefan@priebsch.de>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 *   * Redistributions of source code must retain the above copyright notice,
 *     this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright notice,
 *     this list of conditions and the following disclaimer in the documentation
 *     and/or other materials provided with the distribution.
 *
 *   * Neither the name of Stefan Priebsch nor the names of contributors
 *     may be used to endorse or promote products derived from this software
 *     without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO,
 * THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER ORCONTRIBUTORS
 * BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
 * OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    DocBlock
 * @author     Stefan Priebsch <stefan@priebsch.de>
 * @copyright  Stefan Priebsch <stefan@priebsch.de>. All rights reserved.
 * @license    BSD License
 */

require 'DocBlock.php';
  
/**
 * Tests for the DocBlock Parser.
 *
 * @author     Stefan Priebsch <stefan@priebsch.de>
 * @copyright  Stefan Priebsch <stefan@priebsch.de>. All rights reserved.
 */
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
