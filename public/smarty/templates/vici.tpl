<!DOCTYPE html>
<html lang="{$lang}">
<head>
    <title>{if ! isset($sitesubtitle)}Vici.org - Archaeological Atlas of Antiquity{else}{$sitesubtitle} - Vici.org{/if}</title>
    <meta charset="UTF-8" />
    <meta itemprop="description" name="description" content="{$description}" />
    <link rel="stylesheet" type="text/css" href="/css/vici.css" />
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="sitemap" href="/sitemap.php" />
    <link rel="meta" type="application/rdf+xml" title="OAC" href="http://vici.org/void.rdf" />
    <link rel="search" type="application/opensearchdescription+xml" href="http://vici.org/osd.php" title="Vici.org" />
    <meta name="viewport" content="width=1140">
{$headmeta}
{$scripts}
</head>
<body>

    <div id="leftCol">
        <a href="/"><img id="logoImg" src="/images/vici_org.png" alt="Vici.org" /></a>
        <nav id="mainMenu">{$leftnav}</nav>
    </div>

    <div id="rightCol">

        <div id="pageHead">
            <header id="header">
                <h1>{$pagetitle}</h1>
            </header>
            <nav id="userBox">{$session}</nav>
            <div id="searchBox">
                <form action="/search.php">
                    <div id="searchField">
                        <input id="searchTerms" name="terms" value="{$terms}" placeholder="search" />
                        <button id="searchButton" name="button" title="Search"><img src="/images/search.png" alt="Search" /></button>
                    </div>
                </form>
            </div>
            {*{if isset($editmode)}*}
                <nav id="pageCmdBox">
                    <ul id="pageCmdLst">
                        {$editmenu}
                    </ul>
                </nav>
            {*{/if}*}
        </div>

        <div id="content">
            {block name=main}{/block}
        </div>

    </div>
    {$finalscripts}

</body>
</html>
