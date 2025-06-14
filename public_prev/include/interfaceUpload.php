<?php

/**
Copyright 2014 - 2018, René Voorburg, rene@digitopia.nl

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

interface Upload 
{
    public function __construct($postArr, Lang $lngObj);

    public function authenticate($user, $object);
            
    public function validate($postArr);
    
    public function process($postArr);  
    
    public function getLastMessage();  
    
    public function getPageScripts();
    
    public function getPageForm();
    
}