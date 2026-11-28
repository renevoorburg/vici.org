{extends file="base.tpl"}
{block name=sitesubtitle}<h1 class="subtitle">:: {$sitesubtitle|default:"archaeological atlas"}</h1>{/block}
{block name=metadata}
    <meta name="description" content="{$meta_description|escape}">
    <link rel="canonical" href="https://vici.org/">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Vici.org">
    <meta property="og:title" content="Vici.org — {$title|escape}">
    <meta property="og:description" content="{$meta_description|escape}">
    <meta property="og:url" content="https://vici.org/">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="Vici.org — {$title|escape}">
    <meta name="twitter:description" content="{$meta_description|escape}">
    {if $jsonld}
    <script type="application/ld+json">{$jsonld}</script>
    {/if}
{/block}
{block name=stylesheets}
    <link rel="stylesheet" href="/js/photoswipe/dist/photoswipe.css">
    <link rel="stylesheet" href="/js/photoswipe-dynamic-caption-plugin/photoswipe-dynamic-caption-plugin.css">
{/block}
{block name=main}
    <main class="homepage">  
        <div id="map"></div>

        <div class="highlights-column">
            <div class="highlights-scroll">
                <div id="selectionbox-content">
                    <!-- dynamic selection -->
                </div>
                <div class="highlights-content">
                    <h2>{$highlights_title|default:"Highlights"}:</h2>
                    <div id="highlights-items" class="space-y-3">
                        <!-- dynamic highlights -->
                    </div>
                </div>
            </div>
        </div>
    </main> 
{/block}

{block name=footerscripts}
    <script src="/js/footer-home.js?v={$smarty.now|date_format:"%Y%m%d"}"></script>
{/block}
