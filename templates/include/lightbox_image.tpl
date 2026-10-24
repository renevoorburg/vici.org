<a href="//images.vici.org/auto{$image->filepath}"
data-pswp-width="{$image->width|default:1200}"
data-pswp-height="{$image->height|default:900}"
target="_blank"
title="{$image->title}">
    <figure>
        <img src="//images.vici.org/{$imageSizeUrlPrefix}{$image->filepath}"
            alt="{$image->title}"
            data-caption-link="/image/{$image->id}"
            data-caption-license="{$image->license->shortName}"
            data-caption-creator="{if $image->isOwnWork} {$image->uploader->getRealName()}{else}{$image->creator}{/if}"
            >
    </figure>
</a>