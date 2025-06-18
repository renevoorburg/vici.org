{extends file="base.tpl"}
{block name=main}

    <div>
        {if $message}
        <div>{$message}</div>
        {/if}

        <form action="" method="post">
            <label>{$form_accountname|default:"Account name"}:</label><input type="text" id="frm_accountname" name="accountname"  value="{$form_accountname_previous}" /><br>
            <label>{$form_password|default:"Password"}:</label><input type="password" id="frm_password" name="password" /><br>
            {$captcha}<br />
            <input type="submit" value="{$form_login|default:Login}">
        </form>
    </div>

{/block}