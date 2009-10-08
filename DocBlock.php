<?php

class DocBlock
{
    protected $docBlock = array();

    protected $heading = '';
    protected $body = '';
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
        
        // skip first /** line
        $lineNumber = 1;

        // If first line is a tag, there is no heading and no body
        if (!$this->isTag($this->getLine($lineNumber))) {
	        $this->heading = $this->getLine($lineNumber);
	        $lineNumber++;    

		    // skip empty lines between heading and body
			$lineNumber = $this->skipEmptyLines($lineNumber);
			
		 	// body ends with a blank line or tag, or at the end of the DocBlock
			while (!$this->isEmptyLine($this->getLine($lineNumber)) && !$this->isTag($this->getLine($lineNumber)) && !$this->isLastLine($lineNumber)) {
				$this->body .= $this->getLine($lineNumber) . ' ';
				$lineNumber++;
			}

			$this->body = trim($this->body);

			// skip blank lines below body
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
    
    public function getHeading()
    {
        return $this->heading;
    }

    public function getBody()
    {
        return $this->body;
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
        }
        
        return $result;
    }
    
    public function getReturn()
    {
        foreach ($this->tags as $tag) {
            if ($this->isTag($tag, 'return')) {
	            if ($this->isTag($tag, 'returns')) {
                    return $this->removeTag('returns', $tag);
                }
            
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
}
?>
