<?php

namespace Vici\Negotiator;

const LANG_PARAM_NAME = 'lang';

class LanguageNegotiator extends Negotiator {
    protected $headerName = 'HTTP_ACCEPT_LANGUAGE';
    protected $urlParamName = LANG_PARAM_NAME;

    public static function hasForcedLanguage() {
        return !empty($_GET[LANG_PARAM_NAME]);
    }
}