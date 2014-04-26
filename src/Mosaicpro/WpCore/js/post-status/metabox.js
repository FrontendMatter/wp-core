(function ($) {
    "use strict";

    $(function ()
    {

        if (typeof post_status_metabox == 'undefined')
            return;

        var post_status = post_status_metabox,
            appended = false;

        if (post_status.display != '')
            $( '#post-status-display' ).html( post_status.display );

        $( '.edit-post-status' ).on( 'click', function()
        {
            if (appended) return;
            var select = $( '#post-status-select' ).find( 'select' );
            $( select ).append( post_status.options );
            appended = true;
        } );

    });

})(jQuery);
