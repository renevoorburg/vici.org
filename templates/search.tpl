{extends file="base.tpl"}

{block name=stylesheets}
    <style>

    </style>
{/block}


{block name=main}
    <div class="hr"></div>
    <main>

    search work in progress

    {if $sites->count() > 0}
        <div class="sitelist">
            <h2>{$nearby_sites_label|default:"Nearby sites"}</h2>
            <ul>
                {foreach from=$sites item=site}
                    <li>
                        {include file="include/site_list_item.tpl"}
                    </li>
                {/foreach}
            </ul>
        </div>
    {/if}

    </main>
{/block}    

