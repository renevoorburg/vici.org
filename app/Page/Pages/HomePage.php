<?php

namespace Vici\Page\Pages;

use Vici\Page\PageRenderer;
use Vici\Session\Session;

class HomePage extends PageRenderer
{

    private Session $session;
    private string $template = 'home.tpl';

    public function __construct(Session $session)
    {
        $this->session = $session;
        parent::__construct($this->template, $session);
        $this->assignTranslatedTemplateVars($this->template);
        $this->assign('js_translations', $this->session->translator->getTranslationsJson(['more', 'show on map', 'Close', 'more info'], 'markerdef.'));
        $this->assign('title', $this->session->translator->get('archaeological atlas of antiquity'));
        $metaDescription = $this->session->translator->get('Interactive archaeological atlas of antiquity: explore hundreds of thousands of archaeological sites, monuments and finds on an interactive map.');
        $this->assign('meta_description', $metaDescription);

        $jsonLd = [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'WebSite',
                    '@id' => 'https://vici.org/#website',
                    'url' => 'https://vici.org/',
                    'name' => 'Vici.org',
                    'alternateName' => $this->session->translator->get('archaeological atlas'),
                    'description' => $metaDescription,
                    'inLanguage' => $this->session->getLanguage(),
                    'publisher' => ['@id' => 'https://vici.org/#organization'],
                    'potentialAction' => [
                        '@type' => 'SearchAction',
                        'target' => [
                            '@type' => 'EntryPoint',
                            'urlTemplate' => 'https://vici.org/search?q={search_term_string}',
                        ],
                        'query-input' => 'required name=search_term_string',
                    ],
                ],
                [
                    '@type' => 'Organization',
                    '@id' => 'https://vici.org/#organization',
                    'name' => 'Vici.org',
                    'url' => 'https://vici.org/',
                    'logo' => 'https://vici.org/icon-192.png',
                    'sameAs' => [
                        'https://archaeo.social/@vici',
                        'https://github.com/renevoorburg/vici.org',
                    ],
                ],
            ],
        ];
        $jsonLdJson = json_encode($jsonLd,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE);
        if ($jsonLdJson === false) {
            $jsonLdJson = '{}';
        }
        $this->assign('jsonld', $jsonLdJson);
    }

}