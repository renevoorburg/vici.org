(function(){
  function $(sel, root){ return (root||document).querySelector(sel); }
  function $all(sel, root){ return Array.from((root||document).querySelectorAll(sel)); }
  function setFieldError(field, msg){
    var el = document.querySelector('.vici-field-error[data-error-for="'+field+'"]');
    if (el) el.textContent = msg || '';
  }
  function clearErrors(){ $all('.vici-field-error').forEach(function(e){ e.textContent=''; }); var formErr=$('#form-error'); if(formErr) formErr.textContent=''; }
  function getAltchaPayload(){
    // Hidden input produced by the altcha-widget in include/altcha.tpl
    var hidden = document.querySelector('input[name="altcha"]');
    return (hidden && hidden.value) ? hidden.value : '';
  }
  function resetAltcha(){
    var widget = document.querySelector('altcha-widget');
    if (!widget) return;
    try {
      if (typeof widget.reset === 'function') { widget.reset(); }
      else { widget.setAttribute('challenge', '/captcha?r=' + Date.now()); }
    } catch(e) {}
  }

  document.addEventListener('DOMContentLoaded', function(){
    var form = document.getElementById('register-form');
    if (!form) return;

    form.addEventListener('submit', function(ev){
      ev.preventDefault();
      clearErrors();

      var payload = {
        accountName: $('#accountName')?.value || '',
        realName: $('#realName')?.value || '',
        email: $('#email')?.value || '',
        password: $('#password')?.value || '',
        passwordConfirm: $('#passwordConfirm')?.value || '',
        website: $('#reg_website')?.value || '',
        altcha: getAltchaPayload()
      };

      var headers = { 'Content-Type': 'application/json' };
      if (window.viciToken) headers['X-Vici-Token'] = window.viciToken;

      var submitBtn = form.querySelector('button[type="submit"]');
      if (submitBtn) submitBtn.disabled = true;

      fetch('/api/users', {
        method: 'POST',
        headers: headers,
        body: JSON.stringify(payload),
        credentials: 'same-origin'
      }).then(function(res){
        return res.json().catch(function(){ return {}; }).then(function(json){ return { status: res.status, body: json }; });
      }).then(function(resp){
        if (resp.status === 201) {
          // success; login page explains the activation email
          window.location.href = '/login?registered=1';
          return;
        }
        if (resp.status === 422 || resp.status === 409) {
          var errors = resp.body && resp.body.errors || {};
          Object.keys(errors).forEach(function(field){ setFieldError(field, errors[field]); });
          var msg = resp.body && resp.body.message;
          if (msg && document.getElementById('form-error')) document.getElementById('form-error').textContent = msg;
          // New ALTCHA challenge if the captcha itself was rejected
          if (errors.__all__ === 'user.bot_detected') resetAltcha();
          return;
        }
        // Other errors
        if (document.getElementById('form-error')) document.getElementById('form-error').textContent = 'Server error';
      }).catch(function(){
        if (document.getElementById('form-error')) document.getElementById('form-error').textContent = 'Network error';
      }).finally(function(){ if (submitBtn) submitBtn.disabled = false; });
    });
  });
})();
