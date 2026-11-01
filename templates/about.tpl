{extends file="base.tpl"}

{block name=main}
    <div class="hr"></div>
    <main class="margin">
        <h1>{$about_title|default:"About Vici.org"}</h1>
        {$about_content nofilter}

        <h2>{$support_title|default:"Support Vici.org"}</h2>
        <p>{$support_text|default:"Vici.org is free to use; it is run, maintained and paid for by one person. If you appreciate what I do, you can support me with a donation via PayPal."}</p>
        <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
            <input type="hidden" name="cmd" value="_s-xclick" />
            <input type="hidden" name="hosted_button_id" value="BBSUCN9SDNBNJ" />
            <input type="image" src="/images/btn_donate_LG.gif" name="submit" title="PayPal" alt="{$donate_alt|default:"Donate with PayPal"}" />
        </form>

        <p>
            <span style="font-style:italic">{$about_signature|default:"René Voorburg, May 27th, 2025."}</span><br />
            rene@digitopia.nl
        </p>
    </main>
{/block}
