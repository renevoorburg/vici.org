{extends file="base.tpl"}

{block name=main}
    <div class="hr"></div>
    <main class="margin">

    <h1>{$accesscontrol_title|default:"Access control"}</h1>

    <h2>{$subnet_blocks_label|default:"Blocked subnets"}</h2>
    <table class="stats-table">
        <thead>
            <tr>
                <th>{$address_label|default:"Address"}</th>
                <th>{$expires_label|default:"Expires in"}</th>
                <th>{$subnets_label|default:"Subnets"}</th>
                <th>{$trigger_label|default:"Triggered by"}</th>
            </tr>
        </thead>
        <tbody>
            {foreach $subnet_blocks as $row}
            <tr>
                <td>{$row.key|escape:'html'}</td>
                <td class="stats-num">{$row.ttl|escape:'html'}</td>
                <td class="stats-num">{$row.count|escape:'html'|default:"&mdash;"}</td>
                <td>{$row.trigger|escape:'html'|default:"&mdash;"}</td>
            </tr>
            {foreachelse}
            <tr><td colspan="4">{$none_label|default:"None"}</td></tr>
            {/foreach}
        </tbody>
    </table>

    </main>
{/block}
