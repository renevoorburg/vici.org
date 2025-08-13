{extends file="base.tpl"}

{block name=stylesheets}
    <style>

    .sitelistmetadata {
        font-size: 0.8em; 
        color: #6b7280; 
        margin-bottom: 0.25em;
}

    </style>
{/block}


{block name=main}
    <div class="hr"></div>
    <main class="margin">

    {if $sites->count() > 0}
        <div class="sitelist">
            <h2>{$search_results_label|default:"Results"}</h2>
            <ul>
                {foreach from=$sites item=site}
                    <li>
                        {include file="include/site_list_item_extended.tpl"}
                    </li>
                {/foreach}
            </ul>
        </div>
    {/if}

    </main>
{/block}  