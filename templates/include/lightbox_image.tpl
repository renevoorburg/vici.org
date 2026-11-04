<a href="//images.vici.org/auto{$image->filepath|escape:'html'}"
data-pswp-width="{$image->width|default:1200}"
data-pswp-height="{$image->height|default:900}"
target="_blank"
title="{$image->title|escape:'html'}">
    <figure>
        <img src="//images.vici.org/{$imageSizeUrlPrefix}{$image->filepath|escape:'html'}"
            alt="{$image->title|escape:'html'}"
            data-caption-link="/image/{$image->id}"
            data-caption-license="{if $image->license}{$image->license->shortName|escape:'html'}{/if}"
            data-caption-creator="{if $image->isOwnWork} {if $image->uploader}{$image->uploader->getRealName()|escape:'html'}{/if}{else}{$image->creator|escape:'html'}{/if}"
            >
    </figure>
</a>
