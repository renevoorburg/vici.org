{extends file="base.tpl"}

{block name=main}
    <div class="hr"></div>
    <main class="margin">
        <h1>{$privacy_title|default:"Privacy"}</h1>
        {$privacy_content nofilter}
    </main>
{/block}
