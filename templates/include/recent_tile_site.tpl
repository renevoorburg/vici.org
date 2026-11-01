{assign var=thumb value=''}
{foreach $site->images as $siteImage}
    {if $thumb == ''}{assign var=thumb value=$siteImage->filepath}{/if}
{/foreach}
{assign var=siteTitle value=(isset($site->locales[$sessionLanguage]->title) && $site->locales[$sessionLanguage]->title ne '') ? $site->locales[$sessionLanguage]->title : $site->defaultTitle}
{assign var=siteLoc value=$site->toponym->countryName[$sessionLanguage]|default:''}
{if $site->toponym->placeName[$sessionLanguage]|default:'' ne ''}{assign var=siteLoc value="{$siteLoc}, {$site->toponym->placeName[$sessionLanguage]}"}{/if}
{if $metaMode|default:'added' == 'changed'}
{assign var=siteMeta value="{$site->updateDate|date_format:'%d-%m-%Y'}{if $site->updater} &middot; {$site->updater->getRealName()}{/if}"}
{else}
{assign var=siteMeta value="{$site->createDate|date_format:'%d-%m-%Y'}{if $site->creator} &middot; {$site->creator->getRealName()}{/if}"}
{/if}
<a class="tile tile-site{if $thumb != ''} tile-has-img{/if}" href="/vici/{$site->id}">
    {if $thumb != ''}
    <img src="https://images.vici.org/crop/w440xh248{$thumb}" class="tile-image" alt="{$siteTitle|escape}" loading="lazy">
    {/if}
    <span class="marker-icon" data-icon-type="{$site->type->id}"></span>
    <span class="tile-title">{$siteTitle}</span>
    <span class="tile-sub tile-loc">{$siteLoc}</span>
    <span class="tile-sub tile-meta">{$siteMeta}</span>
</a>
