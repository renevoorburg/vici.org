{extends file="base.tpl"}
{block name=main}
    <main>
        <div class="vici-overlay">
            <div class="vici-card">
            <a href="/" class="vici-close" title="{$close|default:"Close"}" aria-label="{$close|default:"Close"}">×</a>
            <h1>{$lostpw_title|default:"Reset password"}</h1>
                {if $lostpw_done}
                <div class="vici-message">{$lostpw_sent|default:"If an account is registered with that email address, an email with instructions has been sent."}</div>
                {else}
                <div class="vici-hint vici-mb">{$lostpw_intro|default:"To reset your password, please identify yourself by supplying the email address you registered at Vici."}</div>
                {if $error_message}
                <div class="vici-error">{$error_message}</div>
                {/if}
                <form action="/lostpw" method="post" class="vici-form">
                    <div class="vici-form-grid">
                        <label class="vici-label" for="frm_email">{$email_label|default:"Email address"}:</label>
                        <input class="vici-input" type="email" id="frm_email" name="frm_email" value="{$lostpw_email_previous|escape}" required />
                    </div>
                    {include file="include/turnstile.tpl" is_verified_real_user=$is_verified_real_user turnstile_sitekey=$turnstile_sitekey bot_challenge_label=$bot_challenge_label}
                    <div>
                        <input type="submit" class="vici-btn" value="{$lostpw_submit|default:"Submit"}">
                    </div>
                </form>
                {/if}
            </div>
        </div>
    </main>
{/block}
