(function ($) {
    "use strict";

    $(function () {

        $('[data-toggle="add-to-post"]').on('click', function(e)
        {
            e.preventDefault();
            var id = $(this).data('relatedId'),
                title = $(this).data('relatedTitle'),
                type = $(this).data('relatedType'),
                instance = $(this).data('relatedInstance');

            parent.crud_instances[instance].addPostRelated({ id: id, title: title, type: type });
            parent.tb_remove();
        });
    });

})(jQuery);
