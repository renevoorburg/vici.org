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

        // hidden sites are only visible to admins
        $isAdmin = $session->hasUser() && $session->getUser()->isAdmin();

        // admin-only visibility toggle (POST to avoid accidental triggers via prefetch/links)
        if ($isAdmin && $site !== null && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_hidden'])
            && $session->isCsrfTokenValid($_POST['csrf_token'] ?? null)) {
            $siteRepo->setHidden($site->id, $site->isPublished);
            header('Location: /vici/' . $site->id);
            exit;
        }
        $this->assign('is_admin', $isAdmin);
        if ($site === null || (!$site->isPublished && !$isAdmin)) {
            http_response_code(404);
            $this->setTemplate('notfound.tpl');
            $this->assign('title', $this->session->translator->get('Site not found.'));
            $this->assign('notfound_message', $this->session->translator->get('Site not found.'));
            return;
        }
        $this->assign('site_is_hidden', !$site->isPublished);

        $nearbySites = $siteRepo->findNearbySites($site->representativeLocation->latitude, $site->representativeLocation->longitude);
        $relevantMuseums = $siteRepo->findRelevantMuseums($site->representativeLocation->latitude, $site->representativeLocation->longitude);

        $preferredLocaleLanguage = $site->locales->preferredLocaleLanguage($session->getLanguage());

        $this->assign('mainSite', $site);
        if ($isAdmin) {
            // hidden linked images, so admins can find and unhide them
            $imageRepo = new \Vici\Model\Site\Image\ImageRepository($db);
            $this->assign('hidden_images', $imageRepo->findHiddenBySite($site->id));
        }
        $this->assign('preferredLocaleLanguage', $preferredLocaleLanguage);
        $this->assign('nearbySites', $nearbySites);
        $this->assign('relevantMuseums', $relevantMuseums);
        $this->assign('site_type', $site->type);
        $this->assign('classification_description', $this->session->translator->get("markerdef." . $site->type->id));  
        $this->assign('classification_title', $this->session->translator->get($site->type->title));

        $locale = $site->locales[$preferredLocaleLanguage] ?? null;
        $siteTitle = $locale?->title ?? $site->locales->getFirstTitle();

        $sessionLanguage = $this->session->getLanguage();
        $placeName = $site->toponym->placeName[$sessionLanguage] ?? '';
        $countryName = $site->toponym->countryName[$sessionLanguage] ?? '';
        $placeLabel = $placeName !== '' ? $placeName : $countryName;
        $docTitle = $siteTitle . ($placeLabel !== '' ? ', ' . $placeLabel : '');

        $metaDescription = $locale?->summary ?? '';
        if ($metaDescription === '') {
            $metaDescription = trim((string)preg_replace('/\s+/', ' ',
                html_entity_decode(strip_tags($locale?->description ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
        }
        $metaDescription = mb_strimwidth($metaDescription, 0, 160, '');
        if ($metaDescription === '') {
            $metaDescription = $docTitle . ' — Vici.org';
        }

        $availableLangs = $this->session->getAvailableLanguages();

        // Canonical reflects the language of the displayed description, not the UI language.
        $contentLang = $preferredLocaleLanguage;
        if ($contentLang === '' || !in_array($contentLang, $availableLangs, true)) {
            // Fall back to the UI language, which always leads to the same displayed content.
            $contentLang = in_array($sessionLanguage, $availableLangs, true)
                ? $sessionLanguage
                : ($availableLangs[0] ?? 'en');
        }

        $canonicalUrl = 'https://vici.org/vici/' . $site->id . '?lang=' . $contentLang;

        $hreflangAlternates = [];
        foreach ($availableLangs as $lang) {
            $loc = $site->locales->getByLanguage($lang);
            if ($loc !== null && !empty($loc->description)) {
                $hreflangAlternates[] = [
                    'lang' => $lang,
                    'url'  => 'https://vici.org/vici/' . $site->id . '?lang=' . $lang,
                ];
            }
        }

        // x-default: English if present, otherwise first available content language.
        $defaultLang = null;
        foreach ($hreflangAlternates as $alt) {
            if ($alt['lang'] === 'en') {
                $defaultLang = 'en';
                break;
            }
        }
        if ($defaultLang === null && $hreflangAlternates !== []) {
            $defaultLang = $hreflangAlternates[0]['lang'];
        }
        if ($defaultLang !== null) {
            $hreflangAlternates[] = [
                'lang' => 'x-default',
                'url'  => 'https://vici.org/vici/' . $site->id . '?lang=' . $defaultLang,
            ];
        }

        $ogImage = null;
        if ($site->images->count() > 0) {
            $ogImage = 'https://images.vici.org/auto' . $site->images[0]->filepath;
        }

        $sameAs = [];
        foreach ($site->identifiers as $identifier) {
            if (preg_match('#^https?://#', $identifier->uri)) {
                $sameAs[] = $identifier->uri;
            }
        }

        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'Place',
            'name' => $siteTitle,
            'url' => $canonicalUrl,
        ];
        if ($metaDescription !== '') {
            $jsonLd['description'] = $metaDescription;
        }
        $jsonLd['geo'] = [
            '@type' => 'GeoCoordinates',
            'latitude' => $site->representativeLocation->latitude,
            'longitude' => $site->representativeLocation->longitude,
        ];
        if ($ogImage !== null) {
            $jsonLd['image'] = $ogImage;
        }
        if ($sameAs !== []) {
            $jsonLd['sameAs'] = $sameAs;
        }

        $this->assign('title', $docTitle);
        $this->assign('meta_description', $metaDescription);
        $this->assign('canonical_url', $canonicalUrl);
        $this->assign('hreflang_alternates', $hreflangAlternates);
        $this->assign('og_image', $ogImage);
        $jsonLdJson = json_encode($jsonLd,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE);
        if ($jsonLdJson === false) {
            $jsonLdJson = '{}';
        }
        $this->assign('jsonld', $jsonLdJson);

    }

    public static function link_urls($text) {
        // based on http://stackoverflow.com/questions/1188129/replace-urls-in-text-with-html-links
        $rexProtocol = '(https?://)';
        $rexDomain   = '((?:[-a-zA-Z0-9]{1,63}\.)+[-a-zA-Z0-9]{2,63}|(?:[0-9]{1,3}\.){3}[0-9]{1,3})';
        $rexPort     = '(:[0-9]{1,5})?';
        $rexPath     = '(/[!$-/0-9:;=@_\':;!a-zA-Z\x7f-\xff]*?)?';
        $rexQuery    = '(\?[!$-/0-9:;=@_\':;!a-zA-Z\x7f-\xff\|]+?)?';
        $rexFragment = '(#[!$-/0-9:;=@_\':;!a-zA-Z\x7f-\xff\|]+?)?';

        return preg_replace_callback("&$rexProtocol$rexDomain$rexPort$rexPath$rexQuery$rexFragment(?=[?.!,;:\"]?(\s|$|((</)[^aA])))&",
            function ($match) {
                return '<a href="' . $match[0] . '">'. $match[1] . $match[2] . $match[3] . $match[4] . $match[5] . '</a>';
            }, $text);
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

        $html = self::link_urls($html);
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
                $refs .= '<li><a class="siteref" href="#cite_ref-' . $count . '">↑</a> <a href="' . $node->attributes->getNamedItem('href')->nodeValue . '">' . $anchorText . '</a></li>';
            } else {
                $refs .= '<li><a class="siteref" href="#cite_ref-' . $count . '">↑</a>' . $anchorText . '</li>';
            }

            // replace node with a reference link:
            $refLink = $annotation->createElement('sup');
            $refLink->setAttribute('id', '#cite_note-' . $count);
            $refAnchor = $annotation->createElement('a', (string)$count);
            $refAnchor->setAttribute('href', '#cite_note-' . $count);
            $refAnchor->setAttribute('class', 'refanchor');
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