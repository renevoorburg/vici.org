{if !$is_verified_real_user}
    <script src="/js/altcha.js" defer></script>
    {if $lang|default:'en' eq 'nl' || $lang|default:'en' eq 'de' || $lang|default:'en' eq 'fr'}
        <script src="/js/altcha-i18n-{$lang|default:'en'}.js" defer></script>
    {/if}
    {$bot_challenge_label|default:"Verifying you are human"}:
    <altcha-widget challenge="/captcha" auto="onfocus" hidelogo hidefooter name="altcha" language="{$lang|default:'en'}"></altcha-widget>
{/if}
