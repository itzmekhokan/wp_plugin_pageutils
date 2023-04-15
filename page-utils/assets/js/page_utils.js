( function( $ ) {
    // Admin plugin settings
    /***** Hreflang settings ******/
    $( 'select[name^="hreflang_tags_settings_options[supported_lang]"]' ).each( function() {
      var $lang_locale = $(this).val();
      var lang_code = $('option:selected', this).attr('lang');
      var $close_lang_code = $(this).closest('.supported-base-langs-field_wrapper').find('.pu-supported-lang-code');
      $close_lang_code.attr('name', $close_lang_code.attr('name')+'['+$lang_locale+']');
      $close_lang_code.attr('value', lang_code);
    } );
    /***** GDPR settings ******/
    $('.pu-color-picker').wpColorPicker();
    $( 'input#gdpr_cookie_has_sticky' ).change( function() {
      if ( $( this ).is(':checked') ) {
        $( this ).closest('tbody').find( '#gdpr_cookie_sticky_heading' ).closest( 'tr' ).show();
      } else {
        $( this ).closest('tbody').find( '#gdpr_cookie_sticky_heading' ).closest( 'tr' ).hide();
      }
    }).change();

    $( 'select#accept_button_action' ).change( function() {
      if ( $( this ).val() == 'open_url' ) {
        $( this ).closest('tbody').find( '.accept-btn-link' ).closest( 'tr' ).show();
      } else {
        $( this ).closest('tbody').find( '.accept-btn-link' ).closest( 'tr' ).hide();
      }
    }).change();

	  // hide toplevel menu item
	  $('#toplevel_page_page-utils li.wp-first-item').addClass('current');

	  // Toggle Collapse
  	$('.faq li .question').on('click', function () { 
  		  $(this).find('.plus-minus-toggle').toggleClass('collapsed');
  		  $(this).parent().toggleClass('active');
  	});

  	// Copy to clipboard
  	$('.copy-faq-shortcode').click(function() {
  		  var $temp = $("<input>");
    		$("body").append($temp);
    		$temp.val($(this).text()).select();
    		document.execCommand("copy");
    		$('.copied-faq-shortcode').css({'padding-left': '10px', 'color': '#403e3e'}); 
    		$('.copied-faq-shortcode').text($(this).attr('data-text_copied')).show().fadeOut(2500);
    		$temp.remove();
  	});

    //add more fields group
    $('.add_more_langs_support').on( 'click', function(){
        var fieldHTML = '<p class="supported-base-langs-field_wrapper">'+$('.supported-base-langs-field_wrapperCopy').html()+'</p>';
        $('body').find('.supported-base-langs-field_wrapper:last').after(fieldHTML);
    });
      
    //remove fields group
    $("body").on("click",".remove_langs_support",function(){ 
        $(this).parents(".supported-base-langs-field_wrapper").remove();
    });

} )( jQuery );