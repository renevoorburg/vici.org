<?php
/* Smarty version 4.5.5, created on 2025-06-14 15:45:16
  from '/var/www/local.vici.novo/templates/base.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.5.5',
  'unifunc' => 'content_684d990cbeb475_56883827',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'dbeb7e7b11ab6f471d3a297cc72a42f44d7e5575' => 
    array (
      0 => '/var/www/local.vici.novo/templates/base.tpl',
      1 => 1749915909,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_684d990cbeb475_56883827 (Smarty_Internal_Template $_smarty_tpl) {
?><!DOCTYPE html>
<html lang="<?php echo $_smarty_tpl->tpl_vars['lang']->value;?>
">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Vici.org :: <?php echo (($tmp = $_smarty_tpl->tpl_vars['title']->value ?? null)===null||$tmp==='' ? "archaeological atlas" ?? null : $tmp);?>
</title>
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/js/ol/v4.6.5/css/ol.css">
    <?php echo '<script'; ?>
 src="/js/ol/v4.6.5/ol.js"><?php echo '</script'; ?>
>
    <?php echo '<script'; ?>
 src="/js/vici.js?version=20250614"><?php echo '</script'; ?>
>

</head>

<body>
<header>
    <div>Vici.org<span class="subtitle">:: <?php echo (($tmp = $_smarty_tpl->tpl_vars['sitesubtitle']->value ?? null)===null||$tmp==='' ? "archaeological atlas" ?? null : $tmp);?>
</span></div>
    <button id="menu-button">&#9776;</button>
    <nav id="main-menu">
        <input type="text" placeholder="<?php echo (($tmp = $_smarty_tpl->tpl_vars['search_placeholder']->value ?? null)===null||$tmp==='' ? "search" ?? null : $tmp);?>
..." />
        <a href="/add"><?php echo (($tmp = $_smarty_tpl->tpl_vars['add_menu_item']->value ?? null)===null||$tmp==='' ? "Add" ?? null : $tmp);?>
</a>
        <a href="/login"><?php echo (($tmp = $_smarty_tpl->tpl_vars['login_menu_item']->value ?? null)===null||$tmp==='' ? "Login / Register" ?? null : $tmp);?>
</a>
    </nav>
</header>

<div id="mobile-menu">
    <a href="/add"><?php echo (($tmp = $_smarty_tpl->tpl_vars['add_menu_item']->value ?? null)===null||$tmp==='' ? "Add" ?? null : $tmp);?>
</a>
    <a href="/login"><?php echo (($tmp = $_smarty_tpl->tpl_vars['login_menu_item']->value ?? null)===null||$tmp==='' ? "Login / Register" ?? null : $tmp);?>
</a>
    <input type="text" placeholder="<?php echo (($tmp = $_smarty_tpl->tpl_vars['search_placeholder']->value ?? null)===null||$tmp==='' ? "search" ?? null : $tmp);?>
..." />
</div>

<main>
    <div id="map"></div>

    <div class="highlights-column">
        <div class="highlights-scroll">
            <div id="selectionbox-content">
                <!-- selection -->
            </div>
            <div class="highlights-content">
                <h2><?php echo (($tmp = $_smarty_tpl->tpl_vars['highlights_title']->value ?? null)===null||$tmp==='' ? "Highlights" ?? null : $tmp);?>
</h2>
                <div id="highlights-items" class="space-y-3">
                    <!-- highlights -->
                </div>
            </div>
        </div>
    </div>

</main>

<footer id="footer">
    <div class="footerbox">
        <div id="footerhead"><?php echo (($tmp = $_smarty_tpl->tpl_vars['footer_title']->value ?? null)===null||$tmp==='' ? "More about Vici.org" ?? null : $tmp);?>
<span id="footer-arrow">↓</span></div>
        <div class="footercontent">
            <div class="footerline">
                <a href="/new" class="flex items-center gap-2"><?php echo (($tmp = $_smarty_tpl->tpl_vars['footer_new']->value ?? null)===null||$tmp==='' ? "Recently added" ?? null : $tmp);?>
</a>
                <a href="/changed" class="flex items-center gap-2"><?php echo (($tmp = $_smarty_tpl->tpl_vars['footer_changed']->value ?? null)===null||$tmp==='' ? "Recently changed" ?? null : $tmp);?>
</a>
                <a href="/about" class="flex items-center gap-2"><?php echo (($tmp = $_smarty_tpl->tpl_vars['footer_about']->value ?? null)===null||$tmp==='' ? "About Vici.org" ?? null : $tmp);?>
</a>
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

<?php echo '<script'; ?>
>
    function toggleMobileMenu() {
        const menu = document.getElementById('mobile-menu');
        if (menu.style.display === 'none' || menu.style.display === '') {
            menu.style.display = 'flex';
        } else {
            menu.style.display = 'none';
        }
    }
    document.getElementById('menu-button').addEventListener('click', toggleMobileMenu);
<?php echo '</script'; ?>
>
<?php echo '<script'; ?>
>
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
<?php echo '</script'; ?>
>
<?php echo '<script'; ?>
 src="/js/home.js"><?php echo '</script'; ?>
>
</body>

</html><?php }
}
