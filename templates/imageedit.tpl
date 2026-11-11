{extends file="base.tpl"}
{block name=main}
    <main>
        <div class="vici-overlay">
            <div class="vici-card">
            <a href="/image/{$image->id}" class="vici-close" title="{$close|default:"Close"}" aria-label="{$close|default:"Close"}">×</a>
            <h1>{$edit_description_label|default:"Edit description"}</h1>
                {if isset($form_error)}<div class="vici-error">{$form_error|escape}</div>{/if}
                <form action="" method="post" class="vici-form">
                    <input type="hidden" name="csrf_token" value="{$csrf_token|escape}">
                    <div class="vici-form-grid">
                        <label class="vici-label" for="img_title">{$title_label|default:"Title"}:</label>
                        <input class="vici-input" type="text" id="img_title" name="title" value="{$image->title|escape}" maxlength="255" required />
                        <label class="vici-label vici-label--top" for="img_description">{$description_label|default:"Description"}:</label>
                        <textarea class="vici-textarea" id="img_description" name="description" rows="4">{$image->description|escape}</textarea>
                        {if $can_edit_all}
                        <label class="vici-label" for="img_license">{$license_label|default:"License"}:</label>
                        <select class="vici-input" id="img_license" name="license">
                            {foreach $licenses as $license}
                            {if $license->isUserSelectable || ($image->license && $image->license->id == $license->id)}
                            <option value="{$license->id}" {if $image->license && $image->license->id == $license->id}selected{/if}>{$license->name|escape}</option>
                            {/if}
                            {/foreach}
                        </select>
                        {/if}
                    </div>
                    <div>
                        <input type="submit" class="vici-btn" value="{$save_label|default:"Save"}">
                    </div>
                </form>
            </div>
        </div>
    </main>
{/block}
