{extends file="base.tpl"}

{block name=main}
    <main>
        <h1>{$add_title_label|default:"Add new site"}</h1>
        <p>{$add_description_label|default:"Use this form to add a new archaeological site to the database."}</p>
        
        <div style="background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 0.375rem; padding: 1rem; margin: 1rem 0;">
            <h3>{$coming_soon_label|default:"Coming soon"}</h3>
            <p>{$add_functionality_label|default:"The add functionality is currently under development."}</p>
        </div>
    </main>
{/block}
