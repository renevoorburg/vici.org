{extends file="base.tpl"}
{block name=main}
    <main>
        <div class="vici-overlay">
            <div class="vici-card">
            <a href="/" class="vici-close" title="{$close|default:"Close"}" aria-label="{$close|default:"Close"}">×</a>
            <h1>{$reset_title|default:"Reset password"}</h1>
                {if $reset_done}
                <div class="vici-message">{$reset_active|default:"The password is now active."}</div>
                <p><a href="/login">{$confirm_login_link|default:"Login to continue."}</a></p>
                {elseif $reset_error && !$reset_account_name}
                <div class="vici-error">{$reset_error}</div>
                {else}
                {if $reset_error}
                <div class="vici-error">{$reset_error}</div>
                {/if}
                <form action="/reset?code={$reset_code|escape:'url'}" method="post" class="vici-form">
                    <div class="vici-form-grid">
                        <label class="vici-label">{$account_name_label|default:"Account name"}:</label>
                        <div>{$reset_account_name|escape}</div>

                        <label class="vici-label" for="frm_password">{$password_label|default:"Password"}:</label>
                        <input class="vici-input" type="password" id="frm_password" name="password" minlength="8" required />

                        <label class="vici-label" for="frm_passwordrepeat">{$password_confirm_label|default:"Confirm password"}:</label>
                        <input class="vici-input" type="password" id="frm_passwordrepeat" name="passwordrepeat" minlength="8" required />
                    </div>
                    <div>
                        <input type="submit" class="vici-btn" value="{$reset_submit|default:"Reset"}">
                    </div>
                </form>
                {/if}
            </div>
        </div>
    </main>
{/block}
