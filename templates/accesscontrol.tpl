{extends file="base.tpl"}

{block name=main}
    <div class="hr"></div>
    <main class="margin">

    <h1>{$accesscontrol_title|default:"Access control"}</h1>

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

    </main>
{/block}
