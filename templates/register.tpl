{extends file="base.tpl"}

{block name=main}
    <main>
        <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); display: flex; justify-content: center; align-items: center; z-index: 1000;">
            <div style="margin: auto; border: 1px solid #ccc; padding: 1rem; border-radius: 0.5rem; box-shadow: 0 0.25rem 0.375rem rgba(0, 0, 0, 0.1); background-color: white; max-width: 31.25rem; width: 100%; position: relative;">
                <a id="modal-close" href="/" title="Sluiten" style="position: absolute; top: 0.5rem; right: 0.5rem; text-decoration: none; color: #888; font-size: 1.5rem; font-weight: bold; line-height: 1; cursor: pointer; z-index: 10;" aria-label="{$close|default:"Close"}">×</a>
                <h1 style="font-size: 1.5rem; color: #1e3a8a; margin-bottom: 1rem;">{$register_account_label|default:"Register account"}</h1>

                {if isset($error_message) && $error_message}
                    <div style="margin-bottom: 1rem; color: #db3e17;">{$error_message nofilter}</div>
                {/if}
                <div id="form-error" class="error" style="margin-bottom: 0.5rem; color: #db3e17;"></div>

                <form id="register-form" method="post" action="/api/users" novalidate style="display: flex; flex-direction: column; gap: 1rem;">
                    <div style="display: grid; grid-template-columns: auto 1fr; gap: 0.75rem; align-items: center;">
                        <label for="accountName" style="color: #374151;">{$account_name_label|default:"Account name"}:</label>
                        <div>
                            <input type="text" id="accountName" name="accountName" value="{$form_accountname_previous|escape}" minlength="4" required placeholder="{$account_name_placeholder|default:"janedoe"}" style="border: 1px solid #e2e8f0; border-radius: 0.25rem; padding: 0.5rem; font-size: 1rem; width: 100%;">
                            <div class="field-error" data-error-for="accountName" style="color:#db3e17; font-size:0.9rem; margin-top:0.25rem;"></div>
                        </div>

                        <label for="realName" style="color: #374151;">{$realname_label|default:"Full name"}:</label>
                        <div>
                            <input type="text" id="realName" name="realName" value="{$form_realname_previous|escape}" minlength="4" 
                                required 
                                placeholder="{$realname_placeholder|default:"Jane Doe"}" 
                                style="border: 1px solid #e2e8f0; border-radius: 0.25rem; padding: 0.5rem; font-size: 1rem; width: 100%;">
                            <div class="field-error" data-error-for="realName" style="color:#db3e17; font-size:0.9rem; margin-top:0.25rem;"></div>
                        </div>

                        <label for="email" style="color: #374151;">{$email_label|default:"Email"}:</label>
                        <div>
                            <input type="email" id="email" name="email" value="{$form_email_previous|escape}" required 
                                placeholder="{$email_placeholder|default:"jane.doe@example.com"}" 
                                style="border: 1px solid #e2e8f0; border-radius: 0.25rem; padding: 0.5rem; font-size: 1rem; width: 100%;">
                            <div class="field-error" data-error-for="email" style="color:#db3e17; font-size:0.9rem; margin-top:0.25rem;"></div>
                        </div>

                        <label for="password" style="color: #374151;">{$password_label|default:"Password"}:</label>
                        <div>
                            <input type="password" id="password" name="password" required minlength="8" style="border: 1px solid #e2e8f0; border-radius: 0.25rem; padding: 0.5rem; font-size: 1rem; width: 100%;">
                            <div class="field-error" data-error-for="password" style="color:#db3e17; font-size:0.9rem; margin-top:0.25rem;"></div>
                        </div>

                        <label for="passwordConfirm" style="color: #374151;">{$password_confirm_label|default:"Confirm password"}:</label>
                        <div>
                            <input type="password" id="passwordConfirm" name="passwordConfirm" required minlength="8" style="border: 1px solid #e2e8f0; border-radius: 0.25rem; padding: 0.5rem; font-size: 1rem; width: 100%;">
                            <div class="field-error" data-error-for="passwordConfirm" style="color:#db3e17; font-size:0.9rem; margin-top:0.25rem;"></div>
                        </div>
                    </div>

                    {include file="include/turnstile.tpl" is_verified_real_user=$is_verified_real_user turnstile_sitekey=$turnstile_sitekey bot_challenge_label=$bot_challenge_label}

                    <div style="margin-top: 0.5rem;">
                        <button type="submit" style="background-color: #1e3a8a; color: white; border: none; border-radius: 0.25rem; padding: 0.5rem 1rem; font-size: 1rem; cursor: pointer;">{$register_label|default:"Register"}</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
{/block}

{block name=footerscripts}
    <script src="/js/register.js?v=1"></script>
    <script>
    (function(){
      function sameOriginReferrer(){
        try {
          if (!document.referrer) return null;
          var ref = new URL(document.referrer, window.location.origin);
          return ref.origin === window.location.origin ? ref : null;
        } catch(e){ return null; }
      }
      function closeOverlay(){
        var ref = sameOriginReferrer();
        if (ref) {
          var isLogin = ref.pathname === '/login';
          if (isLogin) {
            history.go(-2);
          } else {
            history.back();
          }
          // Fallback mocht history geen effect hebben
          setTimeout(function(){
            if (!document.hidden) { window.location.href = '/'; }
          }, 500);
        } else {
          window.location.href = '/';
        }
      }
      document.addEventListener('DOMContentLoaded', function(){
        var btn = document.getElementById('modal-close');
        if (btn) {
          btn.addEventListener('click', function(e){ e.preventDefault(); closeOverlay(); });
        }
        document.addEventListener('keydown', function(e){
          if (e.key === 'Escape') closeOverlay();
        });
      });
    })();
    </script>
{/block}
