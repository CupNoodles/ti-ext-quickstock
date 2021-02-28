$(document).ready(function(){

    $('.quickstock-checkbox').on('change', function(){

        // disable this until we get back confirmation that it worked
        $(this).prop("disabled", true );
        $.ajax({
            url: '/admin/cupnoodles/quickstock/quickstock/save',
            type: 'POST',
            dataType: 'json',
            data: {
              location_id: $(this).data('location'),
              menu_id: $(this).data('menu'),
              action: $(this).attr('name'),
              val: $(this).is(':checked')
            },
            success: function(data) {
                $('#'+(data['action'] == 'status' ? 'status' : 'out_of_stock')+'_'+data['location_id']+'_'+data['menu_id']).prop("disabled", false );
            },
            
        });
    });
});