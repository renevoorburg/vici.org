{extends file="base.tpl"}
{block name=main}
    <main>
        <div class="vici-overlay">
            <div class="vici-card">
            <a href="/" class="vici-close" title="{$close|default:"Close"}" aria-label="{$close|default:"Close"}">×</a>
            <h1>{$settings_title|default:"Settings"}</h1>
                {if $error_message}
                <div class="vici-error">{$error_message}</div>
                {/if}
                <form action="" method="post" class="vici-form">
                    <div class="vici-form-grid">
                        <label class="vici-label" for="realName">{$displayname_label|default:"Display name"}:</label>
                        <input class="vici-input" type="text" id="realName" name="realName" value="{$settings_realname|escape}" minlength="4" required />
                    </div>
                    <div class="vici-hint vici-mb">
                        {$displayname_explanation|default:"This name is shown publicly on the pages and images you contribute to."} {$displayname_format|default:"Format: Doe, J."}<br>
                        {$displayname_citation_label|default:"In suggested citations this name appears as"}: <em id="citation-preview"></em>
                    </div>
                    <div>
                        <input type="submit" class="vici-btn" value="{$save_label|default:"Save"}">
                    </div>
                </form>
            </div>
        </div>
    </main>
{/block}
{block name=footerscripts}
<script>
    // mirrors User::parseName(): "Jane Doe" or "Doe, Jane"
    (function () {
        const input = document.getElementById('realName');
        const preview = document.getElementById('citation-preview');
        function updateCitationPreview() {
            const name = input.value.trim().replace(/\s+/g, ' ');
            if (!name) { preview.textContent = '—'; return; }
            if (name.includes(',')) { preview.textContent = name; return; }
            const parts = name.split(' ');
            const given = parts[0] || '';
            const last = (parts.slice(1).join(' ') || '');
            const initials = given.split(' ').filter(Boolean)
                .map(w => w.charAt(0).toUpperCase() + '.').join('');
            const capLast = last ? last.charAt(0).toUpperCase() + last.slice(1) : '';
            preview.textContent = (capLast && initials) ? capLast + ', ' + initials : name;
        }
        input.addEventListener('input', updateCitationPreview);
        updateCitationPreview();
    })();
</script>
{/block}
