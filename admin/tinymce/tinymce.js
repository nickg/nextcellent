function init() {
	tinyMCEPopup.resizeToInnerSize();
}

function getCheckedValue(radioObj) {
	if(!radioObj)
		return "";
	var radioLength = radioObj.length;
	if(radioLength == undefined)
		if(radioObj.checked)
			return radioObj.value;
		else
			return "";
	for(var i = 0; i < radioLength; i++) {
		if(radioObj[i].checked) {
			return radioObj[i].value;
		}
	}
	return "";
}

function insertNGGLink() {

	var tagtext;
	var selected = document.getElementsByClassName('current')[0];
	var panel = selected.id;

	// who is active ?
	switch(panel) {
	case 'gallery_panel' :

		var galleryid = document.getElementById('gallerytag').value;
		var showtype = getCheckedValue(document.getElementsByName('showtype'));
		var images = document.getElementById('nggallery-images').value;
		var width = document.getElementById('slide-width').value;
		var height = document.getElementById('slide-height').value;
		var otherName = document.getElementById('other-name').value;
		
		switch(showtype) {
			case 'nggallery':
				if (images) {
					tagtext = "[nggallery id=" + galleryid + " images=" + images + "]";
				} else {
					tagtext = "[nggallery id=" + galleryid + "]";
				}
				break;
			case 'slideshow':
				tagtext = "[slideshow id=" + galleryid;
				if (width)
					tagtext += " w=" + width;
				if (height)
					tagtext += " h=" + height;
				tagtext += "]";
				break;
			case 'imagebrowser':
				tagtext = "["+ showtype + " id=" + galleryid + "]";
				break;
			case 'other':
				tagtext = "[nggallery id=" + galleryid + " template="+ otherName + "]";
			default:
				tagtext = "[nggallery id=" + galleryid + " template="+ showtype + "]";
		}
		break;
	case 'album_panel':

		var albumid = document.getElementById('albumtag').value;
		var showtype = getCheckedValue(document.getElementsByName('albumtype'));
		var albumshowtype = getCheckedValue(document.getElementsByName('album-showtype'));

		if (albumshowtype == 'nggallery') {
			tagtext = "[nggalbum id=" + albumid + " template=" + showtype + "]";
		} else {
			tagtext = "[nggalbum id=" + albumid + " template=" + showtype + " gallery=" + albumshowtype + "]";
		}
		break;
	case 'singlepic_panel':
	
		//get all the options
		var singlepicid = document.getElementById('singlepictag').value;
		var imgWidth = document.getElementById('imgWidth').value;
		var imgHeight = document.getElementById('imgHeight').value;
		var imgeffect = document.getElementById('imgeffect').value;
		var imgfloat = document.getElementById('imgfloat').value;
		var imglink = document.getElementById('imglink').value;
		var imgcaption = document.getElementById('imgcaption').value;

		tagtext = "[singlepic id=" + singlepicid;
		if (imgWidth)
			tagtext += " w=" + imgWidth;
		if (imgHeight)
			tagtext += " h=" + imgHeight;
		if (imgeffect != 0 )
			tagtext += " mode=" + imgeffect;
		if (imgfloat != 0 )
			tagtext += " float=" + imgfloat;
		if (imglink)
			tagtext += " link=" + imglink;
		if (imgcaption) {
			tagtext += "]" + imgcaption + "[/singlepic]";
		} else {
			tagtext += "]";
		}
		break;
	case 'recent_panel' :

		var number = document.getElementById('recent-images').value;
		var sort = document.getElementById('sortmode').value;
		var gallery = document.getElementById('recentgallery').value;
		var recentshowtype = getCheckedValue(document.getElementsByName('recent-showtype'));

		tagtext = "[recent max=" + number;
		if (sort != 0)
			tagtext += " mode=" + sort;
		if (gallery != 0)
			tagtext += " id=" + gallery;
		if (recentshowtype != 'nggallery')
			tagtext += " template=" + recentshowtype;
		tagtext += "/]";
		break;
	case 'random_panel' :

		var number = document.getElementById('random-images').value;
		var gallery = document.getElementById('randomgallery').value;
		var recentshowtype = getCheckedValue(document.getElementsByName('random-showtype'));

		tagtext = "[random max=" + number;
		
		if (gallery != 0)
			tagtext += " id=" + gallery;
		if (recentshowtype != 'nggallery')
			tagtext += " template=" + recentshowtype;
		
		tagtext += "/]";
		break;
	default:
		tinyMCEPopup.close();
	}

	if(window.tinyMCE) {
		tinyMCEPopup.editor.insertContent(tagtext, false);
		tinyMCEPopup.close();
	}
	return;
}