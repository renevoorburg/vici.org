
CKEDITOR.plugins.addExternal( 'vicicite', '/ckeditor/plugins/vicicite/');
CKEDITOR.plugins.addExternal( 'viciquote', '/ckeditor/plugins/viciquote/');

CKEDITOR.editorConfig = function( config )
{
	config.contentsCss = '/ckeditor/css/ck.css';
	config.extraAllowedContent = 'cite[href](reference); blockquote';
    config.format_tags = 'p;h2;h3';
    config.forcePasteAsPlainText = true;
};
