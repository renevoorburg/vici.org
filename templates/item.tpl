{extends file="base.tpl"}

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
    <main class="item{if $mainSite->images->count() > 0} item--img{/if}{if $mainSite->images->count() > 2} item--img3{/if}{if $mainSite->images->count() > 3} item--img4{/if}">
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

                {if isset($username)}
                <nav class="edit-menu">
                    <a href="/edit/{$mainSite->id}">{$edit_menu_edit|default:"Edit"}</a>
                    <a href="/edit/{$mainSite->id}/image">{$edit_menu_image|default:"Add image"}</a>
                    <a href="/edit/{$mainSite->id}/lines">{$edit_menu_lines|default:"Add lines"}</a>
                </nav>
                {/if}

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

            <strong>{$data_access_label|default:"Data downloads"}</strong>: <a href="/vici/{$mainSite->id}/kml">KML</a>, <a href="https://data.digitopia.nl/uri:http://vici.org/vici/{$mainSite->id}">RDF</a><br>

            <strong>{$suggested_citation_label|default:"Suggested citation"}</strong>: <em>{$mainSite->creator->getLastName()}, {$mainSite->creator->getInitials()}
                    {if $mainSite->creator->getId() != $mainSite->updater->getId()}{$and_label|default:"and"} {$mainSite->updater->getLastName()}, {$mainSite->updater->getInitials()}{/if},
                    {$mainSite->locales[$preferredLocaleLanguage]->title}.</em> <span class="underline">https://vici.org/vici/{$mainSite->id}</span>, {$accessed_label|default:"accessed"} {$smarty.now|date_format:"%Y-%m-%d"}.
        </p>
    </div>

{/block}

{block name=footerscripts}
    <script src="/js/footer-item.js?v={$smarty.now|date_format:"%Y%m%d"}"></script>
{/block}