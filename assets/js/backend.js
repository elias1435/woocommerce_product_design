(function($){ 
$(document)
.on('click', '.add-slot', function(){
    const parent      = $(this).closest('.custom-uploads'),
        loop          = $(this).data('loop');
    
    var lastImageBox  = $(parent).find('.image-box:last'),
        hiddenInput   = $('[name=slot-index-'+loop+']'),
        index         = parseInt(hiddenInput.val()),
        inputNameUpd  = 'additional_img_ids-'+loop+'-'+index,
        inputPropsUpd = {'name': inputNameUpd, 'value': null},
        placeholder   = $('[name=ph-img-'+loop+']').val(),
        clonedImgBox  = lastImageBox.clone().insertBefore('.buttons-box'); // Insert a clone

    clonedImgBox.find('.upload_image_id').prop(inputPropsUpd);
    clonedImgBox.find('img').prop('src', placeholder);
    clonedImgBox.find('a').removeClass('remove');
    hiddenInput.val(index+1);

    if ( 1 === index ) {
        $(this).parent().find('.remove-slot').show();
    }
}).on('click', '.remove-slot', function(){
    const parent     = $(this).closest('.custom-uploads'),
        loop         = $(this).data('loop');
        
    var lastImageBox = $(parent).find('.image-box:last'),
        hiddenInput  = $('[name=slot-index-'+loop+']'),
        index        = parseInt(hiddenInput.val());

    lastImageBox.remove();
    hiddenInput.val(index-1);
    
    if ( 2 === index ) {
        $(this).hide();
    }
});
}(jQuery));
