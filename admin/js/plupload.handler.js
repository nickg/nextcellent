/**
 * NextGEN Gallery - plupload Handlers 
 *
 * Built on top of the plupload library
 *   http://www.plupload.com version 1.4.2
 *
 *  version 1.0.0
 */

// on load change the upload to plupload
function initUploader() {

	jQuery(document).ready(function($){
	   
    	/* Not working in chrome, needs rework
        var dropElm = jQuery('#' + uploader.settings.drop_element);
    	if (dropElm.length && uploader.features.dragdrop) {
    		dropElm.bind('dragenter', function() {
    			jQuery(this).css('border', '3px dashed #cccccc');
    		});
    		dropElm.bind('dragout drop', function() {
    			jQuery(this).css('border', 'none');
    		});
    	}*/
        
        if ( uploader.features.dragdrop )
				jQuery('.ngg-dragdrop-info').show();
        	
        jQuery("#uploadimage_btn").after("<input class='button-primary' type='button' name='uploadimage' id='plupload_btn' value='" + uploader.settings.i18n.upload + "' />")
                                  .remove();
    	jQuery("#plupload_btn").click( function() {
			//check if a gallery is selected
			if (jQuery('#galleryselect').val() > "0") {
				uploader.start(); 
			} else {
				event.preventDefault();
				alert( pluploadL10n.no_gallery );
			}
		});
	}); 
}

// called when a file is added
function fileQueued( fileObj ) {
    debug('[FilesAdded]', fileObj);
    
	filesize = " (" + plupload.formatSize(fileObj.size) + ") ";
	jQuery("#txtFileName").val(fileObj.name);
	jQuery("#uploadQueue")
		.append("<div id='" + fileObj.id + "' class='nggUploadItem'> [<a href=''>" + uploader.settings.i18n.remove + "</a>] " + fileObj.name + filesize + "</div>")
		.children("div:last").slideDown("slow")
		.end();
    jQuery('#' + fileObj.id + ' a').click(function(e) {
        jQuery('#' + fileObj.id).remove();
		uploader.removeFile(fileObj);
		e.preventDefault();
	});        
}

// called before the uploads start
function uploadStart(fileObj) {
    debug('[uploadStart]');
    nggProgressBar.init(nggAjaxOptions);
	debug('[gallery selected]');
	// update the selected gallery in the post_params 
	uploader.settings.multipart_params.galleryselect = jQuery('#galleryselect').val();   
	return false;
}

// called during the upload progress
function uploadProgress(fileObj, bytesDone, bytesTotal) {
    var percent = Math.ceil((bytesDone / bytesTotal) * 100);
    debug('[uploadProgress]', fileObj.name + ' : ' + percent + "%");
    nggProgressBar.increase( percent );
	jQuery("#progressbar span").text(percent + "% - " + fileObj.name);
}

// called when all files are uploaded
function uploadComplete(fileObj) {
    debug('[uploadComplete]');

	// Upload the next file until queue is empty
	if ( uploader.total.queued == 0) {
        //TODO: we submit here no error code
		jQuery('#uploadimage_form').prepend("<input type=\"hidden\" name=\"swf_callback\" value=\"0\">");
        nggProgressBar.finished();   
		jQuery("#uploadimage_form").submit();				 
	}	
}

// called when the file is uploaded
function uploadSuccess(fileObj, serverData) {
    debug('[uploadSuccess]', serverData);
    
    if (serverData.response != 0)
        nggProgressBar.addNote("<strong>ERROR</strong>: " + fileObj.name + " : " + serverData.response);
    
	jQuery("#" + fileObj.id).hide("slow");
	jQuery("#" + fileObj.id).remove();
}

function cancelUpload() {
	uploader.stop();
	jQuery.each(uploader.files, function(i,file) {
		if (file.status == plupload.STOPPED)
			jQuery('#' + file.id).remove();
	});
}

function uploadError(fileObj, errorCode, message) {
    debug('[uploadError]', errorCode, message);
	error_name = fileObj.name + ': ';
	switch (errorCode) {
		case plupload.FAILED:
			message = pluploadL10n.upload_failed;
			break;
		case plupload.FILE_EXTENSION_ERROR:
			message = pluploadL10n.invalid_filetype;
			break;
		case plupload.FILE_SIZE_ERROR:
			message = pluploadL10n.file_exceeds_size_limit;
			break;
		case plupload.IMAGE_FORMAT_ERROR:
			message = pluploadL10n.not_an_image;
			break;
		case plupload.IMAGE_MEMORY_ERROR:
			message = pluploadL10n.image_memory_exceeded;
			break;
		case plupload.IMAGE_DIMENSIONS_ERROR:
			message = pluploadL10n.image_dimensions_exceeded;
			break;
		case plupload.GENERIC_ERROR:
			message = pluploadL10n.upload_failed;
			break;
		case plupload.IO_ERROR:
			message = pluploadL10n.io_error;
			break;
		case plupload.HTTP_ERROR:
			message = pluploadL10n.http_error;
			break;
		case plupload.INIT_ERROR:
            /* what should we do in this case ? */
			//switchUploader(0);
			//jQuery('.upload-html-bypass').hide();
			break;
		case plupload.SECURITY_ERROR:
			message = pluploadL10n.security_error;
			break;
		case plupload.UPLOAD_ERROR.UPLOAD_STOPPED:
		case plupload.UPLOAD_ERROR.FILE_CANCELLED:
			break;
		default:
			FileError(fileObj, pluploadL10n.default_error);
	}
	//nggProgressBar.addNote("<strong>ERROR " + error_name + " </strong>: " + message);
	jQuery('#plupload-upload-ui').prepend('<div id="file-' + fileObj.id + '" class="error"><p style="margin: auto;">' + error_name + message + '</p></div>');
	jQuery("#" + fileObj.id).hide("slow");
	jQuery("#" + fileObj.id).remove();
}

function debug() {
    if ( uploader.settings.debug ) {
        plupload.each(arguments, function(message) {
        	var exceptionMessage, exceptionValues = [];
        
        	// Check for an exception object and print it nicely
        	if (typeof message === "object" && typeof message.name === "string" && typeof message.message === "string") {
        		for (var key in message) {
        			if (message.hasOwnProperty(key)) {
        				exceptionValues.push(key + ": " + message[key]);
        			}
        		}
        		exceptionMessage = exceptionValues.join("\n") || "";
        		exceptionValues = exceptionMessage.split("\n");
        		exceptionMessage = "EXCEPTION: " + exceptionValues.join("\nEXCEPTION: ");
        		if (window.console)
        			console.log(exceptionMessage);
        		else	
        			debugConsole(exceptionMessage);
        	} else {
        		if (window.console)
        			console.log(message);
        		else
        			debugConsole(message);
        	}
        });
    }
};

function debugConsole(message) {
	var console, documentForm;

	try {
		console = document.getElementById("plupload_Console");

		if (!console) {
			documentForm = document.createElement("form");
			document.getElementsByTagName("body")[0].appendChild(documentForm);

			console = document.createElement("textarea");
			console.id = "plupload_Console";
			console.style.fontFamily = "monospace";
			console.setAttribute("wrap", "off");
			console.wrap = "off";
			console.style.overflow = "auto";
			console.style.width = "99%";
			console.style.height = "350px";
			console.style.margin = "5px";
			documentForm.appendChild(console);
		}

		console.value += message + "\n";

		console.scrollTop = console.scrollHeight - console.clientHeight;
	} catch (ex) {
		alert("Exception: " + ex.name + " Message: " + ex.message);
	}
};
