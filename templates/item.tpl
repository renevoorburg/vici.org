{extends file="base.tpl"}
{block name=stylesheets}
    <style>

main.item {
      display: flex;
      flex: none;
      width: 100%;

.maincontent {
    display: flex;
    flex-direction: column;
    flex: 1 1 auto; 
    width: 100%;  
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
    /* color: white; */
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
    list-style: none;      /* geen bullets */
    margin-left: 0;        /* geen marge links */
    margin-top: 0;         /* geen marge boven */
    padding-left: 0;       /* geen inspringing */
}

.lang-header-row {
    display: flex;
    align-items: flex-end; /* tabs onderaan uitlijnen */
    justify-content: space-between;
    margin-bottom: 1em;
    border-bottom: 2px solid #ccc; /* lijn onder de hele rij */
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

.imagecolumn {
    width: 100%;
    min-width: 240px;
}

.itemImages {
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-start;
}

@media (min-width: 768px) {
    .maincontent {
        width: 70%;    

    }
    .imageColumn {
        width: 30%;
    }
}


    </style>
{/block}


{block name=main}
    <main class="item">
        <div class="maincontent">
            <div id="map"></div>
            
            <div class="textualcontent">
            
                <div class="meta-container">
                    <div class="meta-left">
                        <div class="marker-icon" data-icon-type="{$icon_type}" title="{$iconTitle}"></div>
                    </div>
                    <div class="meta-right">
                    <div>
                        <h3>{$location_metadata|default:"Location"}</h3>
                        <ul>
                        <li>{$toponym->countryName[$sessionLanguage]}, {$toponym->placeName[$sessionLanguage]}</li>
                        <li>geo:{$lat},{$lng}</li>
                        <li>{include file="include/location_accuracy.tpl" accuracy=$q}</li>
                        </ul>
                    </div>
                    {if !$isContemporary}
                        <div>
                            <h3>{$period_metadata|default:"Period or year"}</h3>
                            <ul>
                            <li>{$period_start_qualifier} / {$period_end_qualifier}</li>
                            </ul>
                        </div>
                    {/if}
                    <div>
                        <h3>{$classification_metadata|default:"Classification"}</h3>
                        <ul>
                        <li title="{$classification_description}">{$classification_title}</li>
                        {if !$isContemporary}
                            <li>{include file="include/is_visible.tpl" isVisible=$isVisible}</li>
                        {/if}
                        </ul>
                    </div>
                    <div>
                        <h3>{$identifier_metadata|default:"Identifiers"}</h3>
                        <ul>
                        <li><a href="/vici/{$id}">vici:{$id}</a></li>
                        {foreach $identifiers as $id}
                        <li>{$id}</li>
                        {/foreach}
                        </ul>
                    </div>
                    </div>
                </div>

                <div class="lang-header-row">
                    <h1>{$title}</h1>
                    <nav id="langSelBox">
                        <ul>
                            {foreach from=$locales key=langKey item=langVal}
                                {if $langVal->description}
                                    <li class="{if $langKey == $preferredLocale}selected{else}disabled{/if}" id="xt_{$langKey}">{$langKey|upper}</li>
                                {/if}
                            {/foreach}
                        </ul>
                    </nav>
                </div>

                {foreach from=$locales key=langKey item=langVal}
                    {if $langVal->description}
                        <article id="txt_{$langKey}" class="{if $langKey == $preferredLocale}selected{else}disabled{/if}">
                            {$langVal->description}
                        </article>
                    {/if}
                {/foreach}


                {if $nearbySites->count() > 0}
                    <div class="nearbySites">
                        <h2>Nearby sites</h2>
                        <ul>
                            {foreach from=$nearbySites item=site}
                                {if $site->id != $id}
                                    <li>{$site->defaultTitle}</li>
                                {/if}
                            {/foreach}
                        </ul>
                    </div>
                {/if}

                {if $relevantMuseums->count() > 0 && !$isContemporary}
                    <div class="relevantMuseums">
                        <h2>Relevant museums</h2>
                        <ul>
                            {foreach from=$relevantMuseums item=site}
                                <li>{$site->defaultTitle}</li>
                            {/foreach}
                        </ul>
                    </div>
                {/if}


            </div> 

        </div> 

        <div class="highlights-column">
            <div class="highlights-scroll">
            {foreach from=$images item=image}
                <figure><img loading="lazy" src="//images.vici.org/cover/w268xh268{$image->filepath}" alt="{$image->title} {$image->description}" title="{$image->title}" data-pswp-uid="{$image->id}"></figure>
            {/foreach}
            </div>
    
        </div> 

    </main>
{/block}

{block name=footerscripts}
    <script src="/js/footer-item.js"></script>
{/block}