<script>
    window.viciToken = "{$viciToken}";
    window.sessionLanguage = "{$sessionLanguage}";
    {if isset($vicibase)}
        window.viciBase = "{$vicibase}";
    {/if}
    {if isset($id)}
        window.siteId = "{$id}";
    {/if}
    {if isset($location) && isset($location->latitude) && isset($location->longitude)}
        window.lat = {$location->latitude};
        window.lng = {$location->longitude};
    {/if}
    {if isset($js_translations)}
        window.viciTranslations = {$js_translations};
    {/if}
</script>