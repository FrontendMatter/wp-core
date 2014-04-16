(function ($) {
    "use strict";

    $(function () {

        $('#' + taxonomy_radio.taxonomy + 'checklist li :radio, #' + taxonomy_radio.taxonomy + 'checklist-pop :radio').on( 'click', function()
        {
            var t = $(this),
                c = t.is(':checked'),
                id = t.val();

            $('#' + taxonomy_radio.taxonomy + 'checklist li :radio, #' + taxonomy_radio.taxonomy + 'checklist-pop :radio').prop('checked',false);
            $('#in-' + taxonomy_radio.taxonomy + '-' + id + ', #in-popular-' + taxonomy_radio.taxonomy + '-' + id).prop( 'checked', c).trigger('change');
        });

    });

})(jQuery);
