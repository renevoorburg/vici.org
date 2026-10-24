{extends file="base.tpl"}
{block name=main}
    <main>
        <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); display: flex; justify-content: center; align-items: center; z-index: 1000;">
            <div class="vici-card" style="margin: auto; width: 100%; position: relative;">
            <a href="/" title="Sluiten" style="position: absolute; top: 0.5rem; right: 0.5rem; text-decoration: none; color: #888; font-size: 1.5rem; font-weight: bold; line-height: 1; cursor: pointer; z-index: 10;" aria-label="{$close|default:"Close"}">×</a>
            <h1>{$login_title_label|default:"Login"}</h1>
                {if $message}
                <div class="vici-hint" style="margin-bottom: 1rem;">{$message}</div>
                {/if}
                {if $error_message}
                <div class="vici-error">{$error_message}</div>
                {/if}
                <div class="vici-hint login-text" style="margin-bottom: 1rem;">{$login_text|default:"Log on to your account or <a href='/register'>register</a>."}</div>
                <form action="" method="post" class="vici-form">
                    <div class="vici-form-grid">
                        <label class="vici-label" for="frm_accountname">{$account_name_label|default:"Account name"}:</label>
                        <input class="vici-input" type="text" id="frm_accountname" name="accountname" value="{$form_accountname_previous}" />
                        
                        <label class="vici-label" for="frm_password">{$password_label|default:"Password"}:</label>
                        <input class="vici-input" type="password" id="frm_password" name="password" />
                    </div>
                    {include file="include/turnstile.tpl" is_verified_real_user=$is_verified_real_user turnstile_sitekey=$turnstile_sitekey bot_challenge_label=$bot_challenge_label}
                    <div>
                        <input type="submit" class="vici-btn" value="{$login_title_label}">
                    </div>
                </form>
            </div>
        </div>
    </main>
{/block}