// Docu : http://wiki.moxiecode.com/index.php/TinyMCE:Create_plugin/3.x#Creating_your_own_plugins

(function() {
	// Load plugin specific language pack
	
	tinymce.create('tinymce.plugins.NextGEN', {
		/**
		 * Initializes the plugin, this will be executed after the plugin has been created.
		 * This call is done before the editor instance has finished it's initialization so use the onInit event
		 * of the editor instance to intercept that event.
		 *
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */
		init : function(ed, url) {
			// Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('mceExample');

			ed.addCommand('mceNextGEN', function() {
				vp = tinymce.DOM.getViewPort();
				H = vp.h-150; //580 < (vp.h - 70) ? 580 : vp.h - 70;
				W = vp.w-200; //650 < vp.w ? 650 : vp.w;
				
				ed.windowManager.open({
				    // call content via admin-ajax, no need to know the full plugin path
					file : ajaxurl + '?action=ngg_tinymce',
					width : W + 'px',
					height : H + 'px',
					inline : 1
				}, {
					ajax_url: ajaxurl, //wp ajaxurl
					plugin_url : url // Plugin absolute URL
				});
			});

			// Register example button
			ed.addButton('NextGEN', {
				title : 'NextCellent',
				cmd : 'mceNextGEN',
				image : url + '/nextgen.gif',
			});
		},

		/**
		 * Creates control instances based in the incomming name. This method is normally not
		 * needed since the addButton method of the tinymce.Editor class is a more easy way of adding buttons
		 * but you sometimes need to create more complex controls like listboxes, split buttons etc then this
		 * method can be used to create those.
		 *
		 * @param {String} n Name of the control to create.
		 * @param {tinymce.ControlManager} cm Control manager to use inorder to create new control.
		 * @return {tinymce.ui.Control} New control instance or null if no control was created.
		 */
		createControl : function(n, cm) {
			return null;
		},

		/**
		 * Returns information about the plugin as a name/value array.
		 * The current keys are longname, author, authorurl, infourl and version.
		 *
		 * @return {Object} Name/value array containing information about the plugin.
		 */
		getInfo : function() {
			return {
					longname  : 'NextCellent',
					author 	  : '',
					authorurl : '',
					infourl   : '',
					version   : "1.9.21"
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('NextGEN', tinymce.plugins.NextGEN);
})();