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

/**
 * DocBlock Parser
 *
 * @author     Stefan Priebsch <stefan@priebsch.de>
 * @copyright  Stefan Priebsch <stefan@priebsch.de>. All rights reserved.
 */
class DocBlock
{
    protected $docBlock = array();

    protected $shortDescription = '';
    protected $longDescription = '';
    protected $tags = array();
    
    protected function preProcess($docblock)
    {
        // normalize line endings
        $docblock = str_replace("\r\n", "\n", $docblock);
        $docblock = str_replace("\r", "\n", $docblock);

        // reset in case the object instance is reused to parse another doc block
        $this->docBlock = array();

        // remove leading * and whitespace from each line
        foreach (explode ("\n", $docblock) as $line) {
            $this->docBlock[] = (trim(str_replace('*', '', trim($line))));
        }
    }
    
    protected function getLine($line)
    {
        return $this->docBlock[$line];
    }
    
    protected function isLastLine($line)
    {
        return $line == sizeof($this->docBlock) - 1;
    }

    protected function isEmptyLine($string)
    {
        return strlen($string) == 0;
    }

    protected function isTag($string, $tag = null)
    {
        if ($tag === null) {
	        return substr($string, 0, 1) == '@';
	    }

	    $tag = '@' . $tag;
	    return substr($string, 0, strlen($tag)) == $tag;
    }
    
    protected function skipEmptyLines($line)
    {
		while ($this->isEmptyLine($this->getLine($line))) {
			$line++;
		}
		
		return $line;
    }
    
    protected function removeTag($tag, $string)
    {
    	return trim(substr($string, 1 + strlen($tag)));
   	}
    
    public function parse($docblock)
    {
        $this->preProcess($docblock);
        
        $this->shortDescription = '';
        $this->longDescription = '';
        $this->tags = array();
        
        // skip first /** line
        $lineNumber = 1;

        // If first line is a tag, there is no short and no long description
        if (!$this->isTag($this->getLine($lineNumber))) {
	        $this->shortDescription = $this->getLine($lineNumber);
	        $lineNumber++;    

		    // skip empty lines between short and long description
			$lineNumber = $this->skipEmptyLines($lineNumber);
			
		 	// long description ends with a blank line or another tag,
		 	// or at the end of the DocBlock
			while (!$this->isEmptyLine($this->getLine($lineNumber)) && !$this->isTag($this->getLine($lineNumber)) && !$this->isLastLine($lineNumber)) {
				$this->longDescription .= $this->getLine($lineNumber) . ' ';
				$lineNumber++;
			}

			$this->longDescription = trim($this->longDescription);

			// skip blank lines below long description
			$lineNumber = $this->skipEmptyLines($lineNumber);
 		}

        // process the @ tags
	    while (!$this->isLastLine($lineNumber)) {
     	    if ($this->isTag($this->getLine($lineNumber))) {
	            $this->tags[] = $this->getLine($lineNumber);
	        }
            $lineNumber++;
	    }
    }
    
    public function getShortDescription()
    {
        return $this->shortDescription;
    }

    public function getLongDescription()
    {
        return $this->longDescription;
    }
    
    public function getTag($index)
    {
        if (!isset($this->tags[$index])) {
            return '';
        }
    
        return $this->tags[$index];
    }
    
    public function getParam($index)
    {
         return str_replace('@param ', '', $this->getTag($index));
    }
    
    public function getThrows()
    {
        $result = array();

        foreach ($this->tags as $tag) {
            if ($this->isTag($tag, 'throws')) {
                $result[] = $this->removeTag('throws', $tag);
            }

            if ($this->isTag($tag, 'exception')) {
                $result[] = $this->removeTag('exception', $tag);
            }
        }
        
        return $result;
    }
    
    public function getReturn()
    {
        foreach ($this->tags as $tag) {
            if ($this->isTag($tag, 'return')) {
                return $this->removeTag('return', $tag);
            }
        }
        
        throw new RuntimeException('No @return tag found');
    }
    
    public function getVar()
    {
        foreach ($this->tags as $tag) {
            if ($this->isTag($tag, 'var')) {
                return $this->removeTag('var', $tag);
            }
        }
        
        throw new RuntimeException('No @var tag found');
    }

    public function getAuthor()
    {
        foreach ($this->tags as $tag) {
            if ($this->isTag($tag, 'author')) {
                return $this->removeTag('author', $tag);
            }
        }
        
        throw new RuntimeException('No @author tag found');
    }
}
?>
