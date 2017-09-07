/**
 * @global eraser_props
 */

jQuery(document).ready(function($) {
    var ajaxdata = { nonce : eraser_props.nonce };

    function erase_data(action){
        ajaxdata.action = action;

        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: ajaxdata,
            success: function(response){
                var response = JSON.parse(response);

                console.log(response);
                if( response.result == 1 ) {
                    erase_data(ajaxdata);
                }
                else if( response.result == 2 ) {
                    console.log('Done!');
                }
                else {
                    alert(response.message);
                }
            }
        }).fail(function() {
            alert('Случилась непредвиденая ошибка, попробуйте повторить позже');
        });
    }

    $('#erase-terms').on('click', function(event) {
        event.preventDefault();

        erase_data('erase_terms');
    });

    function select_tax( ajax_post_data ) {
        return ajax_post_data;
    }

    $('#erase_taxanomy').on('change', function(event) {
        select_tax($(this).val());
    });
});
