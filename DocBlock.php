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
    /**
     * Lists supported tags that are handled by the generic accessor getTag().
     * @param tags are also supported, but have their own accessor.
     * @throws and @exception also have their own accessor.
     *
     * @var array
     */
    protected $supportedTags = array(
        'author',
        'copyright',
        'copyright',
        'deprecated',
        'example',
        'global',
        'ignore',
        'internal',
        'link',
        'package',
        'return',
        'see',
        'since',
        'subpackage',
        'todo',
        'var',
        'version',
    );

    protected $docBlock = array();

    protected $shortDescription = '';
    protected $longDescription = '';
    protected $tags = array();
    protected $paramTags = array();
    
    protected function preProcess($docblock)
    {
        if ($docblock == '') {
            throw new RuntimeException('Empty docblock');
        }
    
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
    
    /**
     * Returns given line of the docblock
     *
     * @param int $line Line number
     * @return string
     */
    protected function getLine($line)
    {
        return $this->docBlock[$line];
    }
    
    /**
     * Returns whether given line is the last line in the docblock
     *
     * @param int $line Line number
     * @return bool
     */
    protected function isLastLine($line)
    {
        return $line == sizeof($this->docBlock) - 1;
    }

    /**
     * Returns whether string is empty (is an empty line).
     *
     * @param string $line String to check
     * @return bool
     */
    protected function isEmptyLine($string)
    {
        return strlen($string) == 0;
    }

    /**
     * Returns whether string ends with a dot.
     *
     * @param string $line String to check
     * @return bool
     */
    protected function isDotTerminated($string)
    {
        return substr($string, -1) == '.';
    }

    /**
     * Returns whether given string is a tag (starts with @),
     * or whether given string is a given tag (starts with @<tag>).
     *
     * @param string $string String to check
     * @param string $tag    Optional tag to check for
     * @return bool
     */
    protected function isTag($string, $tag = null)
    {
        if ($tag === null) {
	        return substr($string, 0, 1) == '@';
	    }

	    $tag = '@' . $tag;
	    return substr($string, 0, strlen($tag)) == $tag;
    }
    
    /**
     * Skips empty lines in the docblock, starting from given line.
     * Returns the next line number that is non-empty.
     *
     * @param int $line Line number to start
     * @return int
     */
    protected function skipEmptyLines($line)
    {
        // if current line is not empty, we are done    
        if (!$this->isEmptyLine($this->getLine($line))) {
			return $line;        
        }
    
		while ($this->isEmptyLine($this->getLine($line))) {
			$line++;
		}
		
		return $line;
    }

    /**
     * Removes a tag (@<tag>) from the beginning of given string.
     *
     * @param $tag    Tag to remove
     * @param $string String to remove tag from
     * @return string
     */    
    protected function removeTag($tag, $string)
    {
    	return trim(substr($string, 1 + strlen($tag)));
   	}

    /**
     * Returns whether given tag appears in the docblock at least once.
     *
     * @param string $name Name of the tag (without @)
     * @return bool
     */
    protected function hasTag($name)
    {
		foreach ($this->tags as $tag) {
		    if ($this->isTag($tag, $name)) {
		        return true;
		    }
		}
        
        return false;
    }
   	
    /**
     * Returns given tag from the docblock. Will only return one tag,
     * so cannot be used for tags that appear more than once.
     *
     * @param string $name Name of the tag (without @)
     * @return string
     */
    protected function getTag($name)
    {
		foreach ($this->tags as $tag) {
		    if ($this->isTag($tag, $name)) {
		        return $this->removeTag($name, $tag);
		    }
		}
        
        throw new RuntimeException('No @' . $name . ' tag found');
    }

    /**
     * Returns given tags from the docblock. This method returns an array of
     * tags, and must thus be used for tags that appear more than once, 
     * e.g. exception or throws.
     *
     * @param array $name Array of names (each without @)
     * @return array
     */
    protected function getTags(array $names)
    {
        $result = array();

        foreach ($names as $name) {
		    foreach ($this->tags as $tag) {
		        if ($this->isTag($tag, $name)) {
		            $result[] = $this->removeTag($name, $tag);
		        }
		    }
		}
        
        return $result;
    }
    
    /**
     * Parses a docblock comment.
     *
     * @param string $docblock The docblock
     * @return null
     */
    public function parse($docblock)
    {
        $this->preProcess($docblock);
        
        $this->shortDescription = '';
        $this->longDescription = '';
        $this->tags = array();
        $this->paramTags = array();
        
        // skip first /** line
        $lineNumber = 1;

        // If first line is a tag, there is no short and no long description
        if (!$this->isTag($this->getLine($lineNumber))) {
	        // short description ends either with empty line, or dot at the end of a line.
	        while (!$this->isEmptyLine($this->getLine($lineNumber)) && !$this->isDotTerminated($this->shortDescription) && !$this->isLastLine($lineNumber)) {
		        $this->shortDescription .= ' ' . trim($this->getLine($lineNumber));
		        $lineNumber++;
		    }
		    
		    $this->shortDescription = trim($this->shortDescription);

		    // skip empty lines between short and long description
			$lineNumber = $this->skipEmptyLines($lineNumber);
			
		 	// long description ends with a blank line or another tag,
		 	// or at the end of the DocBlock
			while (!$this->isEmptyLine($this->getLine($lineNumber)) && !$this->isTag($this->getLine($lineNumber)) && !$this->isLastLine($lineNumber)) {
				$this->longDescription .= $this->getLine($lineNumber) . ' ';
				$lineNumber++;
			}

			$this->longDescription = trim($this->longDescription);

     	    if ($this->isLastLine($lineNumber)) {
				return;
     	    }

			// skip blank lines below long description
			$lineNumber = $this->skipEmptyLines($lineNumber);
 		}

        // process the @ tags
	    while (!$this->isLastLine($lineNumber)) {
     	    $line = $this->getLine($lineNumber);

            // ignore non-tag lines (for example blank ones)
     	    if ($this->isTag($line)) {

	     	    if ($this->isTag($line, 'param')) {
		            $this->paramTags[] = $line;
	     	    } else {
		            $this->tags[] = $line;
		        }
	        }

            $lineNumber++;
	    }
    }

    /**
     * Returns the short description from the docblock.
     *
     * @return string
     */
    public function getShortDescription()
    {
        return $this->shortDescription;
    }

    /**
     * Returns the long description from the docblock.
     *
     * @return string
     */
    public function getLongDescription()
    {
        return $this->longDescription;
    }
        
    /**
     * Returns parameter tag (@param) of given index.
     *
     * @param int $index The param tag index
     * @return string
     */
    public function getParam($index)
    {
        if (!isset($this->paramTags[$index])) {
            throw new RuntimeException('No @param tag #' . $index);
        }
    
        return str_replace('@param ', '', $this->paramTags[$index]);
    }

    /**
     * Returns type of the parameter tag of given index.
     *
     * @param int $index The param tag index
     * @return string
     */
    public function getParamType($index)
    {
        $param = $this->getParam($index);
        $pos = strpos($param, ' ');
        
        // No spaces, we assume that full string is the type
        if ($pos === false) {
            $pos = strlen($param);
        }

        return substr($param, 0, $pos);
    }

    /**
     * Returns variable name of the parameter tag of given index.
     *
     * @param int $index The param tag index
     * @return string
     */
    public function getParamName($index)
    {
        $param = $this->getParam($index);

        // skip type
        $param = substr($param, strlen($this->getParamType($index)) + 1);

        if (substr($param, 0, 1) != '$') {
            throw new RuntimeException('Missing variable name in @param #' . $index);
        }
        
        // blank separating varname and description
        $pos = strpos($param, ' ');
        
        // No space, we assume that there is no description
        if ($pos === false) {
            $pos = strlen($param);
        }

        return substr($param, 0, $pos);
    }

    /**
     * Returns description of the parameter tag of given index.
     *
     * @param int $index The param tag index
     * @return string
     */
    public function getParamDescription($index)
    {
        $param = $this->getParam($index);
        $paramType = $this->getParamType($index);
        $paramName = $this->getParamName($index);

        return substr($param, strlen($paramType) + strlen($paramName) + 2);
    }

    /**
     * Returns the number of parameter (@param) tag in the docblock.
     *
     * @return int
     */
    public function getNumberOfParamTags()
    {
        return sizeof($this->paramTags);
    }
    
    /**
     * Returns the exceptions thrown (@throws and @exception tags).
     *
     * @return array
     */
    public function getThrows()
    {
		return $this->getTags(array('throws', 'exception'));
    }

    /**
     * Delegates get<name> calls to getTag(<name>).
     *
     * @param string $method Name of the called method
     * @param array $parameters Parameters
     * @return string
     */
    public function __call($method, $parameters)
    {
        if (substr($method, 0, 3) != 'get' && substr($method, 0, 3) != 'has') {
            throw new RuntimeException('Method ' . $method . ' does not exist');
        }

        // get<Name> -> <name>        
        $tag = lcfirst(substr($method, 3));

        if (!in_array($tag, $this->supportedTags)) {
        	throw new RuntimeException('Unknown tag ' . $tag);
        }

        if (substr($method, 0, 3) == 'has') {
            return $this->hasTag($tag);
        }
        
        return $this->getTag($tag);
    }    
}
?>
