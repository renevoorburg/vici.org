{* Smarty include for displaying location accuracy label, with translation and sensible defaults *}
{* Usage: {include file="include/location_accuracy.tpl" accuracy=$site->representativeLocation->qualifier} *}

{if accuracy == 0}
    {$accuracy_label_0|default:"Location: correct and verified"}
{elseif accuracy == 1}
    {$accuracy_label_1|default:"Location: with minor uncertainty"}
{elseif accuracy == 2}
    {$accuracy_label_2|default:"Location: in range of ± 5-25 m."}
{elseif accuracy == 3}
    {$accuracy_label_3|default:"Location: in range of ± 25-100 m."}
{elseif accuracy == 4}
    {$accuracy_label_4|default:"Location: in range of ± 100-500 m."}
{elseif accuracy == 5}
    {$accuracy_label_5|default:"Location: uncertain"}
{/if}

    