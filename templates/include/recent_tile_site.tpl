{assign var=thumb value=$site->thumbPath|default:''}
{assign var=siteTitle value=(isset($site->locales[$sessionLanguage]->title) && $site->locales[$sessionLanguage]->title ne '') ? $site->locales[$sessionLanguage]->title : $site->defaultTitle}
{assign var=siteSum value=(isset($site->locales[$sessionLanguage]->summary) && $site->locales[$sessionLanguage]->summary ne '') ? $site->locales[$sessionLanguage]->summary : $site->defaultSummary}
{assign var=siteCountry value=$site->toponym->countryName[$sessionLanguage]|default:''}
{assign var=sitePlace value=$site->toponym->placeName[$sessionLanguage]|default:''}
{if $siteCountry ne '' && $sitePlace ne ''}{assign var=siteLoc value="{$siteCountry}, {$sitePlace}"}{else}{assign var=siteLoc value="{$siteCountry}{$sitePlace}"}{/if}
{if ($metaMode|default:'added') == 'changed'}
{assign var=siteMeta value="{$site->updateDate|date_format:'%d-%m-%Y'}{if $site->updater} &middot; {$site->updater->getRealName()|escape:'html'}{/if}"}
{else}
{assign var=siteMeta value="{$site->createDate|date_format:'%d-%m-%Y'}{if $site->creator} &middot; {$site->creator->getRealName()|escape:'html'}{/if}"}
{/if}
<a class="tile tile-site{if $thumb != ''} tile-has-img{/if}" href="/vici/{$site->id}">
    {if $thumb != ''}
    <img src="https://images.vici.org/crop/w440xh248{$thumb}" class="tile-image" alt="{$siteTitle|escape}" loading="lazy">
    {/if}
    <span class="marker-icon" data-icon-type="{$site->type->id}"></span>
    <span class="tile-title">{$siteTitle|escape:'html'}</span>
    <span class="tile-desc">{$siteSum|escape:'html'}</span>
    <span class="tile-sub tile-loc">{$siteLoc|escape:'html'}</span>
    <span class="tile-sub tile-meta">{$siteMeta}</span>
</a>
