<?php

require_once (dirname(__FILE__).'/classDBConnector.php');
require_once (dirname(__FILE__).'/classLang.php');
require_once (dirname(__FILE__).'/classSite.php');
require_once (dirname(__FILE__).'/classViciCommonLogic.php');
require_once (dirname(__FILE__).'/classIntlSiteText.php');

class IntlAnnotations {

    private $annotations, $languageSelector;

    public function __construct(DBConnector $db, site $site, Lang $lngObj) {

        $lang = $lngObj->getLang();
        $nativeText = ViciCommonLogic::parseAnnotation($site->getAnnotation(), $lngObj);
        $nativeTextEmpty = (strlen(trim(strip_tags($nativeText)))<4);
        $longestText = '';
        $longestTextLength = 0;
        $longestTextLang = '';

        $intlSite = new IntlSiteText($db,  $site->getId());
        $this->languageSelector = '<li class="selected langSelLi" id="xt_'.$lngObj->getLang().'"><a href="#" id="t_'.$lngObj->getLang().'" class="langSel">'.$lngObj->str($lngObj->getLang().'_'.$lngObj->getLang()).'</a></li>';
        $this->annotations = '';

        foreach ($lngObj->getAvailableLanguages() as $langCrsr) {
            if ($langCrsr != $lngObj->getLang()) {
                $curText = ViciCommonLogic::parseAnnotation($intlSite->getText($langCrsr), $lngObj);
                $curTextLength = strlen(trim(strip_tags($curText)));
                if ($curTextLength<4) {
                    $this->languageSelector.= '<li class="disabled langSelLi" id="xt_'.$langCrsr.'">'.$lngObj->str($langCrsr.'_'.$lngObj->getLang()).'</li>';
                } else {
                    $this->annotations .= '<div id="txt_'.$langCrsr.'" class="disabled article" lang="' . $langCrsr . '">' . $curText . '</div>';
                    $this->languageSelector.= '<li class="disabled langSelLi" id="xt_'.$langCrsr.'"><a href="#" class="langSel" id="t_'.$langCrsr.'">'.$lngObj->str($langCrsr.'_'.$lngObj->getLang()).'</a></li>';
                    if ($nativeTextEmpty && ($curTextLength > $longestTextLength)) {
                        $longestTextLength =  $curTextLength;
                        $longestText = $curText;
                        $longestTextLang = $langCrsr;
                    }
                }
            }
        }

        if ($nativeTextEmpty) {
            if ($longestTextLength >6) {
                $this->annotations = '<article id="txt_'.$lngObj->getLang().'" class="article">'.
                    '<div class="notice">'.$lngObj->str('no native translation').$lngObj->str($longestTextLang.'_'.$lngObj->getLang()).
                    '.</div><div lang="'.$longestTextLang.'">'.$longestText.'</div></article>'.$this->annotations;
            } else {
                $this->annotations = '<article  style="margin-bottom:18px" id="txt_'.$lngObj->getLang().'" class="article"><div class="notice">'.sprintf($lngObj->str('No annotation available. Please <a href="%s">add information about this place</a>'),$lngObj->langURL($lang, '/edit.php?id='.$site->getId())).'</div></article>'.$this->annotations;
            }
        } else {
            $this->annotations = '<article id="txt_'.$lngObj->getLang().'" class="article">'.$nativeText.'</article>'.$this->annotations;
        }


    }

    public function getAnnotations() {
        return $this->annotations;
    }

    public function getLanguageSelector(){
        return $this->languageSelector;
    }

}