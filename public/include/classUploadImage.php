<?php

/**
Copyright 2014-2018, René Voorburg, rene@digitopia.nl

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

$mydir = dirname(__FILE__);
require_once $mydir.'/classLicenses.php';
require_once $mydir.'/classUploadedImage.php';

class UploadImage implements Upload 
{
   private 
       $msg = '',
       $noErrors = true,
       $user,
       $object,
       $lang,
       $lngObj,
       $filename;
        
    public function __construct($postArr, Lang $lngObj) {
        $this->lngObj = $lngObj;
        $this->lang = $lngObj->getLang();
    }
            
    public function authenticate($user, $object) {
        if ($user > 0) {
            $this->user = $user;
        } else {
            $this->msg .= "Error: You need to log in.<br/>";
            $this->noErrors = false;
        }
        if ($object > 0) {
            $this->object = $object;
        } else {
            $this->msg .= "Error: No object defined.<br/>";
            $this->noErrors = false;
        }
        return $this->noErrors;
    }
          
    public function validate($postArr) {

        if (isset($_FILES["file"]["name"])) {
    
            if(isset($postArr["frm_title"]) && strlen($postArr["frm_title"]) < 3 ) {
                $this->msg .= "Error: The title is too short.<br/>";
                $this->noErrors = false;
            }
            if (isset($postArr["frm_ownership"]) && ($postArr["frm_ownership"] == 0) && !isset($postArr["frm_licensed"]) ) {
                $this->msg .= "Error: No license selected.<br/>";
                $this->noErrors = false;
            }
            if (isset($postArr["frm_ownership"]) && ($postArr["frm_ownership"] == 1) && !isset($postArr["frm_agree"]) ) {
                $this->msg .= "Error: You need to agree with the license.<br/>";
                $this->noErrors = false;
            }
  
            if ($this->noErrors) {
                $this->filename = $_FILES["file"]["name"];
                if (preg_match ('/\.(jpg|jpeg|gif|png)$/i' , $this->filename) == 0) {
                    switch ($postArr['file_type']) {
                        case 'image/png':
                        case 'image/x-png':
                             $this->filename .= '.png';
                             break;
                        case 'image/jpeg':
                        case 'image/jpg':
                             $this->filename .= '.jpg';
                             break;
                        case 'image/gif':
                            $this->filename .= '.gif';
                            break;
                        default:
                            $this->msg .= 'Error: Unsupported file type.<br/>';
                            $this->noErrors = false;
                            break;
                    }
                }
            }
        }
        return $this->noErrors;
    }
    
    public function process($postArr){

//        echo "tmp: ".$_FILES["file"]["tmp_name"];

        $image = new UploadedImage($_FILES["file"]["tmp_name"], $this->filename, $this->user, $this->object);
        $image->setDescription($postArr['frm_title'], $postArr['frm_text'], $this->lang);
        if ($postArr["frm_ownership"] == 0) {
            $image->setLicense($postArr["frm_owner"], $postArr["frm_license"], $postArr["frm_attribute"], $postArr["frm_source"]);
        }
        $image->store();
    
        if ($image->error()) {
            $this->msg = $image->errorMsg();
            $this->noErrors = false;
        } else {
            $this->msg = "Image was uploaded successfully.";
        }
        return $this->noErrors; 
    }    
    
    public function getLastMessage(){
        return $this->msg;
    }  
    
    public function getPageScripts(){
        if (preg_match("/\.dev$/i", $_SERVER['HTTP_HOST'])) {
            $requiredLibs = '<script src="/js/jquery-1.8.3.min.js" type="text/javascript"></script>';

        } else {
            $requiredLibs = '<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js" ></script>';
        }
        $scripts = <<<HERE
        $requiredLibs

        <style>
        fieldset {border:1px solid #AAAADD; width:550px}
        textarea {resize:none}
        .label {width:120px;display:inline-block}
        .input {width:420px;display:inline-block}
        .top {vertical-align:top}
        .formtext {line-height: 1.2}
        .red {color:#FF1111}
        #uploadform {line-height: 2.0em}
        </style>

        <script>
        var initialize = function() {
            $('.alert').change(function() {
                $('.alert').removeClass('red');
            });
            $('#ownership').change(function() {
                if ($('#ownership').val() == 1) {
                    $('#own').show();
                    $('#other').hide();
                } else {
                    $('#other').show();
                    $('#own').hide();
                }
            });
            $('#uploadform').submit(function() {
                var result = true;
                if ($('#file').val().length == 0) {
                    $('#file').addClass('red');
                    $('#filelabel').addClass('red');
                    result = false;
                }
                if ($('#title').val().length < 3) {
                    $('#title').addClass('red');
                    $('#titlelabel').addClass('red');
                    result = false;
                }
                if ($('#ownership').val() == 1) {
                    if ($('#agree:checked').val() != 'on') {
                        $('#agreetext').addClass('red');
                        result = false;
                    }
                } else {
                    if ($('#licensed:checked').val() != 'on') {
                        $('#licensedtext').addClass('red');
                        result = false;
                    } 
                    if ($('#owner').val().length < 2) {
                        $('#owner').addClass('red');
                        $('#ownerlabel').addClass('red');
                        result = false;
                    }
                }
                return result;
            });
        };
        //$(document).ready(attachEvents());
        </script>
HERE;

        return $scripts;
    }
    
    public function getPageForm(){
    
        $licensesObj = new Licenses($this->lang); 
        $form = <<<HERE
        <h2>Image upload</h2>
        <form id="uploadform" action="%s" enctype="multipart/form-data" method="post">
        <fieldset>
        <legend>General information: </legend>
        <label class="label alert" id="filelabel" for="file">File:</label><input class="input alert" id="file" name="file" type="file" />
        <label class="label alert" id="titlelabel" for="title">Title:</label><input placeholder="title of the image (required)" class="input alert" id="title" name="frm_title" type="text" />
        <label class="label top" for="text">Description:</label><textarea placeholder="a description of what can be seen (optional)" rows="6" class="input" id="text" name="frm_text"></textarea> 
        <input type="hidden" name="frm_id" value="{$this->object}">  
        </fieldset>

        <fieldset>
        <legend>License: </legend>
        <label class="label alert" for="ownership">Owner / creator:</label><select class="input" id="ownership" name="frm_ownership"><option value="1">I am the creator and owner</option><option value="0">Somebody else is the owner / creator :</option></select>

        <div id="own" style="margin-top:8px">
        <div class="label top"><input id="agree" style="margin-left:90px" type="checkbox" class="alert" name="frm_agree"></div>
        <div id="agreetext" class="input formtext alert">As the creator and owner of this image I agree to publish it using the Creative Commons Attribute Share Alike 3.0 license.</div>
        </div>

        <div id="other" style="display:none">
        <label class="label alert" for="owner" id="ownerlabel">Owner name:</label><input placeholder="name of the copyright holder (required / optional)" class="input alert" id="owner" name="frm_owner" type="text" />
        <label class="label" for="source">Image source:</label><input placeholder="original url of the image (optional)" class="input" id="source" name="frm_source" type="text" />
        <label class="label" for="license">License:</label><select class='input' id='license' name="frm_license">{$licensesObj->optionList()}</select>
        <label class="label" for="title">Attribution:</label><input placeholder="attribute desired by the copyright holder (required / optional)" class="input" id="attribute" name="frm_attribute" type="text" />
        <div style="margin-top:8px">
        <div class="label top"><input id="licensed" style="margin-left:90px" type="checkbox" class="alert" name="frm_licensed"></div><div id="licensedtext" class="input formtext alert">The image was published by the owner using the selected license, or the owner explicitly agreed to have it published on Vici.org using the selected license.</div>
        </div>
        </div>

        <br style="clear:left"/>
        <input style="margin-left:120px;margin-top:8px;" type="submit" value=" Submit " />
        </fieldset>
        </form>
HERE;

        $form = sprintf($form, $this->lngObj->langURL($this->lang, '/upload/form.php' ));

        return $form;
    }
}