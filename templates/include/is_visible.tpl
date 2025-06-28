{if $isVisible}
    {$visible_label|default:"Visible"}
{else}
    {$not_visible_label|default:"Not visible"}
{/if}