{assign var=thumb value=$site->thumbPath|default:''}
{assign var=siteTitle value=(isset($site->locales[$sessionLanguage]->title) && $site->locales[$sessionLanguage]->title ne '') ? $site->locales[$sessionLanguage]->title : $site->defaultTitle}
{assign var=siteSum value=(isset($site->locales[$sessionLanguage]->summary) && $site->locales[$sessionLanguage]->summary ne '') ? $site->locales[$sessionLanguage]->summary : $site->defaultSummary}
{assign var=siteCountry value=$site->toponym->countryName[$sessionLanguage]|default:''}
{assign var=sitePlace value=$site->toponym->placeName[$sessionLanguage]|default:''}
{if $siteCountry ne '' && $sitePlace ne ''}{assign var=siteLoc value="{$siteCountry}, {$sitePlace}"}{else}{assign var=siteLoc value="{$siteCountry}{$sitePlace}"}{/if}
{if ($metaMode|default:'added') == 'changed'}
{assign var=metaUser value=$site->updater}
{assign var=metaDate value=$site->updateDate|date_format:'%d-%m-%Y'}
{else}
{assign var=metaUser value=$site->creator}
{assign var=metaDate value=$site->createDate|date_format:'%d-%m-%Y'}
{/if}
<div class="tile tile-site{if $thumb != ''} tile-has-img{/if}">
    {if $thumb != ''}
    <img src="https://images.vici.org/crop/w440xh248{$thumb}" class="tile-image" alt="{$siteTitle|escape}" loading="lazy">
    {/if}
    <a class="tile-main" href="/vici/{$site->id}">
        <span class="marker-icon" data-icon-type="{$site->type->id}"></span>
        <span class="tile-title">{$siteTitle|escape:'html'}</span>
        <span class="tile-desc">{$siteSum|escape:'html'}</span>
        <span class="tile-sub tile-loc">{$siteLoc|escape:'html'}</span>
    </a>
    {if $metaUser}
    <a class="tile-sub tile-meta" href="/user/{$metaUser->getId()}">{$metaDate} &middot; {$metaUser->getRealName()|escape:'html'}</a>
    {else}
    <span class="tile-sub tile-meta">{$metaDate}</span>
    {/if}
</div>
