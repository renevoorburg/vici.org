{extends file="base.tpl"}

{block name=main}
    <main>
        <div class="vici-overlay">
            <div class="vici-card">
                <a id="modal-close" href="/" class="vici-close" title="{$close|default:"Close"}" aria-label="{$close|default:"Close"}">×</a>
                <h1>{$register_account_label|default:"Register account"}</h1>

                {if isset($error_message) && $error_message}
                    <div class="vici-error">{$error_message nofilter}</div>
                {/if}
                <div id="form-error" class="vici-error"></div>

                <form id="register-form" method="post" action="/api/users" novalidate class="vici-form">
                    <div class="vici-form-grid">
                        <label class="vici-label" for="accountName">{$account_name_label|default:"Account name"}:</label>
                        <div>
                            <input class="vici-input" type="text" id="accountName" name="accountName" value="{$form_accountname_previous|escape}" minlength="4" required placeholder="{$account_name_placeholder|default:"janedoe"}">
                            <div class="vici-field-error" data-error-for="accountName"></div>
                        </div>

                        <label class="vici-label" for="realName">{$realname_label|default:"Display name"}:</label>
                        <div>
                            <input class="vici-input" type="text" id="realName" name="realName" value="{$form_realname_previous|escape}" minlength="4" 
                                required 
                                placeholder="{$realname_placeholder|default:"Jane Doe"}">
                            <div class="vici-field-error" data-error-for="realName"></div>
                        </div>
                        <div class="vici-hint vici-grid-field">{$displayname_explanation|default:"This name is shown publicly on the pages and images you contribute to."} {$displayname_format|default:"Format: Jane Doe or Doe, Jane"}</div>

                        <label class="vici-label" for="email">{$email_label|default:"Email"}:</label>
                        <div>
                            <input class="vici-input" type="email" id="email" name="email" value="{$form_email_previous|escape}" required 
                                placeholder="{$email_placeholder|default:"jane.doe@example.com"}">
                            <div class="vici-field-error" data-error-for="email"></div>
                        </div>

                        <label class="vici-label" for="password">{$password_label|default:"Password"}:</label>
                        <div>
                            <input class="vici-input" type="password" id="password" name="password" required minlength="8">
                            <div class="vici-field-error" data-error-for="password"></div>
                        </div>

                        <label class="vici-label" for="passwordConfirm">{$password_confirm_label|default:"Confirm password"}:</label>
                        <div>
                            <input class="vici-input" type="password" id="passwordConfirm" name="passwordConfirm" required minlength="8">
                            <div class="vici-field-error" data-error-for="passwordConfirm"></div>
                        </div>
                    </div>

                    {include file="include/turnstile.tpl" is_verified_real_user=$is_verified_real_user turnstile_sitekey=$turnstile_sitekey bot_challenge_label=$bot_challenge_label}

                    <div>
                        <button type="submit" class="vici-btn">{$register_label|default:"Register"}</button>
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
