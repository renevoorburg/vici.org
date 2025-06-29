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
      height: 350px;
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
    align-items: center;
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
    margin-top: 10px; 
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
    cursor: not-allowed;
}

@media (min-width: 768px) {
    .maincontent {
        width: 70%;    

    }
}


    </style>
{/block}


{block name=main}
    <main class="item">
        <div class="maincontent">
            <div id="map"></div>
            
            <div class="textualcontent">
                

                <h1>{$title}</h1>   

                <!--  meta container -->
                <div class="meta-container">
                    <div class="meta-left">
                        <div class="marker-icon" data-icon-type="{$icon_type}" title="{$iconTitle}"></div>
                    </div>
                    <div class="meta-right">
                    <div>
                        <h3>{$location_metadata|default:"Location"}</h3>
                        <ul>
                        <li>{$country_name}, {$place_name}</li>
                        <li>geo:{$lat},{$lng}</li>
                        <li>{include file="include/location_accuracy.tpl" accuracy=$q}</li>
                        </ul>
                    </div>
                    <div>
                        <h3>{$period_metadata|default:"Period or year"}</h3>
                        <ul>
                        <li>{$period_start_qualifier} / {$period_end_qualifier}</li>
                        </ul>
                    </div>
                    <div>
                        <h3>{$classification_metadata|default:"Classification"}</h3>
                        <ul>
                        <li title="{$classification_description}">{$classification_title}</li>
                        <li>{include file="include/is_visible.tpl" isVisible=$isVisible}</li>
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
                    <h2>{$annotation_metadata|default:"Annotation"}</h2>
                    <nav id="langSelBox">
                        <ul>
                            <li class="selected" id="xt_nl">NL</li>
                            <li class="disabled" id="xt_en">EN</li>
                            <li class="disabled" id="xt_de">DE</li>
                            <li class="disabled" id="xt_fr">FR</li>
                        </ul>
                    </nav>
                </div>

                <article id="txt_nl"><p>
                    {$annotation}
                </article>


            </div> 

        </div> 

        <div class="highlights w-full md:w-1/3 min-w-[240px]">
            <div class="flex flex-wrap justify-start">
                <figure class="item"><a href="//vici.org/image.php?id=1843"><img class="itemImage" loading="lazy" src="//images.vici.org/cover/w268xh268/uploads/rome_tabularium_from_east2.jpg" alt="" title="Tabularium " data-pswp-uid="0"></a></figure>
                <figure class="item"><a href="//vici.org/image.php?id=1843"><img class="itemImage" loading="lazy" src="//images.vici.org/cover/w268xh268/uploads/8430187532_62e87af0b7_b.jpg" alt="" title="Columns " data-pswp-uid="1"></a></figure>
            </div>
    
        </div> 

    </main>

{/block}