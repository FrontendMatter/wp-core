(function ($) {
    "use strict";

    $(function ()
    {

        if (typeof post_status_quick == 'undefined')
            return;

        var post_status = post_status_quick;

        $( '.editinline' ).on( 'click', function()
        {
            var target = $('.inline-edit-status select');
            $( target ).append( post_status.options );
        } );

    });

})(jQuery);
