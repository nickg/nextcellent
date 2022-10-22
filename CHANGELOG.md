# Changelog

All notable changes to this project will be documented in this file. This project adheres to [Semantic Versioning](http://semver.org/).

## 1.9.35 - 2017-10-16

- fix: missing library preventing main window insert tags. Fixed

## 1.9.34 - 2017-10-10

- fix: patch for WP 4.8.2 issue window modal empty. Tested and working, however it could prevent working in other WP versions. Users area advised
  to update WP in order getting Nextcellent operating.
- improved: added filter ngg_pre_delete_image and action ngg_deleted_image to provide some degree of control when deleting a picture.

## 1.9.33 - 2017-09-23

- fix: issue around WP last version prevent from window popup opening.
- fix: internal issue prevent refer images using Media Add
- fix: removed (finally) dependency with AJAX and wp-load.php. Rotation, and thumbnail should work fine.
- fix: issues preventing to display correctly.
- fix: Class constructor warning on PHP 7.1 and aboved
- deprecated: imagerotator.swf: older Nextcellent version depend on Flash part, now replaced with html counterpart
- deprecated: Nextcellent is plupload instead is using swfUpload. For legacy code reasons only swfUpload is mentined but not used.
- improved: core Ajax call simplified

## 19.32 - 2017-07-12

- Fixed few vulnerabilities that turned the plugin down on the repository
- Disabled temporarily upload zip files when creating galleries for vulnerability reasons
- Enforced parameter checking in many places.

## 1.9.31 - 2016-02-09

- Added more help documentation
- Fix Add new page button
- Style improvement
- Enable different size thumbnails only if the option is set
- Wrong url fixed
- Updated cropper library to the latest version
- Fixed few things now working with several PHP versions.
- Few css fixes
- Update setting(s) class(es)
- Several fixes
  ** All credits for Niko Strijbol **

## 1.9.30 - 2016-02-02

- Completely admin rewrite (Credits to Niko Strijbol. See details on https://bitbucket.org/wpgetready/nextcellent/pull-requests/62/rewrite-admin-section)
- Several fixes (Credits to Niko Strijbol)
- Bios4 provided also German translation (Late credits (included in previous version))
- Etard the Live Caster found a XSS vulnerability (Late credits (included in previous version))
- Thomas Bloomberg Hansen: Dashicon in TinyMCE

## Versions 1.9.28 & 1.9.29 - Skipped

## 1.9.27 - 2015-10-01

- Fixes for multisite (credits to Niko Strijbol)
- Fix for slideshow (credits to Niko Strijbol)
- Fix for widget (credits to Niko Strijbol)
- Fix for var_dump in network options (credits to Fernando Zorrilla)
- Manually set ajaxurl in the TinyMCE window (credits to Niko Strijbol)
- Fix injection in albums (credits to Niko Strijbol)
- Fix ajax gallery select in TinyMCE window (credits to Niko Strijbol)
- Fix for PHP warnings (credits to Niko Strijbol)
- Photo swipe integration (credits to Niko Strijbol)
- Change styling PHP Constructor according https://gist.github.com/chriscct7/d7d077afb01011b1839d (credits to Fernando Zorrilla)
- Fix correction suppressed var_dump (Fernando Zorrilla)
- Fix/workaround new WP_List_Table implementation in WP 4.3 (credits to Fernando Zorrilla)
- Danish Translation (credits to Thomas Blomberg Hansen)

## 1.9.26 - 27/03/2015

- Improved Watermark (credits to Niko Strijbol)
- fix: Albums: preview image now correctly shows images from inside the album, not the last uploaded ones.

## 1.9.25.3 - 06/02/2015

- Missing jQuery UI (again), now included
- find_images_in_list incorrect parameter call causing other plugins to break

## 1.9.25.2 - 06/02/2015

- Missing jQuery UI, now included
- Widgets not saving options
- Animation glitches fix

## 1.9.25.1 - 01/02/2015

- Fix: nextcellent crash with an error on some sites with following error:
  Parse error: syntax error, unexpected T_FUNCTION in /home/wpgetrea/public_html/wp-content/plugins/nextcellent-gallery-nextgen-legacy/widgets/class-ngg-slideshow-widget.php on line 174
  Even the problem seems to be related with some old installations using PHP 5.2, we found the same problem with PHP 5.4.x installed. So this is fix is a MUST.
- Fix: AJAX pagination stopped working

## 1.9.25 - 26/01/2015

- Tested up to Wordpress 4.1.
- Fix for zip upload (Niko Strijbol)
- More visual upgrade message (Niko Strijbol)
- Preserve gallery's manual sort order in nggtags shortcode (Frank P. Walentynowicz)
- Add a description field when adding a gallery (Niko Strijbol)
- Show ID when editing a gallery (Niko Strijbol)
- Fix for long album names (Niko Strijbol)
- Update and fix translations (Niko Strijbol)
- Improved Wordpress Integration (Niko Strijbol)
- Image manipulation corrections (Frank P. Walentynowicz)
- Fix manual sort (Niko Strijbol)
- Fixes for multiuser (Niko Strijbol)
- Slideshow Flash retired, replaced with CSS slideshow (Niko Strijbol)
- Code refactoring and optimization (Fernando Zorrilla S.M.)
- Adding QUnit for Javascript Unit testing (Fernando Zorrilla S.M.)

## 1.9.24 - Skipped

## 1.9.23 - 24/09/2014

- Fix for missing gallery stylesheets

## 1.9.22 - 22/09/2014

- Tested up to WordPress 4.0.
- Javascript validation before uploading. (credits to Niko Strijbol)
- Fixed issue with style tabs. (credits to Niko Strijbol)
- Fix: Correction on overview.php in order to make the files translatable. (credits to Balázs Németh)
- Hungarian translation added. (credits to Balázs Németh)
- Additional added from previous version: improved TinyMCE window. (credits to Niko Strijbol)
- Re-added missed translated files.
- Rewrote the style management. (credits to Niko Strijbol)
- Fixed AJAX in the TinyMCE window. (credits to Niko Strijbol)
- Fixed readme.txt.
- A typo was preventing the slideshow from functioning correctly when the dimensions are changed. (credits to Niko Strijbol)

## 1.9.21 - 14/09/2014

- The uploader did not use the quality set in the options. Now it does. (credits to Niko Strijbol)
- Fix: When a gallery is deleted, the ID is removed from albums. (credits to Niko Strijbol)
- Small changes to the readme.txt to make it look better. (credits to Matthew)
- Support for the ngg_styles folder (introduced in NextGEN 2.x), solving issues with updates. (credits to Niko Strijbol)

## 1.9.20 - 20/06/2014

- Fixes on uploader. (credits to Niko Strijbol)
- Fixes for nggtag shortcode. (credits to Niko Strijbol)
- Refactored code in few places.
- Fix vulnerability which disallowed html text & sanitize_taglist function. (credits to NS & FZ)
- Fix for 3.9 and typos for strict warning.
- Improved injections prevention. (credits to jayque9)
- Improve spelling & error message. (credits to Niko Strijbol)

## 1.9.19 - 22/05/2014

- New uploader. Flash uploader deprecated (credits to Niko Strijbol)
- Improved image folder importer. Now fixes folder & files with spaces (credits to Niko Strijbol)
- Removed dead code
- Wikipedia docs in progress http://wpgetready.com/wiki/nextcellent-plugin/ Be free to contribute! (contact us to request permission to edit it)
- Re-fix vuln (previous fix also filtered html data, now fixed)

## 1.9.18 - 23/04/2014

- Fixes compatibility with TinyMCE 1.4, for WordPress 3.9. (credits to Niko Strijbol)
- Fixes use of mysql\_\*(), which isn't allowed anymore. (credits to Niko Strijbol)
- Fixes some style stuff from the datepicker interfering with the style of the tabs. (credits to Niko Strijbol)
- Fixes "flash" upload. This does remove the resize option, but it wasn't working anyway. (credits to Niko Strijbol)
- Vulnerability fix: data isn't properly sanitized before being printed ona Alt & Title (credits to Larry W. Cashdollar)
- Changing date for uploaded images improved (credits to Richard Bale)

## 1.9.17 - 17/03/2014

- Fixes to layout and more (credits to Niko Strijbol)
- Added ability to change image upload + Ajax (credits to Richard Bale)
- Russian translation (credits to Vladimir Vasilenko)
- Finnish translation (credits to Vesa Tiirikainen)
- Album and gallery template extension (currently in revision, credits to Stefano Sudati)
- Improved nggtags shortcode implementing Tony Howden's suggestions (see http://howden.net.au/thowden/2012/12/nextgen-gallery-wordpress-nggtags-template-caption-option/)
  added modes ASC,DESC and RAND

## 1.9.16 - 08/01/2014

- Folder and Image Management improved (credits to Niko Strijbol)
- German translation (credits to Niko Strijbol)
- Improved style for WP 3.8 (credits to Niko Strijbol)
- Improper call to method as static when method belongs to instance. Fixed
- Code cleaning, proper call to static method fixed

## 1.9.15 - 03/10/2013

- Code simplification: code supporting PHP4 has no use. Deprecated.
- Plugin should work with PHP strict standard enabled.
- Improper call to static functions corrected all over the code
- Disabled donator metabox since link is dead

## 1.9.14 - 01/09/2013

- The plugin will deactivate if NextGEN (all versions) plugin is installed & activated
- text messages were adjusted to this plugin, to avoid user confusion.
