{extends file="base.tpl"}

{block name=main}
    <div class="hr"></div>
    <main class="margin">

    {if !$profile_found}
        <h1>{$user_notfound|default:"User not found."}</h1>
    {else}
    <h1>{$profile_name|escape}</h1>

    {if $profile_bio != ''}
    <p>{$profile_bio|escape:'html'|nl2br}</p>
    {/if}

    {if count($profile_links) > 0}
        <table class="stats-table">
            {foreach $profile_links as $link}
            <tr>
                <td>{$link.label|escape:'html'}</td>
                <td><a class="vici-link" href="{$link.url|escape:'html'}" target="_blank" rel="noopener">{$link.url|escape:'html'}</a></td>
            </tr>
            {/foreach}
        </table>
    {/if}

    {if !$logged_in}
        <p><a class="vici-link" href="/login">{$profile_login_hint|default:"Log in to see the full profile."}</a></p>
    {else}
        {if $viewer_is_self || $is_admin}
        <nav class="edit-menu">
            {if $viewer_is_self}<a href="/profile">{$edit_profile|default:"Edit profile"}</a>{/if}
            <span class="edit-menu-status">{$status_label|default:"Status"}: {$profile_level_label|escape}</span>
        </nav>
        {/if}

        <h2>{$stats_heatmap_title|default:"Contributions in the last year"}</h2>
        <p><strong>{$heatmap_total}</strong> {$heatmap_sub|default:"contributions in the last year"}</p>
        <div class="heatmap-scroll">
            <div class="heatmap">
                {foreach $heatmap_weeks as $week}
                    <div class="hm-week">
                        {foreach $week as $cell}
                            <span class="hm-cell hm-l{$cell.level}" title="{$cell.count} {$hm_on|default:"contribution(s) on"} {$cell.date}"></span>
                        {/foreach}
                    </div>
                {/foreach}
            </div>
            <div class="hm-legend">
                <span>{$hm_less|default:"Less"}</span>
                <span class="hm-cell hm-l0"></span><span class="hm-cell hm-l1"></span><span class="hm-cell hm-l2"></span><span class="hm-cell hm-l3"></span><span class="hm-cell hm-l4"></span>
                <span>{$hm_more|default:"More"}</span>
            </div>
        </div>

        {if count($contrib_sites) > 0 || count($contrib_images) > 0}
        <h2>{$contrib_label|default:"Recent contributions"}</h2>
        <div class="tile-grid">
            {foreach $contrib_sites as $site}
                {include file="include/recent_tile_site.tpl" site=$site}
            {/foreach}
            {foreach $contrib_images as $image}
                {include file="include/recent_tile_image.tpl" image=$image}
            {/foreach}
        </div>
        {/if}

    {/if}
    {/if}

    </main>
{/block}
