<?php

namespace Vici\Page\Pages;

use Vici\Page\PageRenderer;
use Vici\Session\Session;
use DOMDocument;
use DOMXPath;


class ItemPage extends PageRenderer
{

    private Session $session;
    private string $template = 'item.tpl';

    public function __construct(Session $session)
    {
        $this->session = $session;
        parent::__construct($this->template, $session);
        // Register view-layer formatter only for this page; closure to access $this->session
        $this->registerPlugin('modifier', 'description_as_html', function (string $description) {
            return $this->descriptionAsHtml($description);
        });
        $this->assignTranslatedTemplateVars($this->template);
        $this->assign('js_translations', $this->session->translator->getTranslationsJson(['more', 'show on map'], 'markerdef.'));

        $db = $session->getDBConnector();
        $siteRepo = new \Vici\Model\Site\SiteRepository($db);
        $site = $siteRepo->findById($session->getRequestedItem());

        $nearbySites = $siteRepo->findNearbySites($site->representativeLocation->latitude, $site->representativeLocation->longitude);
        $relevantMuseums = $siteRepo->findRelevantMuseums($site->representativeLocation->latitude, $site->representativeLocation->longitude);

        $preferredLocaleLanguage = $site->locales->preferredLocaleLanguage($session->getLanguage());

        $this->assign('mainSite', $site);
        $this->assign('preferredLocaleLanguage', $preferredLocaleLanguage);
        $this->assign('nearbySites', $nearbySites);
        $this->assign('relevantMuseums', $relevantMuseums);
        $this->assign('classification_description', $this->session->translator->get("markerdef." . $site->type->id));  
        $this->assign('classification_title', $this->session->translator->get($site->type->title));

    }

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
            $refAnchor = $annotation->createElement('a', (string)$count);
            $refAnchor->setAttribute('href', '#cite_note-' . $count);
            $refLink->appendChild($refAnchor);
            $node->parentNode->replaceChild($refLink, $node);

            $count++;
        }
        $refsTitle = $this->session->translator->get('References');
        $refs = ($refs) ? '<h2>' . $refsTitle . '</h2><p><ol>' . $refs . '</ol></p>' : '';
        return str_replace(['<body>', '</body>'], '',
            $annotation->saveHTML($annotation->getElementsByTagName('body')->item(0))) . $refs;
    }

}