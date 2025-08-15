{extends file="base.tpl"}
{block name=stylesheets}
<style>

.access-link {
  position: absolute;
  left: -9999px;
  top: -9999px;
}
main.item {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem;
}
.maincontent > * + * {
    margin-top: 1rem;
}
    
#map {
      max-height: 350px;
      background-color: #ccc;
    }

.textualcontent {
    margin-bottom: 2rem;
    margin-right: 1.5rem;
    margin-left: 1.5rem
}

.meta-container {
    display: flex;
    justify-content: space-between;
    align-items: stretch;
    gap: 0.5rem;
}

.meta-left {
    padding-top: 1.75rem;
    padding-right: 1rem;
    white-space: nowrap;
  }

  .meta-right {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    column-gap: 0.1rem;
    row-gap: 1rem;
    flex: 1;
  }

  .meta-right ul {
    list-style: none;
    margin-left: 0;
    margin-top: 0;
    padding-left: 0;
}

.lang-header-row {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    margin-bottom: 1em;
    border-bottom: 2px solid #ccc;
    position: relative;
}

#langSelBox ul {
    display: flex;
    gap: 0.5em;
    padding: 0;
    margin: 0;
    list-style: none;
}

#langSelBox li {
    display: block;
    padding: 0.3em 1.2em;
    border-top-left-radius: 0.6em;
    border-top-right-radius: 0.6em;
    background: #f2f2f2;
    color: #444;
    border: 1px solid #ccc;
    border-bottom: 2px solid #ccc;
    margin-bottom: -2px;
    cursor: pointer;
    transition: background 0.2s, color 0.2s;
    font-weight: 500;
    position: relative;
    z-index: 1;
}

#langSelBox li.selected {
    background: #fff;
    color: #222;
    border-color: #888 #888 #fff #888;
    border-bottom: 2px solid #fff;
    z-index: 2;
    position: relative;
}

#langSelBox li.disabled {
    color: #bbb;
    background: #f8f8f8;
}
article.selected {
    display: block;
}
article.disabled {
    display: none;
}

article a {
    text-decoration: underline;
    color: #1e3a8a;
}

article blockquote {
    font-variant: small-caps;
    font-family: 'Times New Roman', Times, serif
}

.imagecolumn {
    width: 100%;
    min-width: 240px;
}

.itemImages {
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-start;
}

.sitelist-container-flex {
    display: flex;
    flex-direction: row;
    align-items: flex-start;
    margin: 0;
    gap: 0;
}
.sitelist-container {
    display: flex;
    flex-direction: row;
    width: 100%;
    margin: 0;
    gap: 0;
}

.attributions {
    background-color: #f8fafc;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    padding: 1.25rem 1rem;
    margin: 0 2rem;
    margin-bottom: 0.5rem;
}
.attributions h2 {
    font-size: 1rem;
    color: #64748b;
    margin-top: 0;
    margin-bottom: 0.5rem;
}
.attributions p {
    color: #6b7280;
    font-size: 0.95em;
    margin: 0;
    line-height: 1.5;
}
.attributions a {
    color: #64748b;
    text-decoration: underline;
    font-size: 0.95em;
}
.attributions strong {
    color: #6b7280;
    font-weight: 600;
}

a.hover:hover {
    text-decoration: underline;
}

.identifier-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5em;
    padding: 0;
    margin: 0;
    list-style: none;
}
.identifier-list li {
    background: #f2f2f2;
    border-radius: 0.5em;
    padding: 0.1em 0.5em;
    display: flex;
    align-items: center;
    margin: 0;
}
.identifier-list li a {
    color: #333;
    text-decoration: none;
    font-family: monospace;
    font-size: 0.8em;
}
.identifier-list li a:hover {
    text-decoration: underline;
}

.image-column {
    display: grid;
    grid-template-columns: 1fr;
    gap: 0.5em;
}

.refanchor {
    margin-left: 0.5em;
}

.siteref {
    margin-right: 0.25em;
    text-decoration: none;
    font-weight: bold;
}


@media (max-width: 1600px) {
    .sitelist-container-flex {
        flex-direction: column;
        gap: 1rem;
    }
    .sitelist-container {
        gap: 1rem;
    }

}


