(function ($) {
    "use strict";

    $(function () {

        $('[data-toggle="add-to-post"]').on('click', function(e)
        {
            e.preventDefault();
            var id = $(this).data('relatedId'),
                type = $(this).data('relatedType'),
                instance = $(this).data('relatedInstance');

            parent.crud_instances[instance].addPostRelated({ id: id, type: type });
            parent.tb_remove();
        });

        $('[data-toggle="remove-related"]').on('click', function(e)
        {
            e.preventDefault();
            var id = $(this).data('relatedId'),
                type = $(this).data('relatedType'),
                instance = $(this).data('relatedInstance');

            parent.crud_instances[instance].removeRelated(id, type);
            parent.tb_remove();
        });

    });

})(jQuery);
