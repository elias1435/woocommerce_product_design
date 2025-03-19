(function($){ 
  'use strict'; 
  var prodesignWindow = window; 

   $(document)
    .ready(
      function(){
        let defaultColor = $( '.prodesign-color-variation-select' ).parent('td').parent('tr');
          defaultColor.css({'display':'none'});
          defaultColor.addClass('prodesign-color-tr');

        $( '.pdn-prodesign-class' )
        .parent('div')
        .parent('div')
        .parent('div')
        .addClass( 'pdn-work-design' );

        const pdnPrintCheckout = $( '#pdn-print-file-wp' );
        if( pdnPrintCheckout.length > 0 ){
          pdnPrintCheckout.parent('p').parent('li').remove();
        }

    })
    .on( 'change', '#pdesign-upload', function(){
      if( prodesignWindow.pdnCheckVariation() ){
           let file = this.files[0];
                if ( file ) {
                  $( '.pdesign-upload-btn' ).addClass( 'is_loading' );
                    let reader = new FileReader();
                    reader.onload = function (event) {     
                        $('.pdesign-file-box img').attr('src', event.target.result );
                        $('.pdesign-file-name').text( file.name );
                        $('.pdesign-upload-sticker').css('display','inline-flex');
                        $('.pdesign-image-can').html('<div class="pdesign-sticker-image"><img src="' + event.target.result + '" alt=""></div>');
                    };

                    reader.readAsDataURL( file );
                    $( '.pdesign-upload-btn' ).removeClass( 'is_loading' );
                    
                }

            prodesignWindow.cartPuthold();
      }
    })
    .on( 'click', '.pdesign-upload-wrap', function(){
      $( '#pdesign-upload' ).trigger( 'click' );
    })
    .on('click', '.pdesign-upload-remove', function(){
      if( prodesignWindow.pdnCheckVariation() ){
        $('.pdesign-upload-sticker').css('display','none');
        $('.pdesign-image-can').html('');
        prodesignWindow.cartPuthold();
      }
    })
    .on( 'keyup', 'input[name="pdesign-input"]', function(){
      if( prodesignWindow.pdnCheckVariation() ){
        let input=$(this).val();
        $('.pdesign-text-can').html('<div class="pdesign-heading">'+input+'</div>');
        prodesignWindow.cartPuthold();
      }
    })
    .on('click', '.pdesign-gallery-list li',function(){
      if( prodesignWindow.pdnCheckVariation() ){
          $( '.pdesign-gallery-list li').each( function(){
            $( this ).removeClass( 'pd-active' );
          });
          $(this).addClass('pd-active');
          let src = $( this ).children( 'img' ).attr('src');
           $('.pdesign-gallery-image').children('img').attr('src',src);
           prodesignWindow.cartPuthold();
      }
    })
    .on('click','select[name="pdesign-select-font"]',function(){
        if( prodesignWindow.pdnCheckVariation() ){
          let val = $(this).val();
          $('.pdn-text-active').css('font-family', val);
          prodesignWindow.cartPuthold();
        }
    })
    .on('input change','input[name="pdesign-select-size"]',function(){
      if( prodesignWindow.pdnCheckVariation() ){
         
           var val = $(this).val();
           $('.pdesign-select-wrap span').text(val + 'px');
           $('.pdn-text-active').css('font-size',val+'px');
           prodesignWindow.cartPuthold();
        }

    })
     .on('input change','input[name="pdesign-color"]',function(){
      if( prodesignWindow.pdnCheckVariation() ){
        let val=$(this).val();
         $('.pdn-text-active').css('color',val);
         prodesignWindow.cartPuthold();
      }
    })
    .on('input change', 'input[name="pdesign-sticker-size"]', function(){
      if( prodesignWindow.pdnCheckVariation() ){
          var val = $(this).val();
          $('.pdesign-sticker-size span').text(val + '%');
          $('#pdesign-image-can').css('width',val+'%');
          prodesignWindow.cartPuthold();
      }
    })
    .on( 'click', '.prod-color-box', function(){
      $('.prodesign-color-flex div').each( function(){
        $( this ).html('');
        $( this ).removeClass( 'pdn-variation-active' );
      });
      $( this ).html( prodesignWindow.colorVariationSvg() );
       $( this ).addClass( 'pdn-variation-active' );
      const colorName = $( this ).data('id'),
            colorAttribute = $( this ).data( 'attribute' );
            $( '#' + colorAttribute ).val( colorName );
            $( '#' + colorAttribute ).trigger('change');
            $( '.pdesign-gallery' ).addClass('display-none');
            $( '.prodesign-varibale' ).each( function(){
              $( this ).removeClass( 'display-block' );
            });
            $( '#prodesign-varibale-' + colorName ).addClass('display-block');
          
            let src = $( '#prodesign-variable-gellery-' + colorName).children('li:first-child').children('img').attr('src');
            $('.pdesign-gallery-image').children('img').attr('src',src);
            $( '#prodesign-variable-gellery-' + colorName).children('li').each( function(){
              $( this ).removeClass( 'pd-active' );
            });
            $( '#prodesign-variable-gellery-' + colorName).children('li:first-child').addClass('pd-active');
            $( '.pdn-cerror' ).remove();

    })
    .on('click', '.prodesign-variable-gellery li',function(){
          $( '.prodesign-variable-gellery li').each( function(){
            $( this ).removeClass( 'pd-active' );
          });
          $( this ).addClass('pd-active');
          let src = $( this ).children( 'img' ).attr('src');
          $('.pdesign-gallery-image').children('img').attr('src',src);
          const setValue = $( this ).data( 'set' );
      
          $( '.pdn-sdn-wrap-flex li' ).each( function(){
            $( this ).removeClass( 'pdn-sdn-active' );
            const dnValue = $( this ).data( 'val' );
            if( setValue === dnValue ){
              $( this ).addClass( 'pdn-sdn-active' );
            }
          });

    })
    .on( 'click', '.pdn-design-delete', function(){
      const mediaId = $( this ).data( 'id' );
      $( '#pdn_input_' + mediaId ).remove();
      $( '#pdn_input_print_' + mediaId ).remove();
      $( '#remove-design-' + mediaId ).remove();
      const colsCount = $( '.pdn-design-cols' );
      if( 0 === colsCount.length ){
        $( '.pdn-design-output' ).css({ 'display': 'none'});
      }
    })
    .on( 'click', '.pdn-sdn-wrap-flex li', function(){
  
        $( '.pdn-sdn-wrap-flex li' ).each( function(){
           $( this ).removeClass('pdn-sdn-active');
        });
        $( this ).addClass( 'pdn-sdn-active' );
        $( '.pdn-select-required' ).remove();

        const pdnDesign = $( this ).data( 'val' );
        const pdnVariation = $( '.pdn-variation-active' ).data( 'id' );
        let src = $( '#pdn-select-' + pdnVariation + '-' + pdnDesign ).children('img').attr('src');
      
            $( '#pdesign-gallery-image' ).children('img').attr( 'src', src );
            $( '#prodesign-variable-gellery-' + pdnVariation +' '+'li').each( function(){
              $( this ).removeClass( 'pd-active' );
            });
            $( '#pdn-select-' + pdnVariation + '-' + pdnDesign ).addClass( 'pd-active' );
    })
    .on('click', '.pdesign-save-btn', function () {
        const varitionAvailable = $( '.prodesign-color-variation-select' );
        const selectDesign      = $( '.pdn-sdn-active' );
        let type = 'design';
        let d = new Date();
        let addHtml = '';
        let x =  Math.floor(Math.random() * 1000000000000);
     
        $(this).addClass('is_loading');
        html2canvas( document.getElementsByClassName('pdesign-gallery-image')[0] ).then(function (canvas) {
           
            let fileURL = canvas.toDataURL('image/png', 1.0);
            let filecanva = fileURL.split(',')[1];
           
            if( 0 !== varitionAvailable.length ){
               if( 1 === selectDesign.length ){
                  $( '.pdn-select-required' ).remove();
                    type = selectDesign.data('val');
               }
            }
            $( '.wp-pd-image-opacity' ).addClass( 'pdn-opacity-active' );
            $( 'input[name="pdn_print_'+ type +'"]').remove(); 
            html2canvas( document.getElementsByClassName('pdesign-gallery-image')[0] ).then(function (canvas2) {
                let fileURL2 = canvas2.toDataURL('image/png', 1.0);
                let filecanva2 = fileURL2.split(',')[1];
                let printPDF = prodesignWindow.pdnPrintPDF();
                printPDF.append('<input type="hidden" id="pdn_input_print_'+x+'" name="pdn_print_'+type+'" value="'+filecanva2+'">');
            });

              $( '.wp-pd-image-opacity' ).removeClass( 'pdn-opacity-active' );
              addHtml += '<div class="pdn-design-cols pdn_type_cols_'+ type +'" id="remove-design-'+x+'">';
              addHtml += '<div class="pdn-design-img">';
              addHtml += '<img src="'+fileURL+'" alt="">';
              addHtml += '</div>';
              addHtml += '<div class="pdn-design-head">';
              addHtml +=  ( 'rsleeve' === type ) ? 'R-sleeve' : ( ( 'lsleeve' === type ) ? 'L-sleeve' : type) ;
              addHtml += '</div>';
              addHtml += '<div class="pdn-design-delete" data-id="'+x+'">';
              addHtml += '<svg  viewBox="0 -0.5 25 25" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.96967 16.4697C6.67678 16.7626 6.67678 17.2374 6.96967 17.5303C7.26256 17.8232 7.73744 17.8232 8.03033 17.5303L6.96967 16.4697ZM13.0303 12.5303C13.3232 12.2374 13.3232 11.7626 13.0303 11.4697C12.7374 11.1768 12.2626 11.1768 11.9697 11.4697L13.0303 12.5303ZM11.9697 11.4697C11.6768 11.7626 11.6768 12.2374 11.9697 12.5303C12.2626 12.8232 12.7374 12.8232 13.0303 12.5303L11.9697 11.4697ZM18.0303 7.53033C18.3232 7.23744 18.3232 6.76256 18.0303 6.46967C17.7374 6.17678 17.2626 6.17678 16.9697 6.46967L18.0303 7.53033ZM13.0303 11.4697C12.7374 11.1768 12.2626 11.1768 11.9697 11.4697C11.6768 11.7626 11.6768 12.2374 11.9697 12.5303L13.0303 11.4697ZM16.9697 17.5303C17.2626 17.8232 17.7374 17.8232 18.0303 17.5303C18.3232 17.2374 18.3232 16.7626 18.0303 16.4697L16.9697 17.5303ZM11.9697 12.5303C12.2626 12.8232 12.7374 12.8232 13.0303 12.5303C13.3232 12.2374 13.3232 11.7626 13.0303 11.4697L11.9697 12.5303ZM8.03033 6.46967C7.73744 6.17678 7.26256 6.17678 6.96967 6.46967C6.67678 6.76256 6.67678 7.23744 6.96967 7.53033L8.03033 6.46967ZM8.03033 17.5303L13.0303 12.5303L11.9697 11.4697L6.96967 16.4697L8.03033 17.5303ZM13.0303 12.5303L18.0303 7.53033L16.9697 6.46967L11.9697 11.4697L13.0303 12.5303ZM11.9697 12.5303L16.9697 17.5303L18.0303 16.4697L13.0303 11.4697L11.9697 12.5303ZM13.0303 11.4697L8.03033 6.46967L6.96967 7.53033L11.9697 12.5303L13.0303 11.4697Z" fill="#000000"/></svg>';
              addHtml += '</div>';
              addHtml += '</div>';
              
              let gcarthld = prodesignWindow.cartGethold();

              $( 'input[name="pdn_'+ type +'"]').remove();
              $( '.pdn_type_cols_' + type ).remove();
              $( '.pdn-design-output' ).css({ 'display': 'block'});
              $( '.pdn-design-wrap' ).append( addHtml );
              
              $( 'input[name="pdesign-input"]').val('');
              $( '.pdesign-upload-sticker').css({'display':'none'});
              $( '#pdesign-image-can' ).html('');
              $( '#pdesign-text-can' ).html('');
              $( '#pdesign-more-on' ).html('');
              $( '.pd-add-m-inputs' ).remove();
              $( '.pdesign-input-text-flex span' ).css( {'display':'block'} );

              if( 0 !== varitionAvailable.length ){
                $( '.pdn-sdn-wrap-flex li' ).each( function(){
                  $( this ).removeClass('pdn-sdn-active');
                });
                
              }
              
              gcarthld.append('<input type="hidden" id="pdn_input_'+x+'" name="pdn_'+type+'" value="'+filecanva+'">');
             
      });

    })
    .on(
      'click',
      '#dsvg-add-more-input',
      function(){
       let mdiv = '<div class="pd-add-m-inputs">';
           mdiv += '<input type="text" name="padd-more-inputs" placeholder="Ag.: יום אב שמח">';
           mdiv += '<span>';
           mdiv += '<svg id="psvg-less-more-inputs" viewBox="0 0 24 24" fill="none"><path d="M6 12L18 12" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
           mdiv += '</span>';
           mdiv += '</div>';
           $( '.pdesign-input-text-flex span' ).css( {'display':'none'} );
           $( '.pdesign-input-text-flex' ).after( mdiv );
      }
    )
    .on(
      'click',
      '#psvg-less-more-inputs',
      function(){
          $( '.pd-add-m-inputs' ).remove();
          $( '#pdesign-more-on' ).html('');
          $( '.pdesign-input-text-flex span' ).css( {'display':'block'} );
          $( '#pdesign-text-can' ).removeClass( 'pdn-text-active' );
          $( '#pdesign-more-on' ).removeClass( 'pdn-text-active' );
          $( '#pdesign-text-can' ).addClass( 'pdn-text-active' );
        
      }
    )
    .on(
      'keyup',
      'input[name="padd-more-inputs"]',
      function(){
        if( prodesignWindow.pdnCheckVariation() ){
            let input = $(this).val();
            $( '#pdesign-text-can' ).removeClass( 'pdn-text-active' );
            $( '#pdesign-more-on' ).removeClass( 'pdn-text-active' );
            $( '#pdesign-more-on' ).html('<div class="pdesign-heading">'+input+'</div>');
            $( '#pdesign-more-on' ).addClass( 'pdn-text-active' );
            prodesignWindow.cartPuthold();
        }
      }
    )
    .on(
        'click, mouseenter, mouseleave',
        '#pdesign-text-can',
        function(){
            $( '#pdesign-text-can' ).removeClass( 'pdn-text-active' );
            $( '#pdesign-more-on' ).removeClass( 'pdn-text-active' );
            $( '#pdesign-text-can' ).addClass( 'pdn-text-active' );
        }
    )
    .on(
        'click, mouseenter, mouseleave',
        '#pdesign-more-on',
        function(){
            $( '#pdesign-text-can' ).removeClass( 'pdn-text-active' );
            $( '#pdesign-more-on' ).removeClass( 'pdn-text-active' );
            $( '#pdesign-more-on' ).addClass( 'pdn-text-active' );
        }
    );


}(jQuery));