/**
 * NextCellent implementation of jQuery UI Autocomplete.
 *
 * @see http://jqueryui.com/demos/autocomplete/
 * @see /xml/json.php for the API.
 *
 * @version 1.1
 * 
 */
jQuery.fn.nggAutocomplete = function (args) {

    var defaults = {
        type: 'image',
        domain: '',
        limit: 50
    };

    var s = jQuery.extend({}, defaults, args);

    var settings = {
        method: 'autocomplete',
        type: s.type,
        format: 'json',
        callback: 'json',
        limit: s.limit,
        term: s.term
    };

    var obj = this.selector;
    var id = jQuery(this).attr('id');
    var cache = {}, lastXhr;

    /**
     * The element.
     */
    var obj_selector = jQuery(obj);

    /**
     * The current value of the dropdown field.
     */
    var c_text  = jQuery(obj + ' option:selected').text();
    var c_width = s.width;

    /**
     * Hide the drop down field and add the search field.
     */
    obj_selector.hide().after('<input name="' + id + '_ac" type="search" id="' + id + '_ac"/>');

    /**
     * The search field.
     */
    var obj_ac_selector = jQuery(obj + "_ac");

    /**
     * Add the current value and set the style.
     */
    obj_ac_selector.val(c_text).css('width', c_width).addClass('ui-autocomplete-start');

    /**
     * Initiate the autocomplete
     * 20150305: only add term to request if term is not empty
     */
    obj_ac_selector.autocomplete({
        source: function (request, response) {
            var term = request.term;
            console.log(response);
            if (term in cache) {
                    response(cache[term]);
                return;
            }
            // adding more $_GET parameter
            //20150303: invert stetting and request to make term priority
            request = jQuery.extend({}, request, settings);
            lastXhr = jQuery.getJSON(s.domain, request, function (data, status, xhr) {
                // add term to cache
                cache[term] = data;
                if (xhr === lastXhr)
                    response(data);
            });
        },
        minLength: 0,
        select: function (event, ui) {
            /**
             * We we will add this to the selector.
             *
             * @type {Option} The option to be added.
             */
            var option = new Option(ui.item.label, ui.item.id);

            /**
             * Add the select attribute to the option and remove it from the others.
             */
            jQuery(option).attr('selected', true);
            jQuery(obj + " option:selected").attr('selected', false);


            /**
             * Add the option.
             */
            obj_selector.append(option);

            /**
             * Remove autocomplete class.
             */
            obj_ac_selector.removeClass('ui-autocomplete-start');

            /**
             * Update the text selector
             */
            c_text  = ui.item.label;

            /**
             * Trigger a custom event.
             *
             * @since 1.1
             */
            obj_selector.trigger('nggAutocompleteDone');
        }
    });

    obj_ac_selector.click(function () {
        //FZSM 20050307: There is an issue with drop downn list which it can stay behind form editor.
        //this workaround makes drop-down z-index to follow dialo z-index.
        jQuery ('.ui-autocomplete').css('z-index', jQuery('.ui-dialog').zIndex()+1);

        var search = obj_ac_selector.val();

        /**
         * If the selected value is already present, we need to show all images.
         */
        if (search == c_text) {
            search = '';
        }
        obj_ac_selector.autocomplete('search', search);
    });
}