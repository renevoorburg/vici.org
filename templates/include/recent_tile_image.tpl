<a class="tile" href="/image/{$image->id}">
    <img src="https://images.vici.org/crop/w440xh248{$image->filepath}" class="tile-image" alt="{$image->title|escape}" loading="lazy">
    <span class="tile-caption">
        <span class="tile-title">{$image->title|escape:'html'}</span>
        <span class="tile-sub tile-meta">{$image->dateAdded|date_format:'%d-%m-%Y'}{if $image->uploader} &middot; {$image->uploader->getRealName()|escape:'html'}{/if}</span>
    </span>
</a>
