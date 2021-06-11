$(document).ready(function(){

    // set the tab on load
    var url = document.URL;
    var hash = url.substring(url.indexOf('#')) ;

    $(".nav-tabs").find("li a").each(function(key, val) {

        if (hash == $(val).attr('href')) {
            $(val).click();
        }
        $(val).click(function(ky, vl) {
            location.hash = $(this).attr('href');
        });
    });

    if(hash == url){
        $('#global').click();
    }

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