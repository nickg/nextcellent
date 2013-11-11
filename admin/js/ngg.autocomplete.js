/*
 * Implementation of jQuery UI Autocomplete
 * see http://jqueryui.com/demos/autocomplete/
 * Version:  1.0.1
 * Author : Alex Rabe
 * 
 */ 
jQuery.fn.nggAutocomplete = function ( args ) { 
    
    var defaults = { type: 'image',
                     domain: '',
                     limit: 50 };
    
    var s = jQuery.extend( {}, defaults, args);
    
    var settings = { method: 'autocomplete',
                    type: s.type,
                    format: 'json',
                    callback: 'json',
                    limit: s.limit };
                     
    var obj = this.selector;
    var id  = jQuery(this).attr('id');
    var cache = {}, lastXhr;
    
    // get current value of drop down field
    var c_text = jQuery(obj + ' :selected').text();
    var c_val  = jQuery(obj).val();
    // IE7 / IE 8 didnt get often the correct width
    if (s.width == undefined)  
        var c_width = jQuery(this).width();
    else
        var c_width = s.width;
    //hide first the drop down field
    jQuery(obj).hide();
    jQuery(obj).after('<input name="' + id + '_ac" type="text" id="' + id + '_ac"/>');
    // Fill up current value & style
    jQuery(obj + "_ac").val(c_text);
    jQuery(obj + "_ac").css('width', c_width);
    // Add the dropdown icon
    jQuery(obj + "_ac").addClass('ui-autocomplete-start')
    jQuery(obj + "_ac").autocomplete({
		source: function( request, response ) {
			var term = request.term;
			if ( term in cache ) {
				response( cache[ term ] );
				return;
			}
            // adding more $_GET parameter
            request = jQuery.extend( {}, settings, request);
			lastXhr = jQuery.getJSON( s.domain, request, function( data, status, xhr ) {
				// add term to cache
                cache[ term ] = data;
				if ( xhr === lastXhr )
					response( data );
			});
        },
        minLength: 0,
        select: function( event, ui ) {
            // adding this to the dropdown list
            jQuery(obj).append( new Option(ui.item.label, ui.item.id) );
            // now select it
            jQuery(obj).val(ui.item.id);
            jQuery(obj + "_ac").removeClass('ui-autocomplete-start');
	   }
	});

   	jQuery(obj + "_ac").click(function() {
   	    
   	    var search = jQuery(obj + "_ac").val();
        // if the value is prefilled, we pass a empty string
        if ( search == c_text)
            search = '';            
        // pass empty string as value to search for, displaying all results
        jQuery(obj + "_ac").autocomplete('search', search );
	});
}
