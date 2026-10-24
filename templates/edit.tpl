{extends file="base.tpl"}

{block name=stylesheets}
<style>
.edit-tabs { display: flex; gap: 0.5rem; margin: 1rem 0; border-bottom: 1px solid #dee2e6; }
.edit-tabs a { padding: 0.5rem 1rem; text-decoration: none; color: #1e3a8a; border: 1px solid transparent; border-bottom: none; }
.edit-tabs a.selected { border-color: #dee2e6; background: #f8f9fa; font-weight: bold; }
.edit-tabs a.disabled { color: #aaa; cursor: default; }
#editcanvas { width: 100%; height: 260px; background-color: #5f9cc5; border-radius: 0.25rem; }
.datenotation { border: 1px dotted #1e3a8a; color: #1e3a8a; margin: 10px 0; font-size: 0.85rem; }
#periodbox { display: contents; }
#periodbox .datenotation { grid-column: 2; }
#periodbox .datenotation caption { text-align: left; }
@media (max-width: 768px) {
    #periodbox .datenotation { grid-column: 1 / -1; }
}
.datenotation td { padding: 0 0.5rem; }
.kind-group { display: grid; grid-template-columns: repeat(auto-fill, 240px); gap: 0.75rem 1rem; }
.kind-card { width: 240px; }
.kind-card a { text-decoration: none; color: inherit; }
.kind-head { display: flex; align-items: center; gap: 0.4rem; margin-bottom: 0.35rem; }
.kind-head h3 { font-size: 1rem; margin: 0; color: #1e3a8a; }
.kind-card .kind-imgwrap { position: relative; width: 220px; height: 124px; }
.kind-card .kind-imgwrap img { position: absolute; top: 0; right: 0; left: 0; border-radius: 0.25rem; }
.kind-card .kind-desc { position: absolute; left: 0; right: 0; bottom: 0; margin: 0; padding: 4px; color: #000; background: rgba(255,255,255,0.75); font-size: 0.8rem; }
</style>
{/block}

{block name=main}
<main>
    {if isset($edit_fatal)}
        <div class="vici-error">{$edit_fatal}</div>
    {else}
    <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); display: flex; justify-content: center; z-index: 1000; overflow-y: auto;">
    <div class="vici-card vici-card--wide" style="margin: auto; position: relative;">
    {if $is_new}
        <a href="/" title="{$close|default:"Close"}" aria-label="{$close|default:"Close"}" style="position: absolute; top: 0.5rem; right: 0.5rem; text-decoration: none; color: #888; font-size: 1.5rem; font-weight: bold; line-height: 1; cursor: pointer; z-index: 10;">×</a>
    {else}
        <a href="/vici/{$pntid}" title="{$close|default:"Close"}" aria-label="{$close|default:"Close"}" style="position: absolute; top: 0.5rem; right: 0.5rem; text-decoration: none; color: #888; font-size: 1.5rem; font-weight: bold; line-height: 1; cursor: pointer; z-index: 10;">×</a>
    {/if}
    {if $is_new}
        <h1>{$edit_new_title|default:"Add a new site"}</h1>
    {else}
        <h1>{$edit_title|default:"Edit site"}: {$site_name|escape}</h1>
    {/if}

    {if !$is_new}
    <nav class="edit-tabs">
        <a href="/edit/{$pntid}" {if $edit_tab == 'data'}class="selected"{/if}>{$edit_tab_data|default:"Data"}</a>
        <a href="/edit/{$pntid}/image" {if $edit_tab == 'image'}class="selected"{/if}>{$edit_tab_image|default:"Add image"}</a>
        <a href="/edit/{$pntid}/lines" {if $edit_tab == 'lines'}class="selected"{/if}>{$edit_tab_lines|default:"Add lines"}</a>
    </nav>
    {/if}

    {if $form_errors && count($form_errors) > 0}
        <div class="vici-error">
            {foreach from=$form_errors item=err}{$err|escape}<br>{/foreach}
        </div>
    {/if}
    {if $form_message}
        <div class="vici-message">{$form_message|escape}</div>
    {/if}

    {if isset($show_kind_chooser) && $show_kind_chooser}
        <p class="vici-hint">{$edit_choose_kind|default:"What kind of site do you want to add?"}</p>
        {foreach from=$kind_groups item=group}
            <h2>{$group.title|escape}</h2>
            <div class="kind-group">
            {foreach from=$group.items item=k}
                <div class="kind-card">
                    <a href="/add?kind={$k.id}">
                        <div class="kind-head">
                            <div class="marker-icon" data-icon-type="{$k.id}" title="{$k.title|escape}"></div>
                            <h3>{$k.title|escape}</h3>
                        </div>
                        <div class="kind-imgwrap">
                            <img src="//images.vici.org/crop/w220xh124/{$k.image}" onerror="this.onerror=null;this.src='/{$k.image}'" alt="{$k.title|escape}" loading="lazy">
                            {if $k.desc}<p class="kind-desc">{$k.desc|escape}</p>{/if}
                        </div>
                    </a>
                </div>
            {/foreach}
            </div>
        {/foreach}
    {elseif $edit_tab == 'data'}
    <form class="vici-form" method="post" action="{if $is_new}/add{else}/edit/{$pntid}{/if}">
        <div class="vici-form-grid">
            <label class="vici-label" for="kop_edit">{$edit_label_title|default:"Title"} <span class="vici-hint">({$edit_required|default:"required"})</span></label>
            <input class="vici-input" type="text" id="kop_edit" name="kop_edit" value="{$f_title|escape}" required>

            <label class="vici-label vici-label--top" for="short_edit">{$edit_label_summary|default:"Summary"} <span class="vici-hint">({$edit_required|default:"required"})</span></label>
            <textarea class="vici-textarea" id="short_edit" name="short_edit" rows="3" required>{$f_summary|escape}</textarea>

            <label class="vici-label vici-full-row" for="edit_full">{$edit_label_annotation|default:"Description"}</label>
            <div class="vici-full-row"><textarea id="edit_full" name="edit_full" rows="10">{$f_annotation|escape}</textarea></div>

            <label class="vici-label" for="kindselect">{$edit_label_kind|default:"Category"}</label>
            <select class="vici-select" id="kindselect" name="kindselect">
                {foreach from=$siteTypes item=t}
                    <option value="{$t.id}" {if $f_kind == $t.id}selected{/if}>{$t.title|escape}</option>
                {/foreach}
            </select>

            <div class="vici-full-row"><div id="editcanvas"></div></div>

            <label class="vici-label" for="coords_frm">{$edit_label_coords|default:"Coordinates"} <span class="vici-hint">lat, lng</span></label>
            <input class="vici-input" type="text" id="coords_frm" name="coords_frm" value="{$f_coords|escape}" required>

            <label class="vici-label" for="accuracyselect">{$edit_label_accuracy|default:"Location accuracy"}</label>
            <select class="vici-select" id="accuracyselect" name="accuracyselect">
                {foreach from=$accuracy_options key=i item=label}
                    <option value="{$i}" {if (string)$f_accuracy == (string)$i}selected{/if}>{$label|escape}</option>
                {/foreach}
            </select>

            <div id="periodbox">
                <table class="datenotation">
                    <caption>{$edit_date_examples|default:"examples of allowed date notations:"}</caption>
                    <tr><td>-123</td><td>{$edit_date_neg|default:"the year 123 BCE"}</td></tr>
                    <tr><td>post 70</td><td>{$edit_date_post|default:"after the year 70 CE"}</td></tr>
                    <tr><td>ante 405</td><td>{$edit_date_ante|default:"before the year 405 CE"}</td></tr>
                    <tr><td>3xx</td><td>{$edit_date_x|default:"the fourth century CE"}</td></tr>
                    <tr><td>70~</td><td>{$edit_date_approx|default:"approximately the year 70 CE"}</td></tr>
                    <tr><td>405?</td><td>{$edit_date_unc|default:"the year 405 CE, uncertain"}</td></tr>
                    <tr><td>47?~</td><td>{$edit_date_both|default:"approximately the year 47 CE, uncertain"}</td></tr>
                    <tr><td>unknown</td><td>{$edit_date_unknown|default:"unknown, for end dates only"}</td></tr>
                    <tr><td>open</td><td>{$edit_date_open|default:"no end date, has original function"}</td></tr>
                </table>

                <label class="vici-label" for="start_yr">{$edit_label_start|default:"Year of creation"} <span class="vici-hint">({$edit_required|default:"required"})</span></label>
                <input class="vici-input" type="text" id="start_yr" name="start_yr" pattern="{literal}(post |ante )?-?[0-9]{0,5}[x]{0,3}\??~?{/literal}" value="{$f_start|escape}">

                <label class="vici-label" for="end_yr">{$edit_label_end|default:"Year of destruction / abandonment"} <span class="vici-hint">({$edit_required|default:"required"})</span></label>
                <input class="vici-input" type="text" id="end_yr" name="end_yr" pattern="{literal}(post |ante )?(-?[0-9]{1,5}[x]{0,3}\??~?|open|unknown){/literal}" value="{$f_end|escape}">
            </div>

            <label class="vici-label" for="visibilityselect">{$edit_label_visibility|default:"Visibility"}</label>
            <select class="vici-select" id="visibilityselect" name="visibilityselect">
                <option value="1" {if $f_visible == 1}selected{/if}>{$edit_visible|default:"visible"}</option>
                <option value="0" {if $f_visible == 0}selected{/if}>{$edit_invisible|default:"invisible"}</option>
            </select>

            <label class="vici-label vici-full-row">{$edit_label_extids|default:"External identifiers (URIs)"}</label>
            <div class="vici-full-row" id="extids">
                {foreach from=$ext_ids key=i item=url}
                    <input class="vici-input" type="text" name="extid{$i}" value="{$url|escape}" placeholder="external URI" style="margin-bottom:0.25rem;">
                {/foreach}
            </div>
        </div>

        <div>
            <input type="submit" class="vici-btn" value="{$edit_submit|default:"Save"}">
        </div>
    </form>

    {elseif $edit_tab == 'image'}
    <form class="vici-form" method="post" action="/edit/{$pntid}/image" enctype="multipart/form-data">
        <div class="vici-form-grid">
            <label class="vici-label" for="file">{$edit_img_file|default:"Image file"} <span class="vici-hint">jpg, png, gif</span></label>
            <input class="vici-input" type="file" id="file" name="file" accept="image/jpeg,image/png,image/gif" required>

            <label class="vici-label" for="frm_title">{$edit_img_title|default:"Title"} <span class="vici-hint">({$edit_required|default:"required"})</span></label>
            <input class="vici-input" type="text" id="frm_title" name="frm_title" required>

            <label class="vici-label" for="frm_text">{$edit_img_desc|default:"Description"}</label>
            <textarea class="vici-textarea" id="frm_text" name="frm_text" rows="4"></textarea>

            <label class="vici-label" for="frm_ownership">{$edit_img_owner|default:"Owner / creator"}</label>
            <select class="vici-select" id="frm_ownership" name="frm_ownership">
                <option value="1">{$edit_img_ownwork|default:"I am the creator and owner"}</option>
                <option value="0">{$edit_img_other|default:"Somebody else is the owner / creator"}</option>
            </select>

            <div class="vici-full-row" id="ownwork_block">
                <label class="vici-label"><input type="checkbox" id="frm_agree" name="frm_agree"> {$edit_img_agree|default:"I agree to publish this image under the selected license"}</label>
            </div>

            <div class="vici-full-row" id="otherwork_block" style="display:none">
                <div class="vici-form-grid">
                    <label class="vici-label" for="frm_owner">{$edit_img_ownername|default:"Owner name"}</label>
                    <input class="vici-input" type="text" id="frm_owner" name="frm_owner">
                    <label class="vici-label" for="frm_source">{$edit_img_source|default:"Image source (URL)"}</label>
                    <input class="vici-input" type="text" id="frm_source" name="frm_source">
                    <label class="vici-label" for="frm_attribute">{$edit_img_attr|default:"Attribution"}</label>
                    <input class="vici-input" type="text" id="frm_attribute" name="frm_attribute">
                    <label class="vici-label vici-full-row"><input type="checkbox" id="frm_licensed" name="frm_licensed"> {$edit_img_licensed|default:"The image was published by the owner under the selected license, or the owner agreed to publication on Vici.org"}</label>
                </div>
            </div>

            <label class="vici-label" for="frm_license">{$edit_img_license|default:"License"}</label>
            <select class="vici-select" id="frm_license" name="frm_license">
                {foreach from=$licenses item=l}
                    <option value="{$l.id}" {if $l.id == 4}selected{/if}>{$l.title|escape}</option>
                {/foreach}
            </select>
        </div>

        <div>
            <input type="submit" class="vici-btn" value="{$edit_upload|default:"Upload"}">
        </div>
    </form>

    {elseif $edit_tab == 'lines'}
    <form class="vici-form" method="post" action="/edit/{$pntid}/lines" enctype="multipart/form-data">
        <p class="vici-hint">{$edit_lines_intro|default:"Upload a KML file to add line tracings for this site. All existing lines for this site will be replaced."}</p>

        <div class="vici-form-grid">
            <label class="vici-label" for="file">{$edit_lines_file|default:"KML file"}</label>
            <input class="vici-input" type="file" id="file" name="file" accept=".kml" required>

            <label class="vici-label" for="frm_linekind">{$edit_lines_kind|default:"Line kind"}</label>
            <select class="vici-select" id="frm_linekind" name="frm_linekind">
                <option value="2">{$edit_line_aqueduct|default:"Aqueduct"}</option>
                <option value="1">{$edit_line_road|default:"Road"}</option>
                <option value="3">{$edit_line_canal|default:"Canal"}</option>
                <option value="4">{$edit_line_wall|default:"Wall"}</option>
                <option value="5" selected>{$edit_line_other|default:"Other"}</option>
            </select>

            <label class="vici-label" for="frm_license_l">{$edit_img_license|default:"License"}</label>
            <select class="vici-select" id="frm_license_l" name="frm_license">
                {foreach from=$licenses item=l}
                    <option value="{$l.id}" {if $l.id == 4}selected{/if}>{$l.title|escape}</option>
                {/foreach}
            </select>

            <label class="vici-label" for="frm_owner_l">{$edit_img_ownername|default:"Owner name"}</label>
            <input class="vici-input" type="text" id="frm_owner_l" name="frm_owner">

            <label class="vici-label" for="frm_attribute_l">{$edit_img_attr|default:"Attribution"}</label>
            <input class="vici-input" type="text" id="frm_attribute_l" name="frm_attribute">
        </div>

        <div>
            <input type="submit" class="vici-btn" value="{$edit_upload|default:"Upload"}">
        </div>
    </form>
    {/if}
    </div>
    </div>
    {/if}
</main>
{/block}

{block name=footerscripts}
<script>
document.getElementById('frm_ownership')?.addEventListener('change', function () {
    var own = this.value === '1';
    document.getElementById('ownwork_block').style.display = own ? 'block' : 'none';
    document.getElementById('otherwork_block').style.display = own ? 'none' : 'block';
});
</script>
{if $edit_tab == 'data' && !(isset($show_kind_chooser) && $show_kind_chooser)}
<script src="/vendor/ckeditor/ckeditor/ckeditor.js"></script>
<script src="/js/edit.js"></script>
<script>
editPage();
(function () {
    var coordsField = document.getElementById('coords_frm');
    var lng = 12.5, lat = 41.9, zoom = 5;
    var parts = (coordsField.value || '').split(',');
    if (parts.length === 2 && !isNaN(parseFloat(parts[0])) && !isNaN(parseFloat(parts[1]))) {
        lat = parseFloat(parts[0]); lng = parseFloat(parts[1]); zoom = 14;
    } else {
        try {
            var session = JSON.parse(sessionStorage.getItem('session'));
            if (session && session.center) {
                lat = session.center.lat; lng = session.center.lng; zoom = 14;
                coordsField.value = lat + ', ' + lng;
            }
        } catch (e) {}
    }

    var mapObj = new MapWidget('editcanvas', 'coords_frm', lng, lat, '{$f_kind|default:1}');
    mapObj.setZoom(zoom);

    coordsField.addEventListener('blur', function() { mapObj.moveMarker(this.value); });

    var kindselect = document.getElementById('kindselect');
    var periodbox = document.getElementById('periodbox');
    function applyKind() {
        mapObj.setMarkerIcon(kindselect.value);
        var hide = (kindselect.value == 8 || kindselect.value == 18 || kindselect.value == 19);
        periodbox.style.display = hide ? 'none' : 'contents';
    }
    kindselect.addEventListener('change', applyKind);
    applyKind();
})();
</script>
{/if}
{/block}
