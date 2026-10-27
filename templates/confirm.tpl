{extends file="base.tpl"}

{block name=main}
<main class="margin">
    <h1>{$confirm_title|default:"Account activation"}</h1>
    {if $confirm_message}
        <p class="vici-message">{$confirm_message}</p>
        <p><a href="/login">{$confirm_login_link|default:"Login to continue."}</a></p>
    {/if}
    {if $confirm_error}
        <p class="vici-error">{$confirm_error}</p>
    {/if}
</main>
{/block}
