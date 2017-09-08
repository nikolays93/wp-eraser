/**
 * @global eraser_props
 */

jQuery(document).ready(function($) {
    var ajax = { type: 'POST', url: ajaxurl }

    function call_ajax() {
        ajax.data.nonce = eraser_props.nonce;

        $.ajax( ajax ).fail(function() {
            alert( 'Случилась непредвиденая ошибка, попробуйте повторить позже' );
        });
    }

    function erase(){
        ajax.success = function(response) {
            var response = JSON.parse(response);

            if( response.result == 1 ) {
                $counter.val( $counter.val() - response.count );

                erase(ajaxdata);
            }
            else if( response.result == 2 ) {
                $counter.val( $counter.val() - response.count );

                console.log('Done!', $counter.val(), response.count);
                $counter.closest('.inside').find('.spinner').removeClass('is-active');
            }
            else {
                alert( response.message );
            }
        }

        call_ajax();
    }

    $('#erase-terms').on('click', function(event) {
        event.preventDefault();
        ajax.data = {
            action : 'erase_terms',
            tax    : $('#erase_taxanomy').val(),
        }
        var $counter = $('#terms_count');
        $(this).closest('.inside').find('.spinner').addClass('is-active');

        erase();
    });

    function select_type( post_type ) {
        ajax.data = {
            action : 'update_existing_posts',
            post_type : post_type
        }

        ajax.success = function(response) {
            var response = JSON.parse(response);

            if( response.result == 1 ) {
                $('#existing_posts_filter').html( response.message );
                $('#posts_count').html( response.count );

                $('#existing_posts_filter').closest('.inside').find('.spinner').removeClass('is-active');
            }
            else {
                alert( response.message );
            }
        }

        call_ajax();
    }

    $('#erase_post_type').on('change', function(){
        select_type( $(this).val() );
        $(this).closest('.inside').find('.spinner').addClass('is-active');
    });

    function select_tax( taxanomy ) {
        ajax.data = {
            action : 'update_existing_terms',
            taxanomy : taxanomy
        }

        ajax.success = function(response) {
            var response = JSON.parse(response);

            if( response.result == 1 ) {
                $('#existing_terms_filter').html( response.message );
                $('#terms_count').html( response.count );

                $('#existing_terms_filter').closest('.inside').find('.spinner').removeClass('is-active');
            }
            else {
                alert( response.message );
            }
        }

        call_ajax();
    }

    $('#erase_taxanomy').on('change', function(event) {
        select_tax($(this).val());
        $(this).closest('.inside').find('.spinner').addClass('is-active');
    });
});
