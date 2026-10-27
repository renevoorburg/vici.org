<script>
    window.viciToken = "{$viciToken}";
    window.sessionLanguage = "{$sessionLanguage}";
    {if isset($vicibase)}
        window.viciBase = "{$vicibase}";
    {/if}
    {if isset($mainSite->id)}
        window.siteId = "{$mainSite->id}";
    {/if}
    {if isset($mainSite->representativeLocation) && isset($mainSite->representativeLocation->latitude) && isset($mainSite->representativeLocation->longitude)}
        window.lat = {$mainSite->representativeLocation->latitude};
        window.lng = {$mainSite->representativeLocation->longitude};
    {/if}
    {if isset($js_translations)}
        window.viciTranslations = {$js_translations};
    {/if}
    window.selectionTitle = "{$selection_title|default:"Selected"}";
</script>