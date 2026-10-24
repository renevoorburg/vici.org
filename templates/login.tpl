{extends file="base.tpl"}
{block name=main}
    <main>
        <div class="vici-overlay">
            <div class="vici-card">
            <a href="/" class="vici-close" title="{$close|default:"Close"}" aria-label="{$close|default:"Close"}">×</a>
            <h1>{$login_title_label|default:"Login"}</h1>
                {if $message}
                <div class="vici-hint vici-mb">{$message}</div>
                {/if}
                {if $error_message}
                <div class="vici-error">{$error_message}</div>
                {/if}
                <div class="vici-hint vici-mb login-text">{$login_text|default:"Log on to your account or <a href='/register'>register</a>."}</div>
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
                    <div class="vici-hint"><a href="/lostpw">{$lostpw_link|default:"Forgot your password?"}</a></div>
                </form>
            </div>
        </div>
    </main>
{/block}