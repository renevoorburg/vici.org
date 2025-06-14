<?php

class Lang
{
    private $availableLanguages = array("en", "de", "fr", "nl");
    private $lang;
    private $browserLang;
    private $langGET; // to keep the 'lang=' part of a request
    private $stringsArr;

    public function __construct()
    {
        $this->selectLang();
        // load translation data:
        $stringsArr = array ();
        include (dirname(__FILE__)."/../lang/lang_".$this->lang.'.php');
        $this->stringsArr = $stringsArr;
    }
    
    private function selectLang() 
    {

        # Get the list of acceptable languages
        # or use default
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $acceptlang = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
            for ($i = 0; $i < count($acceptlang);  $i++) {
                $Lang = explode(';', $acceptlang[$i]);
                $acceptlang[$i] = trim($Lang[0]);
            }
        } else {
            $acceptlang = $this->availableLanguages;
        }
        for ($i = 0; $i < count($acceptlang);  $i++) {
            $Lang_split = explode('-', $acceptlang[$i]);
            $Lang_pre = trim($Lang_split[0]);
            if (in_array($Lang_pre, $this->availableLanguages)) {
                $browserLang = $Lang_pre;
                $i = count($acceptlang)+1;
            }
        }
        if (isset($browserLang)) {
            $this->browserLang = $browserLang;
        }

        // language specific url?
        if (isset($_GET['lang']) && !empty($_GET['lang']) && in_array($_GET['lang'], $this->availableLanguages, true)) {
            $this->lang = $_GET['lang'];
            $this->langGET = "lang=".$_GET['lang'];
        } elseif (isset($this->browserLang) && !empty($this->browserLang) && in_array($this->browserLang, $this->availableLanguages, true)) {
            $this->lang = $browserLang;
        } else {
            $this->lang = "en";
        }   
    } // selectLang

    public function getAvailableLanguages() {
        return $this->availableLanguages;
    }

    public function getLang() 
    {
        return $this->lang;
    }
    
    public function getLangGET ($separator)
    {
        if ($this->langGET) {
            return $separator.$this->langGET;
        } else {
            return '';
        }
    }
    
    public function langURL($selectedLang, $url = NULL) 
    // returns the current URL / of given URL for the given $language
    // taken the browser language into consideration
    {
        if (empty($url)) $url=$_SERVER['REQUEST_URI'];
    
        // strip any 'lang' parameter
        $url = preg_replace('/&lang=../', '', $url);
        $url = preg_replace('/\?lang=..&/', '?', $url);
        $url = preg_replace('/\?lang=../', '', $url);
    
   
        if ($this->browserLang != $selectedLang) {

//            echo $this->browserLang." ".$selectedLang."\n";

            // add new 'lang' parameter
            if (preg_match('/\?.*=/', $url)) {
                $url=$url."&lang=$selectedLang";
            } else {
                $url=$url."?lang=$selectedLang";
            }
        }
        return ($url);
    }
    
    public function tr($originalStr) 
    {
        echo $this->str($originalStr);
    }
    
    public function str($originalStr) 
    {
        if(isset($this->stringsArr[$originalStr])) {
            return $this->stringsArr[$originalStr];
        } else {
            return $originalStr;
        }
    }

}

