(function ($) {
    "use strict";

    $(function () {

        var options = $(utility_show_hide.when + utility_show_hide.selector_changes);
        options.on('change', function()
        {
            show(this);
        });

        var checked = $(utility_show_hide.when + ' :checked');
        if (checked.length)
            show(checked);

        function show(that)
        {
            var data = $(that).attr(utility_show_hide.attribute);
            if (data == utility_show_hide.is_value)
                $(utility_show_hide.show_target).show();
            else
                $(utility_show_hide.show_target).hide();
        }

    });

})(jQuery);
