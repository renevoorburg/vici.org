<!DOCTYPE html>
<html lang="{$lang}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Vici.org :: {$title|default:"archaeological atlas"}</title>
    {block name=metadata}{/block}
    <link rel="stylesheet" href="/css/main.css">
    {block name=stylesheets}{/block}
    <link rel="stylesheet" href="/js/ol/v4.6.5/css/ol.css">
    <script src="/js/ol/v4.6.5/ol.js"></script>
    <script src="/js/vici.js?v={$smarty.now|date_format:"%Y%m%d"}"></script>
</head>

<body>
<header>
    <div><a href="/">Vici.org</a><span class="subtitle">:: {$sitesubtitle|default:"archaeological atlas"}</span></div>
    <button id="menu-button">&#9776;</button>
    <nav id="main-menu">
        <input type="text" placeholder="{$search_placeholder|default:"search"}..." />
        <a href="/add">{$add_menu_item|default:"Add"}</a>
        {if isset($username)}
            <a href="/logout">{$logout_menu_item|default:"Logout"}</a>
        {else}
            <a href="/login">{$login_menu_item|default:"Login / Register"}</a>
        {/if}
    </nav>
</header>

<div id="mobile-menu">
    <a href="/add">{$add_menu_item|default:"Add"}</a>
    <a href="/login">{$login_menu_item|default:"Login / Register"}</a>
    <input type="text" placeholder="{$search_placeholder|default:"search"}..." />
</div>

{block name=metadata}{/block}
{block name=main}{/block}

<footer id="footer">
    <div class="footerbox">
        <div id="footerhead">{$footer_title|default:"More about Vici.org"}<span id="footer-arrow">↓</span></div>
        <div class="footercontent">
            <div class="footerline">
                <a href="/new" class="flex items-center gap-2">{$footer_new|default:"Recently added"}</a>
                <a href="/changed" class="flex items-center gap-2">{$footer_changed|default:"Recently changed"}</a>
                <a href="/about" class="flex items-center gap-2">{$footer_about|default:"About Vici.org"}</a>
            </div>
            <div class="footerline">
                <a href="https://github.com/renevoorburg/vici.org" class="footer-logolink">
                    <img src="https://github.githubassets.com/images/modules/logos_page/GitHub-Mark.png"
                         class="footer-logo" alt="GitHub" />
                    GitHub
                </a>
                <a href="https://archaeo.social/@vici" class="footer-logolink">
                    <img src="https://joinmastodon.org/logos/logo-purple.svg" class="footer-logo" alt="Mastodon" />
                    Mastodon
                </a>
                <a href="https://livius.org" class="footer-logolink">
                    <img src="https://www.livius.org/favicon.ico" class="footer-logo" alt="Livius" />
                    Livius.org
                </a>
            </div>
            <div class="languageselector space-x-4">
                {foreach $availableLanguages as $lang}
                <a href="?lang={$lang}">{$lang}</a>
                {/foreach}
            </div>
        </div>
    </div>
</footer>

<script>
    window.viciToken = "{$viciToken}";
    window.sessionLanguage = "{$sessionLanguage}";
    {if isset($vicibase)}
        window.viciBase = "{$vicibase}";
    {/if}
    {if isset($id)}
        window.siteId = "{$id}";
    {/if}
    {if isset($location) && isset($location->latitude) && isset($location->longitude)}
        window.lat = {$location->latitude};
        window.lng = {$location->longitude};
    {/if}
    {if isset($js_translations)}
        window.viciTranslations = {$js_translations};
    {/if}
</script>
<script>
    function toggleMobileMenu() {
        const menu = document.getElementById('mobile-menu');
        if (menu.style.display === 'none' || menu.style.display === '') {
            menu.style.display = 'flex';
        } else {
            menu.style.display = 'none';
        }
    }
    document.getElementById('menu-button').addEventListener('click', toggleMobileMenu);
</script>
<script>
    let isAtFooter = false;

    function toggleFooter() {
        const footer = document.getElementById('footer');
        const footerArrow = document.getElementById('footer-arrow');

        if (isAtFooter) {
            window.scrollTo({ top: 0, behavior: 'smooth' });
            footerArrow.textContent = '↓';
            isAtFooter = false;
        } else {
            footer.scrollIntoView({ behavior: 'smooth' });
            footerArrow.textContent = '↑';
            isAtFooter = true;
        }
    }
    document.getElementById('footerhead').addEventListener('click', toggleFooter);
</script>
{block name=footerscripts}{/block}
</body>
</html>