{extends file="base.tpl"}


{block name=headscripts}
    {include file="include/lightbox_init.tpl"}
{/block}

{block name=main}
    <div class="hr"></div>
    <main class="margin">

    <h1>{$image->title}</h1>

    <div id="my-gallery" class="main-image">
        {include file="include/lightbox_image.tpl" image=$image imageSizeUrlPrefix="size/w800"}
    </div>

    {$image->description}
    <table class="image-meta">
        <tr>
            <td>{$creator_label|default:"Creator"}</td>
            <td>{$image->getCreator()}</td>
        </tr>
        {if !$image->isOwnWork && $image->source}
        <tr>
            <td>{$source_label|default:"Source"}</td>
            <td>{if $image->source}<a href="{$image->source}">{$image->source}</a>{/if}</td>
        </tr>
        {/if}
        <tr>
            <td>{$license_label|default:"License"}</td>
            <td>
            {if $image->license->uri}
            <a href="{$image->license->uri}">{$image->license->name}</a>
            {else}
            {$image->license->name}
            {/if}
            </td>
        </tr>
        {if !$image->isOwnWork && $image->attribution}
        <tr>
            <td>{$attribution_label|default:"Attribution"}</td>
            <td>{$image->attribution}</td>
        </tr>
        {/if}
        <tr>
            <td>{$uploader_label|default:"Added by"}</td>
            <td>{$image->uploader->getRealName()}</td>
        </tr>
        <tr>
            <td>{$date_added_label|default:"Date added"}</td>
            <td>{$image->dateAdded}</td>
        </tr>
    </table>

    {if $image->sites->count() > 0}
        <div class="sitelist">
            <h2>{$used_in_label|default:"Used in"}</h2>
            <ul>
                {foreach $image->sites as $site}
                    <li>
                        {include file="include/site_list_item.tpl"}
                    </li>
                {/foreach}
            </ul>
        </div>
    {/if}
    </main>
{/block}