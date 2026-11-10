<div class="tile">
    <a class="tile-imglink" href="/image/{$image->id}">
        <img src="https://images.vici.org/crop/w440xh248{$image->filepath}" class="tile-image" alt="{$image->title|escape}" loading="lazy">
    </a>
    <span class="tile-caption">
        <a class="tile-caption-link" href="/image/{$image->id}"><span class="tile-title">{$image->title|escape:'html'}</span></a>
        {if $image->uploader}
        <a class="tile-sub tile-meta" href="/user/{$image->uploader->getId()}">{$image->dateAdded|date_format:'%d-%m-%Y'} &middot; {$image->uploader->getRealName()|escape:'html'}</a>
        {else}
        <span class="tile-sub tile-meta">{$image->dateAdded|date_format:'%d-%m-%Y'}</span>
        {/if}
    </span>
</div>
