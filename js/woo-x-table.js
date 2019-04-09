jQuery(document).ready(function($){
    $('.woox-add-to-cart').click(function(e){
        e.preventDefault();
        var data = $(this).closest('form').serialize();
        $.post(ajaxurl,{action:'add_product',data:data},function(data){
           console.log(data); 
            location = data.location;
        });
    });
    
    $('#pa_color').on('change',function(){
       var sel = $(this).val();
        $('.woo_var').hide(0)
        $('#woox_'+sel).show(0)
    })
});