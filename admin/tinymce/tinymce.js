function init() {
    tinyMCEPopup.resizeToInnerSize();
}

function getCheckedValue(radioObj) {
    if (!radioObj)
        return "";
    var radioLength = radioObj.length;
    if (radioLength == undefined)
        if (radioObj.checked)
            return radioObj.value;
        else
            return "";
    for (var i = 0; i < radioLength; i++) {
        if (radioObj[i].checked) {
            return radioObj[i].value;
        }
    }
    return "";
}

function insertNGGLink() {

    var tagText;
    var selected = document.getElementsByClassName('current')[0];
    var panel = selected.id;

    // who is active ?
    switch (panel) {
        case 'gallery_panel' :

            var galleryId = document.getElementById('gallerytag').value;
            var galleryTemplate = getCheckedValue(document.getElementsByName('showtype'));
            var customTemplate = document.getElementById('other-name').value;
            var images = document.getElementById('nggallery-images').value;
            var width = document.getElementById('slide-width').value;
            var height = document.getElementById('slide-height').value;

            switch (galleryTemplate) {
                case 'nggallery':
                    if (images) {
                        tagText = "[nggallery id=" + galleryId + " images=" + images + "]";
                    } else {
                        tagText = "[nggallery id=" + galleryId + "]";
                    }
                    break;
                case 'slideshow':
                    tagText = "[slideshow id=" + galleryId;
                    if (width)
                        tagText += " w=" + width;
                    if (height)
                        tagText += " h=" + height;
                    tagText += "]";
                    break;
                case 'imagebrowser':
                    tagText = "[" + galleryTemplate + " id=" + galleryId + "]";
                    break;
                case 'other':
                    tagText = "[nggallery id=" + galleryId + " template=" + customTemplate + "]";
                    break;
                default:
                    tagText = "[nggallery id=" + galleryId + " template=" + galleryTemplate + "]";
            }
            break;
        case 'album_panel':

            var albumId = document.getElementById('albumtag').value;
            var albumType = getCheckedValue(document.getElementsByName('albumtype'));
            var albumGalleryTemplate = getCheckedValue(document.getElementsByName('album-showtype'));

            if (albumGalleryTemplate == 'nggallery') {
                tagText = "[nggalbum id=" + albumId + " template=" + albumType + "]";
            } else {
                tagText = "[nggalbum id=" + albumId + " template=" + albumType + " gallery=" + albumGalleryTemplate + "]";
            }
            break;
        case 'singlepic_panel':

            //get all the options
            var singlepicId = document.getElementById('singlepictag').value;
            var imgWidth = document.getElementById('imgWidth').value;
            var imgHeight = document.getElementById('imgHeight').value;
            var imgEffect = document.getElementById('imgeffect').value;
            var imgFloat = document.getElementById('imgfloat').value;
            var imgLink = document.getElementById('imglink').value;
            var imgCaption = document.getElementById('imgcaption').value;

            tagText = "[singlepic id=" + singlepicId;
            if (imgWidth) {
                tagText += " w=" + imgWidth;
            }
            if (imgHeight) {
                tagText += " h=" + imgHeight;
            }
            if (imgEffect != 0) {
                tagText += " mode=" + imgEffect;
            }
            if (imgFloat != 0) {
                tagText += " float=" + imgFloat;
            }
            if (imgLink) {
                tagText += " link=" + imgLink;
            }
            if (imgCaption) {
                tagText += "]" + imgCaption + "[/singlepic]";
            } else {
                tagText += "]";
            }
            break;
        case 'recent_panel' :

            var recentNumber = document.getElementById('recent-images').value;
            var sort = document.getElementById('sortmode').value;
            var recentGallery = document.getElementById('recentgallery').value;
            var recentTemplate = getCheckedValue(document.getElementsByName('recent-showtype'));

            tagText = "[recent max=" + recentNumber;
            if (sort != 0)
                tagText += " mode=" + sort;
            if (recentGallery != 0)
                tagText += " id=" + recentGallery;
            if (randomTemplate != 'nggallery')
                tagText += " template=" + recentTemplate;
            tagText += "/]";
            break;
        case 'random_panel' :

            var number = document.getElementById('random-images').value;
            var randomGallery = document.getElementById('randomgallery').value;
            var randomTemplate = getCheckedValue(document.getElementsByName('random-showtype'));

            tagText = "[random max=" + number;

            if (randomGallery != 0)
                tagText += " id=" + randomGallery;
            if (randomTemplate != 'nggallery')
                tagText += " template=" + randomTemplate;

            tagText += "/]";
            break;
        default:
            tinyMCEPopup.close();
    }

    if (window.tinyMCE) {
        tinyMCEPopup.editor.insertContent(tagText, false);
        tinyMCEPopup.close();
    }
}