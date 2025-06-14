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

$mydir = dirname(__FILE__);
require_once $mydir.'/classLicenses.php';
require_once $mydir.'/classLineKinds.php';
require_once$mydir.'/classUploadedKML.php';


class UploadKML implements Upload 
{
   private 
       $msg = '',
       $noErrors = true,
       $dataProcessed = false,
       $user,
       $object,
       $lang,
       $lngObj,
       $lineKind,
       $kmlObj,
       $filename;
    
    public function __construct($postArr, Lang $lngObj) {
        $this->lngObj = $lngObj;
        $this->lang = $lngObj->getLang();
        $this->lineKind = isset($postArr['frm_type']) ? $postArr['frm_type'] : '' ;
    }
    
    public function authenticate($user, $object) {
        $this->user = $user;
        $this->object = $object;
        return $this->noErrors;
    }        
            
    public function validate($postArr){
        $this->filename = md5_file($_FILES["file"]["tmp_name"]).'.kml';
        $this->kmlObj = new UploadedKML($_FILES["file"]["tmp_name"], $this->filename, $this->user, $this->object, $this->lineKind);
        return $this->noErrors;
    }
    
    public function process($postArr){
        $this->kmlObj->setLicense($postArr['frm_owner'], $postArr['frm_license'], $postArr['frm_attribute']);
        $this->kmlObj->store();
    
        if ($this->kmlObj->error()) {
            $this->msg = $this->kmlObj->errorMsg();
            $this->noErrors = false;
        } else {
            $this->msg = "KML was processed successfully.";
            $this->dataProcessed = true;
        }
        return $this->noErrors;     
    }    
    
    public function getLastMessage(){
        return $this->msg;
    }  
    
    public function getPageScripts(){
        $requiredLibs = viciCommon::jqueryInclude();
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
        $form = '';
        // show form only if no data processed
        if (! $this->dataProcessed) {
            
            $lineKindsObj = new LineKinds($this->lang); 
    
            $licensesObj = new Licenses($this->lang, true); 
    
            $form = <<<HERE
            <h2>Add a tracing</h2>
            <p>Upload a KML file to add one or more line tracings for this marker. All existing traces attached to this marker will be replaced.</p>
            <form id="uploadform" action="/upload/form.php?format=kml" enctype="multipart/form-data" method="post">
            <fieldset>
            <legend>KML file upload: </legend>
            <label class="label alert" id="filelabel" for="file">File:</label><input class="input alert" id="file" name="file" type="file" />
            <input type="hidden" name="frm_id" value="{$this->object}"> 
            
            <label class="label" for="Type">Line type:</label><select class='input' id='type' name="frm_type">{$lineKindsObj->optionList()}</select>
            
            </fieldset>
            <fieldset>
            <legend>License: </legend>
            <label class="label" for="ownership">Owner / creator:</label><select class="input" id="ownership" name="frm_ownership"><option value="1">I am the creator and owner</option><option value="0">Show more options :</option></select>

            <div id="own" style="margin-top:8px">
            <div class="label top"><input id="agree" style="margin-left:90px" type="checkbox" class="alert" name="frm_agree"></div>
            <div id="agreetext" class="input formtext alert">As the creator and owner of this data I agree to publish it using the <a href='http://creativecommons.org/publicdomain/zero/1.0/'>Creative Commons CC0 Public Domain Dedication</a>.</div>
            </div>

            <div id="other" style="display:none">
            <label class="label alert" for="owner" id="ownerlabel">Owner name:</label><input placeholder="name of the copyright holder (required / optional)" class="input alert" id="owner" name="frm_owner" type="text" />
            <label class="label" for="license">License:</label><select class='input' id='license' name="frm_license">{$licensesObj->optionList()}</select>
            <label class="label" for="title">Attribution:</label><input placeholder="attribute desired by the copyright holder (required / optional)" class="input" id="attribute" name="frm_attribute" type="text" />
            <div style="margin-top:8px">
            <div class="label top"><input id="licensed" style="margin-left:90px" type="checkbox" class="alert" name="frm_licensed"></div><div id="licensedtext" class="input formtext alert">The data was published by the owner using the selected license, or the owner explicitly agreed to have it published on Vici.org using the selected license.</div>
            </div>
            </div>

            <br style="clear:left"/>
            <input style="margin-left:120px;margin-top:8px;" type="submit" value=" Submit " />
            </fieldset>
            </form>
HERE;
        }
        return $form;
    }
    
}