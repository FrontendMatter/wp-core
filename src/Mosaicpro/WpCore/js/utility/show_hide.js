(function ($) {
    "use strict";

    window.show_hide_utility = {};

    window.utility_show_hide = function(instance)
    {
        this.instance = instance;
    };

    window.utility_show_hide.prototype.init = function()
    {
        var options = $(this.instance.when + this.instance.selector_changes),
            that = this;

        options.on('change', function()
        {
            that.show(this);
        });

        var checked = $(this.instance.when + ' :checked');
        if (checked.length)
            this.show(checked);
        else
            this.show();
    }

    window.utility_show_hide.prototype.show = function(that)
    {
        if (typeof that == 'undefined')
            return $(this.instance.show_target).hide();

        var data = $(that).attr(this.instance.attribute);
        if (data == this.instance.is_value)
            $(this.instance.show_target).show();
        else
            $(this.instance.show_target).hide();
    }

    $(function () {

        var utility_instances = $.map(window, function(e,i){ if (i.match(/utility_show_hide_instance/)) return i; });
        $.each(utility_instances, function(k,instance){
            window.show_hide_utility[instance] = new window.utility_show_hide(window[instance]);
            window.show_hide_utility[instance].init();
        });
    });

})(jQuery);
