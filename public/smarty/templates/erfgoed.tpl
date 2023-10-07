<!DOCTYPE html>
<html lang="{$lang}">
<head>
    <title>Vici.org</title>
    <meta charset="UTF-8" />
    <meta name="description" content="{$description}" />
    <link rel="stylesheet" type="text/css" href="/css/vici.css" />
    <link rel="apple-touch-icon" href="apple-touch-icon.png">
    <link rel="search" type="application/opensearchdescription+xml" href="http://vici.org/osd.php" title="Search Vici.org" />
    {$scripts}
<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-31682789-1']);
  _gaq.push(['_setDomainName', 'vici.org']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
<script type="text/javascript">
    // need these for html5:
    document.createElement('header');
    document.createElement('footer');
    document.createElement('section');
    document.createElement('aside');
    document.createElement('nav');
    document.createElement('article');
</script>
</head>

<body onload="initialize();">    

        
    <div id="canvas" style="height:100%; position:absolute; top:0px; left:0px; right:0px;">
    
        
        <div id="main" style="position: absolute; left: 10px; top: 10px; right:10px">
            <!-- main text --> 
            <div style="position: relative; margin:5px;">
                
                <!-- content head -->      
                <header style="border-bottom: 1px solid #AAA; height:35px; position:relative">
                    <h1 id="kop" style="margin-right;270px; margin-top:4px; margin-bottom: 0px; font-size:1.6em; font-weight: normal;">{$pagetitle}</h1>
                </header>
                <!-- /content head --> 
                 
                <!-- central box -->
                <article style="width:100%; font-size:0.8em; position: relative;">
        
                {$main}
                    
                     
                </article>
                <!-- /central box -->
            
            
            </div>
            <!-- /main text -->     
            
        
        </div>
      
    </div> <!-- canvas --> 

    {block name=full}{/block}

  
</body>
</html>