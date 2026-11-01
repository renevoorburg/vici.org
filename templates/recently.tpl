{extends file="base.tpl"}



{block name=main}
    <div class="hr"></div>
    <main class="margin">

    {if count($items) > 0}
        <div class="tilelist">
            <h2>{$search_results_label|default:"Recently added"}</h2>
            <div class="tile-grid">
                {foreach from=$items item=entry}
                    {if $entry.type == 'image'}
                        {include file="include/recent_tile_image.tpl" image=$entry.item}
                    {else}
                        {include file="include/recent_tile_site.tpl" site=$entry.item metaMode=$entry.metaMode|default:'added'}
                    {/if}
                {/foreach}
            </div>
        </div>
    {/if}

    </main>
{/block}
