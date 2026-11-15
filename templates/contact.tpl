{extends file="base.tpl"}

{block name=main}
    <main>
        <div class="vici-overlay">
            <div class="vici-card">
            <a href="/" class="vici-close" title="{$close|default:"Close"}" aria-label="{$close|default:"Close"}">×</a>
            <h1>{$contact_title|default:"Contact"}</h1>
                {if $sent}
                <div class="vici-message">{$contact_sent|default:"Your message has been sent."}</div>
                {else}
                {if $error_message}
                <div class="vici-error">{$error_message}</div>
                {/if}
                <p class="vici-mb">{$contact_intro|default:"Use this form to send a message to the maintainer of Vici.org."}</p>
                <form action="/contact" method="post" class="vici-form">
                    <input type="hidden" name="csrf_token" value="{$csrf_token|escape}">
                    <div class="vici-form-grid">
                        {if !$is_logged_in}
                        <label class="vici-label vici-label--field" for="frm_email">{$email_label|default:"Email address"}:</label>
                        <div>
                            <input class="vici-input" type="email" id="frm_email" name="email" value="{$form_email|escape}" required />
                            <div class="vici-hint">{$email_hint|default:"Your e-mail address is used only to reply to your message."}</div>
                        </div>
                        {/if}
                        <label class="vici-label vici-label--top" for="message">{$message_label|default:"Message"}:</label>
                        <textarea class="vici-textarea" id="message" name="message" rows="8" maxlength="2000" required>{$form_message|escape}</textarea>
                    </div>
                    {if $is_logged_in}
                    <div class="vici-hint vici-mb">{$contact_account_hint|default:"The reply address will be the e-mail address of your account."}</div>
                    {/if}
                    {include file="include/altcha.tpl" is_verified_real_user=$is_verified_real_user bot_challenge_label=$bot_challenge_label}
                    <div>
                        <input type="submit" class="vici-btn" value="{$send_label|default:"Send"}">
                    </div>
                </form>
                {/if}
            </div>
        </div>
    </main>
{/block}
