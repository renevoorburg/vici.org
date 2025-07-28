{extends file="base.tpl"}
{block name=main}
    <main>

    {$image->id}
    <br>
    {$image->title}
    <br>
    {$image->description}
    <br>
    {$image->language}
    <br>
    {$image->filepath}
    <br>
    {$image->isPublished}
    <br>
    {$image->uploader->getRealName()}
    <br>
    {$image->isOwnWork}
    <br>
    {$image->source}
    <br>
    {$image->creator}
    <br>
    {$image->license->shortName}
    <br>
    {$image->dateAdded}
    <br>
    {$image->width}
    <br>
    {$image->height}
    <br>
    {$image->md5sum}


    {if $image->sites->count() > 0}
        <br>
        <br>
        <h2>Sites</h2>
        <ul>
            {foreach $image->sites as $site}
                <li>{$site->defaultTitle}</li>
            {/foreach}
        </ul>
    {/if}
    </main>
{/block}