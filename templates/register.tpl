{extends file="base.tpl"}

{block name=main}
  <h1>Register</h1>

  {if isset($error_message) && $error_message}
    <div class="error">{$error_message nofilter}</div>
  {/if}

  <div id="form-error" class="error"></div>

  <form id="register-form" method="post" action="/api/users" novalidate>
    <div class="form-row">
      <label for="accountName">Account name:</label>
      <input type="text" id="accountName" name="accountName" value="{$form_accountname_previous|escape}" minlength="4" required>
      <div class="field-error" data-error-for="accountName"></div>
    </div>

    <div class="form-row">
      <label for="realName">Display name</label>
      <input type="text" id="realName" name="realName" value="{$form_realname_previous|escape}" minlength="4" required>
      <div class="field-error" data-error-for="realName"></div>
    </div>

    <div class="form-row">
      <label for="email">Email</label>
      <input type="email" id="email" name="email" value="{$form_email_previous|escape}" required>
      <div class="field-error" data-error-for="email"></div>
    </div>

    <div class="form-row">
      <label for="password">Password</label>
      <input type="password" id="password" name="password" required>
      <div class="field-error" data-error-for="password"></div>
    </div>

    <div class="form-row">
      <label for="passwordConfirm">Confirm password</label>
      <input type="password" id="passwordConfirm" name="passwordConfirm" required>
      <div class="field-error" data-error-for="passwordConfirm"></div>
    </div>

    <div class="form-row">
    {include file="include/turnstile.tpl" is_verified_real_user=$is_verified_real_user turnstile_sitekey=$turnstile_sitekey bot_challenge_label=$bot_challenge_label}
    </div>

    <div class="form-row">
      <button type="submit">Create account</button>
    </div>
  </form>
  <script src="/js/register.js?v=1"></script>
{/block}
