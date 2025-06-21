{extends file="base.tpl"}
{block name=main}

    <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); display: flex; justify-content: center; align-items: center; z-index: 1000;">
        <div style="margin: auto; border: 1px solid #ccc; padding: 1rem; border-radius: 0.5rem; box-shadow: 0 0.25rem 0.375rem rgba(0, 0, 0, 0.1); background-color: white; max-width: 31.25rem; width: 100%;">
        <h1 style="font-size: 1.5rem; color: #1e3a8a; margin-bottom: 1rem;">{$title|default:"Login"}</h1>
            {if $message}
            <div style="margin-bottom: 1rem; color: #718096;">{$message}</div>
            {/if}
            {if $error_message}
            <div style="margin-bottom: 1rem; color: #db3e17;">{$error_message}</div>
            {/if}
            <div style="margin-bottom: 1rem; color: #718096;" class="login-text">{$login_text|default:"Log on to your account or <a href='/register'>register</a>."}</div>
            <form action="" method="post" style="display: flex; flex-direction: column; gap: 1rem;">
                <div style="display: grid; grid-template-columns: auto 1fr; gap: 0.75rem; align-items: center;">
                    <label for="frm_accountname" style="color: #374151;">{$form_accountname|default:"Account name"}:</label>
                    <input type="text" id="frm_accountname" name="accountname" value="{$form_accountname_previous}" style="border: 1px solid #e2e8f0; border-radius: 0.25rem; padding: 0.5rem; font-size: 1rem;" />
                    
                    <label for="frm_password" style="color: #374151;">{$form_password|default:"Password"}:</label>
                    <input type="password" id="frm_password" name="password" style="border: 1px solid #e2e8f0; border-radius: 0.25rem; padding: 0.5rem; font-size: 1rem;" />
                </div>
                
                <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
                <div class="cf-turnstile" data-sitekey="{$turnstile_sitekey}"></div>
                
                <div style="margin-top: 0.5rem;">
                    <input type="submit" value="{$form_login|default:Login}" style="background-color: #1e3a8a; color: white; border: none; border-radius: 0.25rem; padding: 0.5rem 1rem; font-size: 1rem; cursor: pointer;">
                </div>
            </form>
        </div>
    </div>

{/block}