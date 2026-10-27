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
    {block name=headscripts}{/block}
</head>

<body>
<header>
    <div><a href="/">Vici.org</a><span class="subtitle">:: {$sitesubtitle|default:"archaeological atlas"}</span></div>
    <button id="menu-button">&#9776;</button>
    <nav id="main-menu">
        <form action="/search" method="get" class="search-form">
            <input type="text" name="q"
                value="{$query|default:''|escape:'html'}"
                placeholder="{$search_placeholder|default:"search"}..." 
            />
        </form>
        <a href="/add">{$add_menu_item|default:"Add"}</a>
        {if isset($username)}
            <div class="user-menu">
                <button type="button" id="user-menu-button" aria-haspopup="true" aria-expanded="false">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    {$username}
                </button>
                <div class="user-menu-dropdown">
                    <a href="/settings"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg> {$settings_menu_item|default:"Settings"}</a>
                    <a href="/logout"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg> {$logout_menu_item|default:"Logout"}</a>
                </div>
            </div>
        {else}
            <a href="/login">{$login_menu_item|default:"Login / Register"}</a>
        {/if}
    </nav>
</header>

<div id="mobile-menu">
    <a href="/add">{$add_menu_item|default:"Add"}</a>
    {if isset($username)}
        <a href="/settings">{$settings_menu_item|default:"Settings"}</a>
        <a href="/logout">{$logout_menu_item|default:"Logout"}</a>
    {else}
        <a href="/login">{$login_menu_item|default:"Login / Register"}</a>
    {/if}
    <form action="/search" method="get" class="search-form">
        <input type="text" name="q"
            value="{$query|default:''|escape:'html'}"
            placeholder="{$search_placeholder|default:"search"}..."
        />
    </form>
</div>

{block name=main}{/block}

<footer id="footer">
    <div class="footerbox">
        <div id="footerhead">{$footer_title|default:"More about Vici.org"}<span id="footer-arrow">↓</span></div>
        <div class="footercontent">
            <div class="footerline">
                <a href="/additions" class="flex items-center gap-2">{$footer_new|default:"Recently added"}</a>
                <a href="/changes" class="flex items-center gap-2">{$footer_changed|default:"Recently changed"}</a>
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

{include file="include/javascript_globals.tpl"}

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

    const userMenuButton = document.getElementById('user-menu-button');
    if (userMenuButton) {
        const dropdown = userMenuButton.nextElementSibling;
        userMenuButton.addEventListener('click', (e) => {
            e.stopPropagation();
            const open = dropdown.classList.toggle('open');
            userMenuButton.setAttribute('aria-expanded', open);
        });
        document.addEventListener('click', () => dropdown.classList.remove('open'));
    }
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