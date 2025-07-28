{extends file="base.tpl"}

{block name=stylesheets}
    <style>
        main.image {
            margin: 1.5rem;
        }
        .image-meta {
          border-collapse: collapse;
          border-spacing: 0 0.25em;
          width: 100%;
        }
        .image-meta td {
          padding: 0.2em 0.6em 0.2em 0.2em;
          vertical-align: top;
        }
        .image-meta td:first-child {
          color: #555;
          background: #f7f7f7;
          border-radius: 0.3em 0 0 0.3em;
          font-weight: 500;
          min-width: 90px;
        }
        .image-meta td:last-child {
          color: #222;
          background: #fff;
          border-radius: 0 0.3em 0.3em 0;
        }

        .image-meta a {
            color: #64748b;
        }

        .image-meta a:hover {
            text-decoration: underline;
        }

    </style>
{/block}


{block name=main}
    <main class="image">

    <h1>{$image->title}</h1>
    {$image->description}
    <br>
    <table class="image-meta">
        <tr>
            <td>Creator</td>
            <td>{$image->getCreator()}</td>
        </tr>
        <tr>
            <td>Source</td>
            <td>{if $image->source}<a href="{$image->source}">{$image->source}</a>{/if}</td>
        </tr>
        <tr>
            <td>License</td>
            <td>
            {if $image->license->uri}
            <a href="{$image->license->uri}">{$image->license->name}</a>
            {else}
            {$image->license->name}
            {/if}
            </td>
        </tr>
        <tr>
            <td>Date added</td>
            <td>{$image->dateAdded}</td>
        </tr>
    </table>

    {if $image->sites->count() > 0}
        <div class="sitelist">
            <h2>{$used_in_label|default:"Used in"}</h2>
            <ul>
                {foreach $image->sites as $site}
                    <li>
                        {include file="include/site_list_item.tpl"}
                    </li>
                {/foreach}
            </ul>
        </div>
    {/if}
    </main>
{/block}