@media (min-width: 768px) {

    {if $mainSite->images->count() > 0}
    main.item {
        grid-template-columns: 2fr 1fr;
        align-items: start;
    }
    {/if}
}

@media (min-width: 1024px) {
    {if $mainSite->images->count() > 2}
        main.item {
            grid-template-columns: 3fr 2fr;
            align-items: start;
        }
        .image-column {
            grid-template-columns: 1fr 1fr;
        }
    {/if}
}

@media (min-width: 1800px) {
    {if $mainSite->images->count() > 3}

    main.item {
        grid-template-columns: 1fr 1fr;
        align-items: start;
    }
    .image-column {
        grid-template-columns: 1fr 1fr 1fr;
    }
    {/if}
}

</style>
{/block}

{block name=metadata}
    <meta name="citation_title" content="{$mainSite->locales[$preferredLocaleLanguage]->title}">
    <meta name="citation_author" content="{$mainSite->creator->getLastName()}, {$mainSite->creator->getInitials()}">
    {if $mainSite->creator->getId() != $mainSite->updater->getId()}<meta name="citation_author" content="{$mainSite->updater->getLastName()}, {$mainSite->updater->getInitials()}">{/if}
    
    <meta name="citation_publication_date" content="{$mainSite->createDate|date_format:"%Y-%m-%d"}">
    <meta name="citation_online_date" content="{$mainSite->updateDate|date_format:"%Y-%m-%d"}">
    <meta name="citation_public_url" content="https://vici.org/vici/{$mainSite->id}">
    <meta name="citation_access_date" content="{$smarty.now|date_format:"%Y-%m-%d"}">
{/block}

{block name=headscripts}
    {include file="include/lightbox_init.tpl"}
{/block}

