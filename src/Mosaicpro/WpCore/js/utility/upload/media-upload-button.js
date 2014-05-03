( function( $ )
{

    $( function()
    {
        $.fn.mp_media_upload_button = function( options )
        {
            var selector = $( this );

            // Set default options
            var defaults = {
                'preview' : '.preview-upload',
                'text'    : '.text-upload',
                'button'  : '.button-upload'
            };
            var options  = $.extend( defaults, options );

            // When the Button is clicked
            $( options.button ).click( function()
            {
                // Get the Text element.
                var text = selector.find( options.text),

                    // The ThickBox label
                    tb_caption = $(this).text() ? $(this).text() : 'Upload';

                // Show WP Media Uploader popup
                tb_show( tb_caption, 'media-upload.php?TB_iframe=true', false );

                // Re-define the global function 'send_to_editor'
                // Define where the new value will be sent to
                window.send_to_editor = function( html )
                {
                    // Get the URL of new image
                    var src = $( 'img', html ).attr( 'src' );

                    // Send this value to the Text field.
                    text.attr( 'value', src ).trigger( 'change' );

                    // Then close the popup window
                    tb_remove();
                }
                return false;
            } );

            $( options.text ).bind( 'change', function()
            {
                // Get the value of current object
                var url = this.value;

                // Determine the Preview field
                var preview = selector.find( options.preview );

                // Bind the value to Preview field
                $( preview ).attr( 'src', url );
            } );
        }

        // Usage
        $( '.mp-media-upload-button' ).mp_media_upload_button();
    } );

} ( jQuery ) );