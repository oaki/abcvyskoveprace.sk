/**
 * Created by nazumi on 6/28/2016.
 */
(function($) {
    "use strict";
    $(document).ready(function() {

        $('#load_more_project').click(function() {
            var element = $(this);
            var posts_per_page = element.attr('data-posts-per-page');
            var paged = element.attr('data-paged');
            var order = element.attr('data-order');
            var order_by = element.attr('data-order-by');
            var tax_query = element.attr('data-tax-query');
            var max_num_page = element.attr('data-max-num-page');
            var $container = $('.st_feature_work');
            $container.mixItUp();
            $.ajax({
                url        : ajax_process.ajaxurl,
                type       : "GET",
                data       : {
                    'action'         : "st_load_more_project",
                    'posts_per_page' : posts_per_page,
                    'paged'          : paged,
                    'order'          : order,
                    'order_by'       : order_by,
                    'tax_query'      : tax_query,
                    'max_num_page'   : max_num_page,
                },
                dataType   : "json",
                beforeSend : function() {
                    element.html('Loading ...');
                },
                complete   : function(msg) {

                    $('<div>' + msg.responseJSON.html + '</div>').find('.work-item').each(function() {
                        $container.mixItUp('append', $(this));
                    });
                    $container.mixItUp();
                    // disable button
                    element.attr('data-paged', msg.responseJSON.paged);
                    if (msg.responseJSON.paged == element.attr('data-max-num-page')) {
                        element.html('No more');
                        element.attr('disabled', "");
                    } else {
                        element.html('Load more');
                    }
                }
            });
        });
        //loadmore default
    });
})(jQuery)