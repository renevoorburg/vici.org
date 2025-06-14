{extends file="base.tpl"}
{block name=main}
    <div id="map"></div>

    <div class="highlights-column">
        <div class="highlights-scroll">
            <div id="selectionbox-content">
                <!-- selection -->
            </div>
            <div class="highlights-content">
                <h2>{$highlights_title|default:"Highlights"}</h2>
                <div id="highlights-items" class="space-y-3">
                    <!-- highlights -->
                </div>
            </div>
        </div>
    </div>
{/block}