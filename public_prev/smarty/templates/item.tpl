{extends file="vici.tpl"}
{block name=main}
            <div style="position:relative;width:100%;height:335px">
                <div id="canvas" style="position:absolute;left:0;right:560px;top:4px;height:100%;padding:0;background-color: #53D9F0"></div>
                {*<div style="position:absolute;right:290px;top:0;width:260px;bottom:0;padding:5px;overflow:auto;color:#464646">*}
                    {*{$thisplace}*}
                {*</div>*}
                {*<div style="position:absolute;top:0;width:280px;right:0;bottom:0;padding:5px;overflow:auto;color:#464646;background-color: #ffffff">*}
                    {*{$nearby}*}
                {*</div>*}
                <div style="position:relative;float:right;width:550px;padding:4px;margin-top:-1px;">
                    {$itemimages}
                </div>
            </div>

            <div style="margin-right:560px">

                <div style="position:relative">
                    {*<h2 style="margin-top:14px">Metadata</h2>*}
                    <div style="line-height:1.25em; margin-top:8px;">{$metadata}</div>

                    <h2 style="margin-top:4px">{$annotatie}</h2>

                    <nav id="langSelBox">
                        <ul id="langSelLst">
                            {$langsellist}
                        </ul>
                    </nav>
                </div>
                <div>

                </div>
                {$content}
            </div>
            <footer>
                <a rel="license" href="http://creativecommons.org/licenses/by-sa/3.0/deed.{$lang}"><img style="float:right;margin-top:6px;margin-left:8px" src="/images/by-sa_20.png" /></a>
                {$footer}
                {include file="partners.tpl"}
            </footer>

    <!-- Root element of PhotoSwipe. Must have class pswp. -->
    <div class="pswp" tabindex="-1" role="dialog" aria-hidden="true">

        <!-- Background of PhotoSwipe.
             It's a separate element as animating opacity is faster than rgba(). -->
        <div class="pswp__bg"></div>

        <!-- Slides wrapper with overflow:hidden. -->
        <div class="pswp__scroll-wrap">

            <!-- Container that holds slides.
                PhotoSwipe keeps only 3 of them in the DOM to save memory.
                Don't modify these 3 pswp__item elements, data is added later on. -->
            <div class="pswp__container">
                <div class="pswp__item"></div>
                <div class="pswp__item"></div>
                <div class="pswp__item"></div>
            </div>

            <!-- Default (PhotoSwipeUI_Default) interface on top of sliding area. Can be changed. -->
            <div class="pswp__ui pswp__ui--hidden">

                <div class="pswp__top-bar">

                    <!--  Controls are self-explanatory. Order can be changed. -->

                    <div class="pswp__counter"></div>

                    <button class="pswp__button pswp__button--close" title="Close (Esc)"></button>

                    <button class="pswp__button pswp__button--share" title="Share"></button>

                    <button class="pswp__button pswp__button--fs" title="Toggle fullscreen"></button>

                    <button class="pswp__button pswp__button--zoom" title="Zoom in/out"></button>

                    <!-- Preloader demo http://codepen.io/dimsemenov/pen/yyBWoR -->
                    <!-- element will get class pswp__preloader--active when preloader is running -->
                    <div class="pswp__preloader">
                        <div class="pswp__preloader__icn">
                            <div class="pswp__preloader__cut">
                                <div class="pswp__preloader__donut"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">
                    <div class="pswp__share-tooltip"></div>
                </div>

                <button class="pswp__button pswp__button--arrow--left" title="Previous (arrow left)">
                </button>

                <button class="pswp__button pswp__button--arrow--right" title="Next (arrow right)">
                </button>

                <div class="pswp__caption">
                    <div class="pswp__caption__center"></div>
                </div>

            </div>

        </div>

    </div>

{/block}