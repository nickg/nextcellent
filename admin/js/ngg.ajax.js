/*
 * Ajax Plugin for NextGEN gallery
 * Version:  1.4.1
 * Author : Alex Rabe
 * 
 */
/**
 * The NextCellent AJAX plugin.
 */
(function($) {

    nggAjax = {

        run: function(index) {
            s = this.settings;

            var data = {};

            $.extend(data, {
                action: this.settings.action,
                operation: this.settings.operation,
                '_wpnonce': this.settings.nonce,
                image: this.settings.ids[index]
            }, s.data);

            var req = $.ajax({
                type: "POST",
                url: s.url,
                //data: "action=" + s.action + "&operation=" + s.operation + "&_wpnonce=" + s.nonce + "&image=" + s.ids[index],
                data: data,
                cache: false,
                timeout: 10000,
                success: function(msg) {
                    switch (parseInt(msg)) {
                        case -1:
                            nggProgressBar.addNote(nggAjax.settings.permission);
                            break;
                        case 0:
                            nggProgressBar.addNote(nggAjax.settings.error);
                            break;
                        case 1:
                            // show nothing, its better
                            break;
                        default:
                            // Return the message
                            nggProgressBar.addNote("<strong>ID " + nggAjax.settings.ids[index] + ":</strong> " + nggAjax.settings.failure, msg);
                            break;
                    }

                },
                error: function(msg) {
                    nggProgressBar.addNote("<strong>ID " + nggAjax.settings.ids[index] + ":</strong> " + nggAjax.settings.failure, msg.responseText);
                },
                complete: function() {
                    index++;
                    nggProgressBar.increase(index);
                    // parse the whole array
                    if (index < nggAjax.settings.ids.length)
                        nggAjax.run(index);
                    else
                        nggProgressBar.finished();
                }
            });
        },

        readIDs: function(index, operation, next) {
            s = this.settings;

            var data = {};

            $.extend(data, {
                action: this.settings.action,
                operation: operation,
                '_wpnonce': this.settings.nonce,
                image: this.settings.ids[index]
            }, s.data);

            var req = $.ajax({
                type: "POST",
                url: s.url,
                data: data,
                dataType: "json",
                cache: false,
                timeout: 10000,
                success: function(msg) {
                    // join the array
                    imageIDS = imageIDS.concat(msg);
                },
                error: function(msg) {
                    nggProgressBar.addNote("<strong>ID " + nggAjax.settings.ids[index] + ":</strong> " + nggAjax.settings.failure, msg.responseText);
                },
                complete: function() {
                    index++;
                    nggProgressBar.increase(index);
                    // parse the whole array
                    if (index < nggAjax.settings.ids.length)
                        nggAjax.readIDs(index, operation, next);
                    else {
                        // and now run the image operation
                        index = 0;
                        nggAjax.settings.ids = imageIDS;
                        nggAjax.settings.operation = next;
                        nggAjax.settings.maxStep = imageIDS.length;
                        nggProgressBar.init(nggAjax.settings);
                        nggAjax.run(index);
                    }
                }
            });
        },

        init: function(s) {

            var index = 0;

            /**
             * Get the settings. Some of the settings are loaded with wp_localize_script(), in admin.php.
             */
            this.settings = $.extend({
                url: nggAjaxSetup.url,
                type: "POST",
                action: nggAjaxSetup.action,
                operation: "",
                nonce: nggAjaxSetup.nonce,
                ids: [],
                permission: nggAjaxSetup.permission,
                error: nggAjaxSetup.error,
                failure: nggAjaxSetup.failure,
                timeout: 10000,
                mode: "image",
                data: {}
            }, this.settings, s);

            /**
             * If the mode is gallery, we must first get the image ID's from the galleries.
             *
             * The option with 'gallery_' is deprecated. Please use the 'mode' setting.
             */
            if (this.settings.operation.substring(0, 8) === 'gallery_') {
                //first run, get all the ids
                imageIDS = [];
                this.readIDs(index, 'get_image_ids', this.settings.operation.substring(8));
            } else if(this.settings.mode === "gallery") {
                //first run, get all the ids
                imageIDS = [];
                this.readIDs(index, 'get_image_ids', this.settings.operation);
            } else {
                this.run(index);
            }
        }
    }
}(jQuery));
