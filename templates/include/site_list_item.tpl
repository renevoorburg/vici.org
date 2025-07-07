<span class="marker-row">
    <div class="marker-icon" data-icon-type="{$site->type->id}"></div>
    <span class="marker-text">
        <a href="/vici/{$site->id}">{if isset($site->locales[$sessionLanguage]->title) && $site->locales[$sessionLanguage]->title ne ''}{$site->locales[$sessionLanguage]->title}{else}{$site->defaultTitle}{/if}</a>
        <br>
        {if isset($site->locales[$sessionLanguage]->summary) && $site->locales[$sessionLanguage]->summary ne ''}{$site->locales[$sessionLanguage]->summary}{else}{$site->defaultSummary}{/if}
    </span>
</span>
