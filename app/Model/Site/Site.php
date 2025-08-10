<?php

namespace Vici\Model\Site;

use Vici\Model\Site\SiteType;
use Vici\Model\Site\Locale\LocaleCollection;
use Vici\Model\Site\Point;
use Vici\Model\Site\Toponym;
use Vici\Model\Site\Period;
use Vici\Model\Site\Image\ImageCollection;
use Vici\Model\User\User;
use Vici\Model\Site\Identifier\Identifier;
use Vici\Model\Site\Identifier\IdentifierCollection;
use Vici\Model\Site\Line\LineCollection;
use DOMDocument;
use DOMXPath;

class Site
{
    public int $id;
    public string $defaultTitle;
    public string $defaultSummary;
    public SiteType $type;
    public Point $representativeLocation;
    public LineCollection $lines;
    public LocaleCollection $locales;
    public ImageCollection $images;
    public Toponym $toponym;
    public Period $period;
    public User $creator;
    public User $updater;
    public string $createDate;
    public string $updateDate;
    public IdentifierCollection $identifiers;
    public bool $isVisible = true;
    public bool $isPublished = true;


    public function descriptionAsHtml(string $description): string
    {
        $html = $description;

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
        $refs = ($refs) ? '<h2>' . 'References' . '</h2><p><ol>' . $refs . '</ol></p>' : '';
        return str_replace(array('<body>', '</body>'), '',
            $annotation->saveHTML($annotation->getElementsByTagName('body')->item(0))) . $refs;
    }


}

