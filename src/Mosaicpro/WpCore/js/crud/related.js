(function ($) {
    "use strict";

    window.crud_instances = {};
    window.mp_crud_related = function(instance)
    {
        this.instance = instance;
        this.list = '#' + instance.prefix + '_' + this.getRelatedId() + '_list';
    };

    window.mp_crud_related.prototype.getRelatedId = function()
    {
        return $.isArray(this.instance.related) ? this.instance.related.join('_') : this.instance.related;
    }

    window.mp_crud_related.prototype.addPostRelated = function(related)
    {
        var related_data = this.instance,
            that = this;

        var data = {
            action: related_data.prefix + '_add_' + related_data.post + '_' + related.type,
            nonce: related_data.nonce,
            post_id: related_data.post_id,
            related_id: related.id
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

    window.mp_crud_related.prototype.removeRelated = function(id, type)
    {
        var that = this,
            related_data = this.instance,
            data = {
                action: related_data.prefix + '_delete_' + type,
                related_id: id
            };

        $.post( ajaxurl, data )
            .success(function(response)
            {
                if (response == -1)
                    return alert('An error occurred while saving the data');

                if (typeof response.success !== 'undefined')
                {
                    if (response.success !== true)
                        return alert(response.data);

                    that.wp_remove_post_related(id, type);
                }
            });
    }

    window.mp_crud_related.prototype.wp_remove_post_related = function(related_id, type)
    {
        var related_data = this.instance,
            that = this;

        var data = {
            action: related_data.prefix + '_remove_' + related_data.post + '_' + type,
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
                {
                    if (response.success !== true)
                        return alert(response.data);

                    that.listPostRelated();
                }
            });
    }

    window.mp_crud_related.prototype.listPostRelated = function()
    {
        var that = this;
        var related_data = this.instance;
        var data = {
            action: related_data.instance_ID + '_list_related',
            nonce: related_data.nonce,
            post_id: related_data.post_id,
            related_types: related_data.related_types
        };

        $.post( ajaxurl, data )
            .success(function(response)
            {
                if (response == -1)
                    return alert('An error occurred while fetching the data');

                if (typeof response.success !== 'undefined')
                {
                    if (response.success !== true)
                        return alert(response.data);

                    $(that.list).html(typeof response.data !== 'undefined' ? response.data : '');
                }
            });
    };

    $(function()
    {
        var found_crud_instances = $.map(window, function(e,i){ if (i.match(/crud_id_/)) return i; });
        $.each(found_crud_instances, function(k,instance){
            window.crud_instances[instance] = new window.mp_crud_related(window[instance]);
            window.crud_instances[instance].listPostRelated();
        });

        $('body').on('click', '[data-toggle="remove-from-post"]', function(e)
        {
            e.preventDefault();
            var id = $(this).data('relatedId'),
                type = $(this).data('relatedType'),
                instance = $(this).data('relatedInstance');

            window.crud_instances[instance].wp_remove_post_related(id, type);
        });
    });

})(jQuery);