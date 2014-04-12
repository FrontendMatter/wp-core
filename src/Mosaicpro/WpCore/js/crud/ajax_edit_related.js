(function ($) {
    "use strict";

    $(function () {

        $('.edit-related-form').on('submit', function(e)
        {
            e.preventDefault();

            var instance = $(this).data('relatedInstance');
            var instance_data = parent.crud_instances[instance].instance;
            var data = {
                action: instance_data.prefix + '_edit_' + instance_data.related,
                nonce: related_data.nonce,
                related_id: related_data.related_id
            };

            $.post(ajaxurl, $.param(data) + '&' + $(this).serialize())
                .success(function(response)
                {
                    if (response == -1)
                        return alert('An error occurred while saving the data');

                    if (typeof response.success !== 'undefined')
                    {
                        parent.crud_instances[instance].listPostRelated();
                        parent.tb_remove();
                    }
                });
        });
    });

})(jQuery);
