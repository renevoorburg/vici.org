{extends file="vici.tpl"}
{block name=main}
            {$errormsg}
            <div>
                <form id="mainform" action="{$smarty.server.REQUEST_URI}" method="post">
                    <div class="editcol" style="position:relative;float:right;border:0px solid #AAA;width:280px">
                        <div id="editcanvas" style="width:280px;height:240px;background-color:#5f9cc5"></div>

                        <label for="coords_frm">{$locationlabel}</label>
                        <input type="text" id="coords_frm" name="coords_frm" value="{$coords}" />
                        <select id="accuracyselect" name="accuracyselect">
                            {$accuracyselectoptions}
                        </select><br>

                        <label for="short_edit">{$summarylabel}</label>
                        <textarea id="short_edit" name="short_edit" placeholder="summary (required)" rows="3">{$summary}</textarea><br>

                        <label for="kindselect">{$categorylabel}</label>
                        <select id="kindselect" name="kindselect">
                            {$kindselectoptions}
                        </select>

                        <div id="periodbox" style="margin-top:8px;">

                            <div style="color:#37607D; margin-bottom:10px;margin-top:10px;padding:0;border:1px;border-style:dotted">
                                <table>
                                    <caption>examples of allowed date notations:</caption>
                                    <tr>
                                        <td>-123</td>
                                        <td>the year 123 BCE</td>
                                    </tr>
                                    <tr>
                                        <td>post 70</td>
                                        <td>after the year 70 CE</td>
                                    </tr>
                                    <tr>
                                        <td>ante 405</td>
                                        <td>before the year 405 CE</td>
                                    </tr>
                                    <tr>
                                        <td>3xx</td>
                                        <td>the fourth century CE</td>
                                    </tr>
                                    <tr>
                                        <td>70~</td>
                                        <td>approximately the year 70 CE</td>
                                    </tr>
                                    <tr>
                                        <td>405?</td>
                                        <td>the year 405 CE, uncertain</td>
                                    </tr>
                                    <tr>
                                        <td>47?~</td>
                                        <td>approximately the year 47 CE, uncertain</td>
                                    </tr>
                                    <tr>
                                        <td>unknown</td>
                                        <td>unknown, for end dates only</td>
                                    </tr>
                                    <tr>
                                        <td>open</td>
                                        <td>no end date, has originial function</td>
                                    </tr>
                                </table>
                            </div>

                            <label for="start_yr">{if ! isset($startlabel)}Year (of creation){else}{$startlabel}{/if}:</label>
                            <input type="text" id="start_yr" name="start_yr" pattern="{literal}(post |ante )?-?[0-9]{0,5}[x]{0,3}\??~?{/literal}" value="{$start}" />
                            {*<input type="checkbox" name="start_uncertain" value="start_uncertain"> Uncertain<br>*}
                            {*<input type="checkbox" name="start_approx" value="start_approx"> Approximately<br>*}

                            <label for="end_yr">{if ! isset($endlabel)}Year of destruction / abandonment{else}{$endlabel}{/if}:</label>
                            <input type="text" id="end_yr" name="end_yr" pattern="{literal}(post |ante )?(-?[0-9]{1,5}[x]{0,3}\??~?|open|unknown){/literal}" value="{$end}" />
                        </div>


                        <label for="visibilityselect">{$visibilitylabel}</label>
                        <select id="visibilityselect" name="visibilityselect">
                            <option {if $visibility == '0'}selected="selected"{/if} value="0">{$invisible}</option>
                            <option {if $visibility == '1'}selected="selected"{/if} value="1">{$visible}</option>
                        </select><br>


                        <label for="extids">{$tagslabel}</label>
                        <div id="extids">
                            {$extids}
                        </div>

                    </div>
                    <input name="editmode" type="hidden" value="{$editmode}" />
                    <input name="id" type="hidden" value="{$pntid}" />
                    <input id="kop_edit" name="kop_edit" placeholder="title (required)" type="text" value="{$name}" size="60"><br>

                    <div style="margin-right:300px">
                        <textarea name="edit_full" id="edit_full">{$annotation}</textarea>
                    </div>


                    <input type="submit" {if $disabled == true}disabled {/if}value="{$submit}">

                </form>
            </div>
            <footer>
                <a rel="license" href="http://creativecommons.org/licenses/by-sa/3.0/deed.{$lang}"><img style="float:right;margin-top:6px;margin-left:8px" src="/images/by-sa_20.png" /></a>
                {$footer}
                {include file="partners.tpl"}
            </footer>
{/block}