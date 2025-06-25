{extends file="base.tpl"}
{block name=main}


    <div id="maincontent" class="w-full md:w-2/3 space-y-4">
        <div id="map" class="bg-blue-200"></div>
        
        <div class="mb-8 ml-6 mr-6" id="textualcontent">
            

            <h1 class="mb-2 text-2xl text-blue-900 font-bold">In situ est locus</h1>   

            <!--  meta container -->
            <div class="meta-container">
                <div class="meta-left">
                    <div class="marker-icon w-[32px] h-[37px] bg-[-672px_0px]" title="A bridge"></div>
                </div>
                <div class="meta-right">
                <div>
                    <h3 class="text-md text-blue-900 font-semibold">{$location_metadata|default:"Location"}</h3>
                    <ul>
                    <li>{$location_name}</li>
                    <li>geo:{$lat},{$lng}</li>
                    <li>{$location_qualifier}</li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-md text-blue-900 font-semibold">{$period_metadata|default:"Period or year"}:</h3>
                    <ul>
                    <li>{$period_start_qualifier} /{$period_end_qualifier}</li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-md text-blue-900 font-semibold">{$classification_metadata|default:"Classification"}:</h3>
                    <ul>
                    <li>{$classification}</li>
                    <li>{$visibility}</li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-md text-blue-900 font-semibold">{$identifier_metadata|default:"Identifier"}:</h3>
                    <ul>
                    {foreach $identifiers as $id}
                      <li>{$id}</li>
                    {/foreach}
                    </ul>
                </div>
                </div>
            </div>

            <h2 class="mt-4 mb-2 text-lg text-blue-900 font-semibold">{$annotation_metadata|default:"Annotation"}   </h2>            

            <article id="txt_nl" class="article mb-8"><p>
                test: {$annotation}
            </article>


        </div> 

    </div> <!-- maincontent -->

    <div class="highlights w-full md:w-1/3 min-w-[240px]">
        <div class="flex flex-wrap justify-start">
            <figure class="item"><a href="//vici.org/image.php?id=1843"><img class="itemImage" loading="lazy" src="//images.vici.org/cover/w268xh268/uploads/rome_tabularium_from_east2.jpg" alt="" title="Tabularium " data-pswp-uid="0"></a></figure>
            <figure class="item"><a href="//vici.org/image.php?id=1843"><img class="itemImage" loading="lazy" src="//images.vici.org/cover/w268xh268/uploads/8430187532_62e87af0b7_b.jpg" alt="" title="Columns " data-pswp-uid="1"></a></figure>
        </div>
 
    </div> 


{/block}