{extends file="base.tpl"}

{block name=main}
    <div class="hr"></div>
    <main class="margin">
        <h1>{$notfound_message|default:"Not found."}</h1>
        <p><a href="/">{$notfound_home|default:"Back to the map"}</a></p>
    </main>
{/block}
