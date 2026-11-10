{extends file="base.tpl"}

{block name=main}
    <main>
        <div class="vici-overlay">
            <div class="vici-card">
            <a href="/user/{$recipient_id}" class="vici-close" title="{$close|default:"Close"}" aria-label="{$close|default:"Close"}">×</a>
            <h1>{$contact_title|default:"Mail user"}</h1>
                {if $sent}
                <div class="vici-message">{$contact_sent|default:"Your message has been sent."}</div>
                <p><a href="/user/{$recipient_id}">{$back_label|default:"Back to profile"}</a></p>
                {else}
                {if $error_message}
                <div class="vici-error">{$error_message}</div>
                {/if}
                <p>{$contact_intro|default:"Send a message to"} {$recipient_name|escape}. {$contact_note|default:"They will see your account's email address as reply address."}</p>
                <form action="" method="post" class="vici-form">
                    <div class="vici-form-grid">
                        <label class="vici-label vici-label--top" for="message">{$message_label|default:"Message"}:</label>
                        <textarea class="vici-textarea" id="message" name="message" rows="8" required>{$form_message|escape}</textarea>
                    </div>
                    <div>
                        <input type="submit" class="vici-btn" value="{$send_label|default:"Send"}">
                    </div>
                </form>
                {/if}
            </div>
        </div>
    </main>
{/block}
