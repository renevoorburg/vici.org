<span class="marker-row">
    <div class="marker-icon" data-icon-type="{$site->type->id}"></div>
    <span class="marker-text">
        <a href="/vici/{$site->id}">{if isset($site->locales[$sessionLanguage]->title) && $site->locales[$sessionLanguage]->title ne ''}{$site->locales[$sessionLanguage]->title}{else}{$site->defaultTitle}{/if}</a>
        <br>
        {if isset($site->locales[$sessionLanguage]->summary) && $site->locales[$sessionLanguage]->summary ne ''}{$site->locales[$sessionLanguage]->summary}{else}{$site->defaultSummary}{/if}
        
            <div style="font-size: 0.8em; color: #6b7280;">
        {if $site->updateDate ne $site->createDate}
            {$last_edit_date_label|default:"Last edit"} {$site->updateDate|date_format:"%Y-%m-%d"} {$by_label|default:"by"} {$site->updater->getRealName()}
        {/if}
        <br>
        {$site_create_date_label|default:"Date added"} {$site->createDate|date_format:"%Y-%m-%d"} {$by_label|default:"by"} {$site->creator->getRealName()}
            </div>
    </span>
</span>
