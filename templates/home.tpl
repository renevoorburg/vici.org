{extends file="base.tpl"}
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