{block name=main}
    <main class="item">
        <div class="maincontent">
            <div id="map"></div>
            
            <div class="textualcontent">
            
                <div class="meta-container">
                    <div class="meta-left">
                        <div class="marker-icon" id="myIcon" data-icon-type="{$mainSite->type->id}" title="{$classification_description}"></div>
                    </div>
                    <div class="meta-right">
                        <div>
                            <h3>{$location_metadata|default:"Location"}</h3>
                            <ul>
                            <li>{$mainSite->toponym->countryName[$sessionLanguage]}, {$mainSite->toponym->placeName[$sessionLanguage]}</li>
                            <li>geo:{$mainSite->representativeLocation->latitude},{$mainSite->representativeLocation->longitude}</li>
                            <li>{include file="include/location_accuracy.tpl" accuracy=$mainSite->representativeLocation->qualifier}</li>
                            </ul>
                        </div>
                        {if !$mainSite->type->isContemporary}
                            <div>
                                <h3>{$period_metadata|default:"Period or year"}</h3>
                                <ul>
                                <li>{if $mainSite->period->startQualifier || $mainSite->period->endQualifier} {$mainSite->period->startQualifier} / {$mainSite->period->endQualifier}{else}{$not_yet_provided_label|default:"not yet provided"}{/if}</li>
                                </ul>
                            </div>
                        {/if}
                        <div>
                            <h3>{$classification_metadata|default:"Classification"}</h3>
                            <ul>
                            <li title="{$classification_description}">{$classification_title}</li>
                            {if !$mainSite->type->isContemporary}
                                <li>{include file="include/is_visible.tpl" isVisible=$mainSite->isVisible}</li>
                            {/if}
                            </ul>
                        </div>
                        {if $mainSite->identifiers->count() > 0}
                            <div>
                                <h3>{$identifier_metadata|default:"Identifiers"}</h3>
                                <ul class="identifier-list">
                                {foreach $mainSite->identifiers as $identifier}
                                <li><a href="{$identifier->uri}" class="hover">{$identifier->getQname()}</a></li>
                                {/foreach}
                                </ul>
                            </div>
                        {/if}
                    </div>
                </div>

                <div class="lang-header-row">
                    <h1>{$mainSite->locales[$preferredLocaleLanguage]->title}</h1>
                    <nav id="langSelBox">
                        <ul>
                            {foreach from=$mainSite->locales key=langKey item=langVal}
                                {if $langVal->description}
                                    <li class="{if $langKey == $preferredLocaleLanguage}selected{else}disabled{/if}" id="xt_{$langKey}">{$langKey|upper}</li>
                                {/if}
                            {/foreach}
                        </ul>
                    </nav>
                </div>

                <a href="/data-access/{$mainSite->id}" class="access-link">data access {$mainSite->id}</a>

                {foreach from=$mainSite->locales key=langKey item=langVal}
                    {if $langVal->description}
                        <article id="txt_{$langKey}" class="{if $langKey == $preferredLocaleLanguage}selected{else}disabled{/if}">
                            {$langVal->description|description_as_html}
                        </article>
                    {/if}
                {/foreach}


                <div class="sitelist-container-flex">
                    {if $relevantMuseums->count() > 0 && !$site_type->isContemporary}
                        <div class="sitelist">
                            <h2>{$relevant_museums_label|default:"Relevant museums"}</h2>
                            <ul>
                                {foreach from=$relevantMuseums item=site}
                                    <li>
                                        {include file="include/site_list_item.tpl"}
                                    </li>
                                {/foreach}
                            </ul>
                        </div>
                    {/if}
                    {if $nearbySites->count() > 0}
                        <div class="sitelist">
                            <h2>{$nearby_sites_label|default:"Nearby sites"}</h2>
                            <ul>
                                {foreach from=$nearbySites item=site}
                                    {if $site->id != $mainSite->id}
                                        <li>
                                            {include file="include/site_list_item.tpl"}
                                        </li>
                                    {/if}
                                {/foreach}
                            </ul>
                        </div>
                    {/if}
                </div>
            </div> 

        </div> 

        {if $mainSite->images->count() > 0}
            <div class="image-column" id="my-gallery">
                {foreach from=$mainSite->images item=image}
                    {include file="include/lightbox_image.tpl" image=$image imageSizeUrlPrefix="cover/w440xh248"}
                {/foreach}
            </div>
        {/if}

    </main>

    <div class="attributions">
        <h2>{$use_and_reuse_label|default:"Use and reuse"}</h2>
        <p>
            <strong>{$creators_label|default:"Creators"}:</strong> {$added_label|default:"Entry created by"} {$mainSite->creator->getRealName()} ({$mainSite->createDate|date_format:"%Y-%m-%d"})
            {if $mainSite->creator->getId() != $mainSite->updater->getId()}, {$updated_label|default:"last updated by"} {$mainSite->updater->getRealName()} ({$mainSite->updateDate|date_format:"%Y-%m-%d"}){/if},
            {$with_others_label|default:"with possible contributions by others"}.
            <a href="https://creativecommons.org/licenses/by-sa/4.0/" target="_blank">CC BY-SA 4.0</a>, metadata <a href="https://creativecommons.org/publicdomain/zero/1.0/" target="_blank">CC-0</a>.<br>
            <strong>{$persistent_URI_label|default:"Persistent URI"}</strong>: <a class="underline" href="https://vici.org/vici/{$mainSite->id}">https://vici.org/vici/{$mainSite->id}</a><br>

            <strong>{$data_access_label|default:"Data downloads"}</strong>: <a href="/vici/{$mainSite->id}/kml">KML</a><br>

            <strong>{$suggested_citation_label|default:"Suggested citation"}</strong>: <em>{$mainSite->creator->getLastName()}, {$mainSite->creator->getInitials()}
                    {if $mainSite->creator->getId() != $mainSite->updater->getId()}{$and_label|default:"and"} {$mainSite->updater->getLastName()}, {$mainSite->updater->getInitials()}{/if},
                    {$mainSite->locales[$preferredLocaleLanguage]->title}.</em> <span class="underline">https://vici.org/vici/{$mainSite->id}</span>, {$accessed_label|default:"accessed"} {$smarty.now|date_format:"%Y-%m-%d"}.
        </p>
    </div>

{/block}

{block name=footerscripts}
    <script src="/js/footer-item.js?v={$smarty.now|date_format:"%Y%m%d"}"></script>
{/block}