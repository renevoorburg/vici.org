{extends file="vici.tpl"}
{block name=main}
            {$errormsg}
            <article>{$content}</article>           
            <footer>
                <a rel="license" href="http://creativecommons.org/licenses/by-sa/3.0/deed.{$lang}"><img style="float:right;margin-top:6px;margin-left:8px" src="/images/by-sa_20.png" /></a>
                {$footer}
                {include file="partners.tpl"}
            </footer>
{/block}