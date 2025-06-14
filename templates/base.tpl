<!DOCTYPE html>
<html lang="{$lang}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Vici.org :: {$title|default:"archaeological atlas"}</title>
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/js/ol/v4.6.5/css/ol.css">
    <script src="/js/ol/v4.6.5/ol.js"></script>
    <script src="/js/vici.js?version=20250614"></script>

</head>

<body>
<header>
    <div>Vici.org<span class="subtitle">:: {$sitesubtitle|default:"archaeological atlas"}</span></div>
    <button id="menu-button">&#9776;</button>
    <nav id="main-menu">
        <input type="text" placeholder="{$search_placeholder|default:"search"}..." />
        <a href="/add">{$add_menu_item|default:"Add"}</a>
        <a href="/login">{$login_menu_item|default:"Login / Register"}</a>
    </nav>
</header>

<div id="mobile-menu">
    <a href="/add">{$add_menu_item|default:"Add"}</a>
    <a href="/login">{$login_menu_item|default:"Login / Register"}</a>
    <input type="text" placeholder="{$search_placeholder|default:"search"}..." />
</div>

<main>
    {block name=main}{/block}
</main>

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
                <a href="https://mastodon.social/@vici" class="footer-logolink">
                    <img src="https://joinmastodon.org/logos/logo-purple.svg" class="footer-logo" alt="Mastodon" />
                    Mastodon
                </a>
                <a href="https://livius.org" class="footer-logolink">
                    <img src="https://www.livius.org/favicon.ico" class="footer-logo" alt="Livius" />
                    Livius.org
                </a>
            </div>
            <div class="languageselector space-x-4">
                <a href="#">NL</a><a href="#">DE</a><a href="#">FR</a><a href="#">EN</a>
            </div>
        </div>
    </div>
</footer>

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
<script src="/js/home.js"></script>
</body>

</html>