<div class="marker-row">
    <span class="marker-icon" data-icon-type="{$site->type->id}"></span>
    <span class="marker-text">
        <a href="/vici/{$site->id}">{if isset($site->locales[$sessionLanguage]->title) && $site->locales[$sessionLanguage]->title ne ''}{$site->locales[$sessionLanguage]->title}{else}{$site->defaultTitle}{/if}</a>
        <br>
        {if isset($site->locales[$sessionLanguage]->summary) && $site->locales[$sessionLanguage]->summary ne ''}{$site->locales[$sessionLanguage]->summary}{else}{$site->defaultSummary}{/if}
        <br>
        <span class="toponym">{$site->toponym->countryName[$sessionLanguage]}{if $site->toponym->placeName[$sessionLanguage]}, {$site->toponym->placeName[$sessionLanguage]}{/if}</span>
    </span>
</div>
