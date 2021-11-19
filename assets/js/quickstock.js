$(document).ready(function(){


    $.fn.datepicker.defaults.format = "mm/dd/yyyy";
    $('.datepicker').datepicker({
        startDate: '+1d'
    });

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



    $('.quickstock-checkbox').on('change', function(){
        ajaxRequest($(this),$('#' + $(this).data('location') + '_' + $(this).data('menu') + '_back_in_stock_date').val() );
    });

    $('a.in_stock_date_action').on('click', function(){
        ajaxRequest($('#out_of_stock_'+ $(this).data('location') + '_' + $(this).data('menu')), $(this).data('datepicker-value'));
    });
    $('input.in_stock_date_action').on('change', function(){
        ajaxRequest($('#out_of_stock_'+ $(this).data('location') + '_' + $(this).data('menu')), $(this).val());
    });

    function ajaxRequest($stock_el, date_val){
        $.ajax({
            url: '/admin/cupnoodles/quickstock/quickstock/save',
            type: 'POST',
            dataType: 'json',
            data: {
              location_id: $stock_el.data('location'),
              menu_id: $stock_el.data('menu'),
              action: $stock_el.attr('name'),
              val: $stock_el.is(':checked'),
              in_stock_date: date_val
            },
            success: function(data) {
                changeSuccess(data);
            }
        });
    }



    function changeSuccess(data){
        if(data['action'] == 'out_of_stock'){
            if(data['val'] == 'true'){
                $('#until_'+data['location_id']+'_'+data['menu_id']+'_container').hide();
            }
            else{
                $('#until_'+data['location_id']+'_'+data['menu_id']+'_container').show();
            }
        }

        $('#date_button_'+ data['location_id'] + '_' + data['menu_id']).text(data['in_stock_date_text']);
        $('.dropdown.show').removeClass('show');
        
    }

});





