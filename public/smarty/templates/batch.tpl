{extends file="main.tpl"}
{block name=full}

<div id="full" style="position: absolute; top: 122px; left: 185px; right: 5px; height:800px; font-size:0.8em; background: #f0f0f0;">
   
    <div id="map_canvas" style="position:absolute; top:0px; right:0px; left:0px; height:280px;">roman empire map</div>

    <div class="buttonbar" style="position: absolute; left:0px; right:0px; top:280px; height:22px;background: #cccccc">{$buttonbar}</div>

    <div id="rightcol" style="position:absolute; top:6px; right:6px; width:270px; color: #fff; font-family: Helvetica; font-size: 12px; background-image: url(/images/black_50.png);">
        {$rightcol}
    </div>
    
    <div style="position: absolute; left:5px; right:5px; top:304px; height:400px;">
        {$forms}
    </div>
    

</div>

{/block}

{block name=main}
    <div>
        {$below}
    </div>
{/block}
     