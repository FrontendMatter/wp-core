(function ($) {
    "use strict";

    window.crud_instances = {};
    window.mp_crud_related = function(instance)
    {
        this.instance = instance;
        this.list = '#' + instance.prefix + '_' + instance.related + '_list';
    };

    window.mp_crud_related.prototype.addPostRelated = function(related)
    {
        var related_data = this.instance,
            that = this;

        var data = {
            action: related_data.prefix + '_add_' + related_data.post + '_' + related_data.related,
            nonce: related_data.nonce,
            post_id: related_data.post_id,
            related: related
        };

        $.post( ajaxurl, data )
            .success(function(response)
            {
                if (response == -1)
                    return alert('An error occurred while saving the data');

                if (typeof response.success !== 'undefined')
                    that.listPostRelated();
            });
    }

    window.mp_crud_related.prototype.wp_remove_post_related = function(related_id)
    {
        var related_data = this.instance,
            that = this;

        var data = {
            action: related_data.prefix + '_remove_' + related_data.post + '_' + related_data.related,
            nonce: related_data.nonce,
            post_id: related_data.post_id,
            related_id: related_id
        };

        $.post( ajaxurl, data )
            .success(function(response)
            {
                if (response == -1)
                    return alert('An error occurred while removing the data');

                if (typeof response.success !== 'undefined')
                    that.listPostRelated();
            });
    }

    window.mp_crud_related.prototype.listPostRelated = function()
    {
        var that = this;
        var related_data = this.instance;
        var data = {
            action: related_data.prefix + '_list_' + related_data.post + '_' + related_data.related,
            nonce: related_data.nonce,
            post_id: related_data.post_id
        };

        $.post( ajaxurl, data )
            .success(function(response)
            {
                if (response == -1)
                    return alert('An error occurred while fetching the data');

                if (typeof response.success !== 'undefined')
                    $(that.list).html(response.data);
            });
    };

    $(function()
    {
        var found_crud_instances = $.map(window, function(e,i){ if (i.match(/crud_related_instance/)) return i; });
        $.each(found_crud_instances, function(k,instance){
            window.crud_instances[instance] = new window.mp_crud_related(window[instance]);
            window.crud_instances[instance].listPostRelated();
        });

        $('body').on('click', '[data-toggle="remove-from-post"]', function(e)
        {
            e.preventDefault();
            var id = $(this).data('relatedId'),
                instance = $(this).data('relatedInstance');

            window.crud_instances[instance].wp_remove_post_related(id);
        });
    });

})(jQuery);