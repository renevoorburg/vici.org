{extends file="base.tpl"}


{block name=main}
    <div class="hr"></div>
    <main class="margin">

    <h1>{$statistics_title|default:"Statistics"}</h1>
    <p>{$statistics_intro|default:"Your contributions as"} <strong>{$stats_display_name|escape:'html'}</strong></p>

    <table class="stats-table">
        <thead>
            <tr>
                <th>{$stats_type_label|default:"Contribution"}</th>
                <th>{$stats_count_label|default:"Count"}</th>
                <th>{$stats_rank_label|default:"Rank"}</th>
            </tr>
        </thead>
        <tbody>
            {foreach $rows as $row}
            <tr>
                <td>{$row.label}<span class="stats-note">{$row.note}</span></td>
                <td class="stats-num">{$row.stats.count}</td>
                <td class="stats-num">
                    {if $row.stats.rank !== null}
                        #{$row.stats.rank} <span class="stats-note">{$stats_of_label|default:"of"} {$row.stats.contributors}</span>
                    {else}
                        &mdash;
                    {/if}
                </td>
            </tr>
            {/foreach}
        </tbody>
    </table>

    </main>
{/block}
