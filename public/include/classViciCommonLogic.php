<?php

/**
 * Class ViciCommonLogic. Common generic functions for Vici that do require a db connection.
 */

require_once __DIR__ . '/classViciCommon.php';

class ViciCommonLogic extends ViciCommon
{

    /**
     * Determines what site (id) was requested through a GET.
     * @param DBConnector $db
     * @param $idStr
     * @param string $lang
     * @return int
     * @throws Exception
     */
    public static function getSiteId(DBConnector $db, $idStr, $lang = 'en') : int
    {
        $idArr = explode("/", $idStr);
        if ((string)(int)$idArr[0] == (string)$idArr[0]) {
            // identifier is a number
           $id = (int)$idArr[0];
        } else {
            parent::terminateWith404();
        }
        return $id;
    }

    /**
     * @param array $acceptable
     * @param string $default
     * @return string format
     */
    private static function getAcceptFormat($acceptable, $default = 'html') : string
    {
        $accept = strtolower(str_replace(' ', '', $_SERVER['HTTP_ACCEPT']));
        $last = 99;
        $ret = $default;
        foreach ($acceptable as $current) {
            $prio = strpos($accept, $current) ? strpos($accept, $current) : 99;
            if ($prio < $last) {
                $last = $prio;
                $ret = $current;
            }
        }
        return $ret;
    }

    /**
     * Does redirect based on content nego. and ensures uses of https
     * @param $idStr
     * @return string is request plain html or rdf or kml?
     */
    public static function matchRequestedContentType($idStr) : string
    {
        $acceptable =array('html', 'rdf', 'kml');
        $default = 'html';
        $idArr = explode("/", $idStr);
        $accept_format = self::getAcceptFormat($acceptable);

        if (!isset($idArr[1]) || ($idArr[1] === $default) ) {
            // do a redirect based on accept headers
            $format = ($accept_format === $default) ? '' : $accept_format;
            header('Location: ' . parent::getSiteBase() . '/vici/' . $idArr[0] . '/' . $format, true, 303);
            exit;
        }
        if (!in_array($idArr[1], $acceptable) && !empty($idArr[1]) ) {
            parent::terminateWith404();
        }
        return in_array($idArr[1], $acceptable) ? $idArr[1] : $default;
    }

    /**
     * Converts HTML as with inline <cite>references to HTML with a separate refs. list.
     * @param $html string as stored in the db, references in the text.
     * @param $lngObj Lang used for translations.
     * @return string HTML with a separate list of references.
     */
    public static function parseAnnotation($html, $lngObj)
    {
        if (empty($html)) {
            return $html;
        }

        // clean utf-8, based on https://webcollab.sourceforge.io/unicode.html
        $html = preg_replace('/[\x00-\x08\x10\x0B\x0C\x0E-\x19\x7F]'.
            '|(?<=^|[\x00-\x7F])[\x80-\xBF]+'.
            '|([\xC0\xC1]|[\xF0-\xFF])[\x80-\xBF]*'.
            '|[\xC2-\xDF]((?![\x80-\xBF])|[\x80-\xBF]{2,})'.
            '|[\xE0-\xEF](([\x80-\xBF](?![\x80-\xBF]))|(?![\x80-\xBF]{2})|[\x80-\xBF]{3,})/',
            '�', $html);
        $html = preg_replace('/\xE0[\x80-\x9F][\x80-\xBF]'.
            '|\xED[\xA0-\xBF][\x80-\xBF]/S','?', $html);

        $html = preg_replace("/&(?!\S+;)/", "&amp;", $html);

        // still loadhtml complains.. , so keep silent:
        libxml_use_internal_errors(true);
        $annotation = new DOMDocument();
        $annotation->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />' . $html);

        $xpathsearch = new DOMXPath($annotation);
        $nodes = $xpathsearch->query("//cite[contains(@class,'reference')]");

        $refs = '';
        $count = 1;
        foreach ($nodes as $node) {
            // create a list with references:
            preg_match('/\[(.*)\]/', $node->nodeValue, $matches);
            $anchorText = isset($matches[1]) ? $matches[1] : '';
            if (is_object($node) && is_object($node->attributes) && is_object($node->attributes->getNamedItem('href')) && $node->attributes->getNamedItem('href')->nodeValue) {
                $refs .= '<li><a href="#cite_ref-' . $count . '">↑</a><a href="' . $node->attributes->getNamedItem('href')->nodeValue . '">' . $anchorText . '</a></li>';
            } else {
                $refs .= '<li><a href="#cite_ref-' . $count . '">↑</a>' . $anchorText . '</li>';
            }

            // replace node with a reference link:
            $refLink = $annotation->createElement('sup');
            $refLink->setAttribute('id', '#cite_note-' . $count);
            $refAnchor = $annotation->createElement('a', $count);
            $refAnchor->setAttribute('href', '#cite_note-' . $count);
            $refLink->appendChild($refAnchor);
            $node->parentNode->replaceChild($refLink, $node);

            $count++;
        }
        $refs = ($refs) ? '<h2>' . $lngObj->str('References') . '</h2><p><ol>' . $refs . '</ol></p>' : '';
        return str_replace(array('<body>', '</body>'), '',
            $annotation->saveHTML($annotation->getElementsByTagName('body')->item(0))) . $refs;
    }


}

