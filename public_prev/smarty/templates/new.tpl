<!DOCTYPE html>
<html lang="{$lang}">
<head>
    <title>Vici.org - {$sitesub}</title>
    <meta charset="UTF-8" />
    <meta name="description" content="{$description}" />
    <link rel="stylesheet" type="text/css" href="/css/vici.css" />
    <link rel="apple-touch-icon" href="apple-touch-icon.png">
    <link rel="sitemap" href="/sitemap.php" />
    <link rel="search" type="application/opensearchdescription+xml" href="/osd.php" title="Vici.org" />
    {$scripts}
<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-31682789-1']);
  _gaq.push(['_setDomainName', 'vici.org']);
  _gaq.push(['_trackPageview']);

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

<body>    
    
    <div id="header" style="height:80px; width:100%; background-color: white;">
        <nav style="float:right; font-size:0.75em; margin:5px; margin-right:16px; ">
        {$session}
        </nav>
        <img style="float:left; margin-top: 12px; margin-left:15px" src="/images/vici_org.png" />
        
        <!--search-->
        <div style="float:right; clear:right;">
            <form action="search.php">
                <div id="simpleSearch" style="border-style: solid;border-width: 1px; border-color:#AAA;margin-right:11px; margin-top:16px; background-color: #FAFAFA; width:268px; position:relative; ">
                    <input name="terms" value="{$terms}" placeholder="search" style="border-style:none; border-width:0px; margin:0px; margin-left:4px; background-color: transparent; width:245px" />	
                    <button name="button" title="Search" id="searchButton" style="background-color: transparent; border:none; background-image: none; position:absolute; right:0px; top:0px; width:20px;"><img src="/images/search.png" alt="Search" /></button>					
                    
                </div>
            </form>
        </div>
        <!--/search-->
        
    </div>
        
    <div id="canvas" style="height:100%; position:absolute; top:80px; left:0px; right:0px;">
    
        <nav id="leftnav" style="position: absolute; left: 0px; top: 0px; width:180px; background-color:white; padding:20px 5px 20px 18px; line-height: 1.5em; font-size: 0.75em; ">
        <!--leftnav-->
        {$leftnav}
        <!--/leftnav-->
        </nav>
        
        <div id="main" style="position: absolute; left: 180px; top: 0px; right:0px">
            <!-- main text --> 
            <div style="position: relative; margin:5px;">
                
                <!-- content head -->      
                <header style="border-bottom: 1px solid #AAA; height:35px; position:relative">
                    <h1 id="kop" style="margin-right;270px; margin-top:4px; margin-bottom: 0px; font-size:1.6em; font-weight: normal;">{$pagetitle}</h1>
                </header>
                <!-- /content head --> 
                 
                <!-- central box -->
                <article style="width:100%; font-size:0.8em; position: relative;">
        
                {block name=main}{/block}
                    
                     
                </article>
                <!-- /central box -->
            
            
            </div>
            <!-- /main text -->     
            
            <footer style="border-top: 1px solid #AAA; margin:28px 5px 22px 5px;font-size:0.7em;clear:right">
                <p style="line-height:1.5em; margin-top:5px;">
                {$footer}
                </p>
            </footer>
        
        </div>
      
    </div> <!-- canvas --> 

    {block name=full}{/block}

    
  
</body>
</html>