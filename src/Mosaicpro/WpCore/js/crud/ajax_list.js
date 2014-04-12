(function ($) {
    "use strict";

    $(function () {

        $('[data-toggle="add-to-post"]').on('click', function(e)
        {
            e.preventDefault();
            var id = $(this).data('relatedId'),
                title = $(this).data('relatedTitle'),
                instance = $(this).data('relatedInstance');

            parent.crud_instances[instance].addPostRelated({ id: id, title: title });
            parent.tb_remove();
        });
    });

})(jQuery);
