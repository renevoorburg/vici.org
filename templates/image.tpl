{extends file="base.tpl"}


{block name=headscripts}
    {include file="include/lightbox_init.tpl"}
{/block}

{block name=main}
    <div class="hr"></div>
    <main class="margin">

    <h1>{$image->title|escape:'html'}</h1>

    <div id="my-gallery" class="main-image">
        {include file="include/lightbox_image.tpl" image=$image imageSizeUrlPrefix="size/w800"}
    </div>

    {$image->description|escape:'html'}
    <table class="image-meta">
        <tr>
            <td>{$creator_label|default:"Creator"}</td>
            <td>{$image->getCreator()|escape:'html'}</td>
        </tr>
        {if !$image->isOwnWork && $image->source}
        <tr>
            <td>{$source_label|default:"Source"}</td>
            <td>{if $image->getSafeSource() ne ''}<a href="{$image->getSafeSource()|escape:'html'}" target="_blank" rel="noopener">{$image->getSafeSource()|escape:'html'}</a>{else}{$image->source|escape:'html'}{/if}</td>
        </tr>
        {/if}
        <tr>
            <td>{$license_label|default:"License"}</td>
            <td>
            {if $image->license && $image->license->uri}
            <a href="{$image->license->uri|escape:'html'}">{$image->license->name|escape:'html'}</a>
            {else}
            {if $image->license}{$image->license->name|escape:'html'}{/if}
            {/if}
            </td>
        </tr>
        {if !$image->isOwnWork && $image->attribution}
        <tr>
            <td>{$attribution_label|default:"Attribution"}</td>
            <td>{$image->attribution|escape:'html'}</td>
        </tr>
        {/if}
        <tr>
            <td>{$uploader_label|default:"Added by"}</td>
            <td>{if $image->uploader}<a href="/user/{$image->uploader->getId()}">{$image->uploader->getRealName()|escape:'html'}</a>{/if}</td>
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