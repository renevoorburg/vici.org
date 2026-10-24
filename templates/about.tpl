{extends file="base.tpl"}

{block name=main}
    <div class="hr"></div>
    <main class="margin">
        <h1>{$about_title|default:"About Vici.org"}</h1>
        {$about_content nofilter}
    </main>
{/block}
