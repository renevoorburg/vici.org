<?php
/**
Copyright 2014, René Voorburg, rene@digitopia.nl

This file is part of the Vici.org source.

Vici.org source is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Vici.org  source is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Vici.org source.  If not, see <http://www.gnu.org/licenses/>.
*/
 

class XMLNode 
{
    const SPACES = '                                                     ';
    
    protected $name;
    protected $attributes = array();
    protected $subnodes = array();
    protected $text = NULL;

    public function __construct($name, $content = NULL, $attribute = NULL)
    {
        $this->name = $name;
        
        if (is_object($content)) {
            $this->addNode($content);
        } else {
            $this->addText($content);
        }
        
        if ($attribute) {
            $this->addAttribute($attribute);
        }
        
    }

    public function addNode($node)
    {
        $this->subnodes[] = $node;
        $this->text = NULL;
    }
    
    public function addText($text)
    {
        $this->text = $text;
        $this->subnodes = array();
    }
    
    public function addAttribute($attribute)
    {
        $this->attributes[] = $attribute;
    }
    
    public function printNode($indent = false, $spaces = 0) 
    {
        $sep = $indent ? "\n" : '';
        $nextSpaces = $indent ? $spaces + 2 : $spaces;
        $attributeStr = empty($this->attributes) ? '' : ' '.implode(' ', $this->attributes);  
        
        echo substr($this::SPACES, 0, $spaces); 
        if ($this->isEmpty()) {
            echo '<',$this->name,$attributeStr,'/>',$sep;
        } else { 
            echo '<',$this->name,$attributeStr,'>';       
            if (empty($this->subnodes)) {
                echo $this->text;
            } else {
                echo $sep;
                foreach ($this->subnodes as $node) {
                    $node->printNode($indent, $nextSpaces);
                }
                echo substr($this::SPACES, 0, $spaces); 
            }
            echo "</",$this->name,'>',$sep;
        }
    }
    
    private function isEmpty()
    {
        return empty($this->subnodes) && $this->text === NULL;
    }
    
}
