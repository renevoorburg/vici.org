<!DOCTYPE html>
<html lang="{$lang}">
<head>
    <title>Vici.org - {$sitesub}</title>
    <meta charset="UTF-8" />
    <meta name="description" content="{$description}" />
    <link rel="stylesheet" type="text/css" href="/css/vici.css" />
    <link rel="apple-touch-icon" href="apple-touch-icon.png">
    <link rel="search" type="application/opensearchdescription+xml" href="http://vici.org/osd.php" title="Vici.org" />
    {$scripts}

</head>

<body onload="initialize();">    
    
    <div id="header" style="height:80px; width:100%; background-color: white;">
        <nav style="float:right; font-size:0.75em; margin:5px; margin-right:16px; ">
        {$session}
        </nav>
        <img style="float:left; margin-top: 12px; margin-left:15px" src="/images/vici_org.png" />
        
        <!--search-->
        <div style="float:right; clear:right;">
            <form action="/search.php">
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

    <!--
    <nav>
        <div id="errorbox" style="display:none; background-color:#FDD; position:absolute; top:5px; right:270px; left:180px;z-index:1">
            <div id="xxxxmessages" style="border: 2px solid #FF3333; color: #FF0000; margin:5px; padding:5px">{$errorMsg}</div>
            <img src="/images/close-button.png" onclick="closeErrorBox()" style="position:absolute; right:9px; top:9px;" />
        </div>
        
        <div id="successbox" style="display:none; background-color:#DFD; position:absolute; top:5px; right:270px; left:180px;z-index:1">
            <div style="border: 2px solid #33FF33; color: #006600; margin:5px; padding:5px">Saved successfully.</div>
            <img src="/images/close-button.png" onclick="closeSuccessBox()" style="position:absolute; right:9px; top:9px;" />
        </div>
    </nav>
    -->
  
</body>
</html>