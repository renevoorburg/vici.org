{extends file="base.tpl"}

{block name=main}
    <div class="hr"></div>
    <main class="margin">

    <h1>{$accesscontrol_title|default:"Access control"}</h1>

    <h2>{$ip_blocks_label|default:"Blocked IPs"}</h2>
    <table class="stats-table">
        <thead>
            <tr>
                <th>{$address_label|default:"Address"}</th>
                <th>{$expires_label|default:"Expires in"}</th>
            </tr>
        </thead>
        <tbody>
            {foreach $ip_blocks as $row}
            <tr>
                <td>{$row.key|escape:'html'}</td>
                <td class="stats-num">{$row.ttl}</td>
            </tr>
            {foreachelse}
            <tr><td colspan="2">{$none_label|default:"None"}</td></tr>
            {/foreach}
        </tbody>
    </table>

    <h2>{$subnet_blocks_label|default:"Blocked subnets"}</h2>
    <table class="stats-table">
        <thead>
            <tr>
                <th>{$address_label|default:"Address"}</th>
                <th>{$expires_label|default:"Expires in"}</th>
            </tr>
        </thead>
        <tbody>
            {foreach $subnet_blocks as $row}
            <tr>
                <td>{$row.key|escape:'html'}</td>
                <td class="stats-num">{$row.ttl}</td>
            </tr>
            {foreachelse}
            <tr><td colspan="2">{$none_label|default:"None"}</td></tr>
            {/foreach}
        </tbody>
    </table>

    <h2>{$strikes_label|default:"Strikes"}</h2>
    <table class="stats-table">
        <thead>
            <tr>
                <th>{$address_label|default:"Address"}</th>
                <th>{$count_label|default:"Count"}</th>
            </tr>
        </thead>
        <tbody>
            {foreach $strikes as $row}
            <tr>
                <td>{$row.key|escape:'html'}</td>
                <td class="stats-num">{$row.count}</td>
            </tr>
            {foreachelse}
            <tr><td colspan="2">{$none_label|default:"None"}</td></tr>
            {/foreach}
        </tbody>
    </table>

    <h2>{$trap_hits_label|default:"Trap hits (last 24h)"}</h2>
    <table class="stats-table">
        <thead>
            <tr>
                <th>{$address_label|default:"Address"}</th>
                <th>{$count_label|default:"Count"}</th>
            </tr>
        </thead>
        <tbody>
            {foreach $trap_hits as $row}
            <tr>
                <td>{$row.key|escape:'html'}</td>
                <td class="stats-num">{$row.count}</td>
            </tr>
            {foreachelse}
            <tr><td colspan="2">{$none_label|default:"None"}</td></tr>
            {/foreach}
        </tbody>
    </table>

    <h2>{$subnet_hits_label|default:"Subnet trap hits (last 24h)"}</h2>
    <table class="stats-table">
        <thead>
            <tr>
                <th>{$address_label|default:"Address"}</th>
                <th>{$count_label|default:"Count"}</th>
            </tr>
        </thead>
        <tbody>
            {foreach $subnet_hits as $row}
            <tr>
                <td>{$row.key|escape:'html'}</td>
                <td class="stats-num">{$row.count}</td>
            </tr>
            {foreachelse}
            <tr><td colspan="2">{$none_label|default:"None"}</td></tr>
            {/foreach}
        </tbody>
    </table>

    </main>
{/block}
