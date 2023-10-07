CKEDITOR.plugins.add( 'vicicite',
{
	lang: 'en,de,nl',
	init: function( editor )
	{
	    
	    editor.addCommand( 'citeDialog', new CKEDITOR.dialogCommand( 'citeDialog' ) );
	    
	    editor.ui.addButton( 'ViciCite',
        {
	        label: editor.lang.vicicite.insertReference,
	        command: 'citeDialog',
	        icon: this.path + 'images/vicicite.png'
        } );
        
        
        CKEDITOR.dialog.add( 'citeDialog', function ( editor )
        {
	        return {
		        title : editor.lang.vicicite.insertReference,
		        minWidth : 400,
		        minHeight : 140,
 
		        contents : [
			    {
				    id : 'tab1',
				    label : 'URL',
				    elements : [
				    {
		                type : 'html',
		                html : editor.lang.vicicite.explain
	                },
				    {
				        type : 'text',
				        id : 'ref',
				        label : editor.lang.vicicite.reference,
				        validate : CKEDITOR.dialog.validate.notEmpty( editor.lang.vicicite.referenceRequiered )
			        },  
				    {
				        type : 'text',
				        id : 'url',
				        label : 'URL'
			        }],
		        }],
		        onOk : function()
                {
	                var dialog = this;
	                var abbr = editor.document.createElement( 'cite' );

                    abbr.setAttribute( 'class', 'reference' ); 
	                abbr.setAttribute( 'href', dialog.getValueOf( 'tab1', 'url' ) );
	                abbr.setText(' ['+dialog.getValueOf( 'tab1', 'ref' )+'] ' );

	                editor.insertElement( abbr );
                }
	        };
        } );
        
	}
} );