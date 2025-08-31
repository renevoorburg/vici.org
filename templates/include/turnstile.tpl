{if !$is_real_user}
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    {$bot_challenge_label|default:"Verifying you are human"}:
    <div class="cf-turnstile" data-sitekey="{$turnstile_sitekey}"></div>
{/if}