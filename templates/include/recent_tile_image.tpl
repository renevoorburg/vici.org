<a class="tile" href="/image/{$image->id}">
    <img src="https://images.vici.org/crop/w440xh248{$image->filepath}" class="tile-image" alt="{$image->title|escape}" loading="lazy">
    <span class="tile-caption">
        <span class="tile-title">{$image->title}</span>
        <span class="tile-sub">{$image->dateAdded|date_format:'%d-%m-%Y'} &middot; {$image->uploader->getRealName()}</span>
    </span>
</a>
