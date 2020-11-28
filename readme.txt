=== NextCellent Gallery - NextGEN Legacy ===
Contributors: wpready
Tags:gallery,image,images,photo,photos,picture,pictures,slideshow,flash,media,thumbnails,photo-albums,NextGEN-gallery,NextGEN,nextcellent-gallery,nextcellent
Requires at least: 4.0
Tested up to: 4.8.2
Stable tag: trunk
License: GPLv2

== Description ==

= 1.9.35 - 2017-10-16 = Fixes for 1.9.34

What's in it for you?

* fix: missing library preventing main window insert tags. Fixed

VERY IMPORTANT: Read ON!
-----------------------

**NextCellent Gallery provides backward compatibility for older NextGEN until version 1.9.13** . 

- **this plugin will gracefully deactivate if detects NextGEN is working (any version) to avoid compatibility issues**.

- Please remember to **READ THE FAQ!!!** <u>Issues for failing to read the FAQ will be IGNORED!!!</u>

- If you like it, please spread the word and rate it accordingly. I guess a lot of annoyed users can take advantage of NextCellent. Thank you!

- WE APPRECIATE YOUR FEEDBACK. Be our voice and comment it!!!!


= What is Nextcellent? =

- NextCellent is a image gallery plugin, based on older NextGen gallery code

- NextCellent provides an alternative for traditional NextGEN users to keep their sites updated without breaking compatibility.

- Older subplugins NextGen-compatible will be compatible (prior NextGen 1.9.13 or earlier).

- Compatibility issues? Please check NextCellent Wiki (in construction) http://wpgetready.com/wiki/nextcellent-plugin/


= What do you get with NextCellent Gallery? =

- This is a compatibility branch with the older NextGen 1.9.13. As such, it will steadily improving and keeping update with software upgrades. 
For example, Nextcellent is not supporting Flash slideshow as 2017 for vulnerability reasons. In the same way Nextcellent should work fine with PHP 7.

- Backward compatibility with NextGEN plugin version (1.9.13). When we say 'backward' we mean to software level: most filters, actions and shortcodes should work.

- Slow evolving code path. Yep, you read it right: *slow* in counterpart as *fast*. Older code is good enough to keep a community and it worked (and works) for most people. Versions will rollup about once a month. There is another reason for that: we don't have resources to keep a fast pace. So we'll try to improve the code as much as possible, keeping a stable plugin instead developing new features here and there.

- A reliable way to work with already installed NextGEN galleries.

- **Development on Bitbucket open to developers suggestions**. (https://bitbucket.org/wpgetready/nextcellent). You are free to download , test and make suggestions and requests.

Being said that, here are the usual classic features:

NextCellent  Gallery provides a powerful engine for uploading and managing galleries of images, with the ability to batch upload, import meta data, add/delete/rearrange/sort images, edit thumbnails, group galleries into albums, and more. It also provides two front-end display styles (slideshows and thumbnail galleries), both of which come with a wide array of options for controlling size, style, timing, transitions, controls, lightbox effects, and more.

= NextCellent WordPress Gallery Plugin Features =

*Upload Galleries*

* Our WordPress gallery plugin offers diverse and powerful functionality for getting images from your desktop to your website. You can easily upload batches of images via a standard WordPress-style uploader, or upload images via zip file or FTP. NextCellent will automatically import your images meta data.

*Manage Galleries*

* Centralized gallery management. Enjoy a single location where you can see and manage all your galleries.
* Edit galleries.  Add or exclude images, change gallery title and description, reorder of images, resize thumbnails.
* Thumbnail Management. Turn thumbnail cropping on and off, customize how individual thumbnails are cropped, and bulk resize thumbnails across one or more galleries.
* Edit Individual Images. Edit meta data and image tags, rotate images, and exclude images.
* Watermarks. Quickly add watermarks to batches or galleries of images.
* Albums. Create and organize collections of galleries, and display them in either compact or extended format.

*Display Galleries*

* Two Gallery Types. Choose between two main display styles: Slideshow and Thumbnail, and allow visitors to toggle between the two.
* Slideshow Galleries. Choose from a vast array of options for slideshows, including slideshow size, transition style, speed, image order, and optional navigation bar.
* Thumbnail Galleries. Choose from a wide range of options to customize thumbnail galleries, including 5 different lightboxes for individual images, optional thumbnail cropping and editing, thumbnail styles, captions, and more.
* Single Image Displays. Display and format single images.
* Work with Options Panel or Shortcodes.

= NextCellent WordPress Gallery Plugin Community & Extensions =

NextCellent will provide backward compatibility for NextGEN 1.9.13 and it will evolve according user requirements.

As a result, there is large and great community of users and developers, as well as a large number of dedicated extension plugins. For a list of extension plugins, just search for NextGEN in the WordPress.org plugin repository, or visit  <a href="http://www.NextGEN-gallery.com/NextGEN-gallery-extension-plugins/">Complete List of NextGEN Extension Plugins</a>.

== Credits ==

Copyright:<br>
WpGetReady 2013-2017<br>
Photocrati Media 2012<br>
Alex Rabe 2007-2011<br>

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

== Installation ==

1. 	Download, and install from the usual Wordpress Panel.

2. 	Remember: plugin won't activate if NextGEN is installed and activated. A message will remind you this situation. Please deactivate any NextGEN plugin before installing NextCellent.

3.	From your Wordpress Dashboard, go to Gallery > Add Gallery/Images > Follow the on-screen cues.

3. 	Go to a post/page, and select the NextCellent Gallery button from the Kitchen Sink. Follow the on-screen cues to select, adjust, and publish your gallery.

That's it ... have fun! 

== Screenshots ==

1. Screenshot - Admin Area
2. Screenshot - Album Selection
3. Screenshot - Shutter Effect
4. Screenshot - Watermark function
5. Screenshot - Flexible template layout
6. Screenshot - Shows Exif data

== Shortcode ==

NextCellent Plugin Shortcodes should be backward compatible with older NGG shortcodes.

= Examples =

*Use Image Tags to Create Galleries/Albums* - [ nggtags album="WordPress,Cologne,Ireland" ]

*Display Captions in Thumbnail Galleries* - [ nggallery id=1 template=caption ]

*Basic Filmstrip Galleries* - [ nggallery id=2 template=carousel images=7 ]

*Display Exif Data* - [ imagebrowser id=28 template=exif ]

*Sort Images in a Gallery Based on Their Tags* - [ nggtags gallery="cologne,wordpress,.." ]

*Add Tag Clouds* - [ tagcloud]

*Single Pic Options* - [ singlepic id=x w=width h=height mode=web20|watermark float=left|right ]

*Template Engine for Gallery Types*<br>
[ nggallery id=1 template=sample1 ]<br>
[ nggallery id=1 template=sample2 ]<br>
[ nggallery id=1 template=sample3 ]<br>
[ nggallery id=1 template=sample4 ]<br>
[ nggallery id=1 template=sample5 ]<br>
[ nggallery id=1 template=sample6 ]<br>

*Integration with Third Party Plugins*<br>
[ monoslideshow id=1 w=450 h=350 ]<br>
[ nggallery id=1 template=galleryview images=0 ]<br>
[ media id=6 width=320 height=240 plugins=revolt-1 ]<br>
[ media id=3 width=320 height=240 plugins=rateit-2 ]<br>

== Frequently Asked Questions ==

= What are the SIGNIFICATIVE differences between NextCellent and NextGEN 1.9.13? =

- Plugin will detect if NextGEN is installed and it will deativate itself. You'll need to deactivate NextGEN in order to properly use NextCellent.
- Plugin will deactivate if you install and activate any NextGEN version (any version).
- Minimal text changes to let the user know what plugin is running. This would affect translated texts in some cases.
- Lot of fixes
- Faster

= What are the SIGNIFICATIVE differences between this NextCellent and NextGEN 2.x.x? =

- New NextGEN (2.0.x and ahead) uses a completely redesigned framework. Compatibility at code level could be close to none. Being said that, most/all plugins depending on NextGEN would need an internal redesign to work with NextGEN 2.0.x

= I'm going to install the plugin on a fresh site. Any recommendations? = 

- None than usual. NextCellent is a FULL plugin which works with Wordpress. Follow the installation procedure!

= I'm currently using NextGEN. How can I switch from NextGEN to NextCellent? =

- Install NextCellent plugin but do not activate. If you accidentally activate it will tell you that it cannot be activated along with NextGEN.
- Deactivate NextGEN plugin.
- Activate NextCellent
- Play around

= Can I go back and switch to NextGEN if NextCellent is not convincing? =

- Absolutely, just activate NextGEN and NextCellent will deactivate itself automatically

= I prefer using NextGEN, how can I uninstall NextCellent plugin without loosing my pictures/album/galleries? =

- Assuming you have both plugins installed:
- If you are unsure, backup the site first(!)
- Be sure NextCellent is deactivated
- MANUALLY remove NextCellent plugin folder (usually nextcellent-gallery-nextgen-legacy). **DO NOT** use traditional Wordpress uninstall procedure. Remember: if you run the uninstall procedure you are wipping out your images/galleries(!)
- Reactivate NextGEN (if it was deactivated)
- That's all

= I switched to NextCellent, how can I uninstall the NextGEN plugin without loosing my pictures/album/galleries? =

- Assuming you have both plugins installed:
- If you are unsure, I backup the site first(!)
- Be sure NextGEN is deactivated
- MANUALLY remove NextGEN plugin folder(usually NextGEN-gallery) . **DO NOT** use traditional Wordpress uninstall procedure. Remember: if you run the uninstall procedure you are wipping out your images/galleries(!)
- Activate NextCellent (if it was deactivated)
- That's all

= I have NextGEN 1.9.13 or older, should I install NextCellent? =

- **YES** if you want an improved version of the older NextGEN.

= If I have NextGEN 2.x.x, should I install NextCellent? =

- In case NextGEN is working right for you and you had never a problem into transition from 1.9.x to 2.x.x, you can safely ignore NextCellent.
- If you are coming from older NextGEN versions (1.9.13 and earlier) and for some reason you want an alternative development path, give this plugin a try.
- There is no guarantee about compatibility with future NextGEN releases.
 
= I am a site owner , would NextCellent be a good option? =

- If you want to keep the older plugin version, choose NextCellent.
- If you have a lot of sites with customized galleries, choose NextCellent. 
- If you have a site with a lot of customized code connected with older NextGEN versions, choose NextCellent.
- If you are getting performance problems, go with NextCellent.
- If you need peace of mind right now, go with NextCellent.
- If you are managing large amounts of sites, and you need update-and-forget without FTP (like InfiniteWP or ManageWP), go with NextCellent.
- if you need a STABLE plugin codebase without suprising code changes choose NextCellent.
- NextCellent is a place where you can download and install currently an older, stable version. And when I say 'older' is completely different of saying obsolete. We (the users an me) are taking a different development path, picking up the old, good code and working from there.  

= I am a developer , would be wise to create plugins based on NextCellent? =

- **YES**. NextCellent is a branch from NextGEN 1.9.13, I will do my best to keep it stable. I can assure the code will lack of dramatic changes over the time, unless there is a big need to do that.

= Are you related with Alex Rabe or PhotoCrati? =

- I feel closely related with the NextGEN Community. Few months ago I choose NextGEN as essential tools for making wordpress sites. I think Alex Rabe work is great and also Photocrati's work. However, I also feel there is a need to be covered between the past and present NextGEN versions and NextCellent is an alternative to most people so they can sleep with peace of mind.

= Will NextCellent continue evolving? =

- **YES**. Current version has many things to be improved. Versions will be numbered as 1.9.x avoiding conflicting NextGEN numbering. NextCellent will respect current configuration and database table format, so you can switch to NextGEN version (theoretically) safely.

= Will NextCellent Gallery work with my theme? =

- NextCellent uses the same codebase and tries to mimic older NextGEN as much as possible. In short, it should!
- There is a NextCellent Wiki addressing some common questions http://wpgetready.com/wiki/nextcellent-plugin/

= Will NextCellent work with my NextGEN Galleries? =

- If you are an older NextGEN user and you have not migrated to the new NextGEN Gallery, this plugin will work for you. If you already migrated to new NextGEN and want to go back, you should try this scenario. I can't answer that right now as September 2013 without further analysis.

= Which is better: NextGEN or NextCellent? =

- NextGEN and NextCellent came from the same root. However, NextGEN 2.x born from a completely different code. It is thought for the future. This has generated some discussion on the forums and with every developer I talked about, aside a growing list of things to be fixed. NextCellent is a mitigation to the situation. Can't be comparable at this point.

- NextCellent inherited all the code from version 1.9.13 which is both good and bad.

Why then the name NextCellent instead NextGEN Legacy for example? =

- Because I believe there is space to improve the plugin while listening the community. It is likely this version continue evolving while NextGEN follows its own path. Can't be two plugins with same name and following different directions, can be? That would be SO confusing for Wordpress users.

= Will NextCellent work with new NextGEN (2.0.x) side by side? =

- This plugin will automatically deactivate if you attempt to do so. This is a way to protect you avoiding conflicts.

= Will NextCellent work with NextGEN plugins and subplugins? =

- Nextcellent will work with all plugins which worked for NextGEN version 1.9.13 or earlier. However be warned many developers already started (and finished)  making the migration to new NextGEN version.

- We are testing case by case on the compatibility list for plugins http://wpgetready.com/wiki/nextcellent-plugin/compatibility-list/

= Wait, I'm using flash gallery along imagerotator.swf but I cannot find it in this plugin!

The reason is very simple: I cannot include non-GPL compatible code inside the plugin , according Wordpress.org policies. 
But you can. Please download it from  http://www.longtailvideo.com and put in the plugin's root. 
UPDATE: Nextcellent ceased support for imagerotator.swef

= Are the galleries flash based? =

NextCellent Gallery uses Javascript (J-Query) based displays to ensure compatibility across the widest range of displays possible.
For security reasons, Nextcellente ceased supporting Flash sliders.

= Are the galleries mobile friendly? =

Yes, since we use Javascript rather than flash, NextCellent Gallery is compatible with Android, iOS, and Blackberry.

= What is the difference between a gallery and an album? =

- In the simplest of terms, Galleries contain your images and Albums contain your Galleries. Albums act as links and placeholders to quickly and easily navigate your galleries - Galleries will actually display your images.

= Can I upload multiple images at once? =

- Yes, you can batch upload entire galleries at a time.

= Can I password protect galleries? =

- Yes, WordPress allows you to password protect pages by default - which includes all galleries and content for the entire page. Password protection of pages can be turned on and off at any time, with just a few clicks.

= Can I add a watermark to the images/slideshows? =

- Yes, you can add text or image watermarks to your gallery images.

= Can I crop thumbnails? =

- Yes, each thumbnail image can be individually adjusted to suit your needs.

= Is there pagination for galleries? =

- Yes, and you can adjust the amount of images to be shown on a page at any time.

= Can I customize the lightbox? =

- Yes, the lightbox can be configured with multiple options directly from the Dashboard, and there are multiple CSS styles which can be applied and modified as well.

= Can I add HTML to the captions? =

- Yes, caption areas are fully HMTL capable.

= Can I add an external links to galleries? =

- Yes,Since the captions are fully HTML capable, you can add external links and any other type of mark up you wish.

= Is NextCellent Gallery available in foreign languages? =

- You should go to original NextGEN plugin to check this out. <a href="http://www.NextGEN-gallery.com/languages/" target="_blank">click here to find out more.</a>
- Many users are creating their respective translations and they will be included, along respective credits. Thanks to them!!!

== Changelog ==

= 1.9.35 - 2017-10-16 = Fixes for 1.9.34

* fix: missing library preventing main window insert tags. Fixed

= 1.9.34 - 2017-10-10 = Fixes for 1.9.33

* fix: patch for WP 4.8.2 issue window modal empty. Tested and working, however it could prevent working in other WP versions. Users area advised
to update WP in order getting Nextcellent operating.
* improved: added filter ngg_pre_delete_image and action ngg_deleted_image to provide some degree of control when deleting a picture.

= 1.9.33 -  2017-09-23 = Fixes for 1.9.32

* fix: issue around WP last version prevent from window popup opening.
* fix: internal issue prevent refer images using Media Add
* fix: removed (finally) dependency with AJAX and wp-load.php. Rotation, and thumbnail should work fine.
* fix: issues preventing to display correctly.
* fix: Class constructor warning on PHP 7.1 and aboved 
* deprecated: imagerotator.swf: older Nextcellent version depend on Flash part, now replaced with html counterpart
* deprecated: Nextcellent is plupload instead is using swfUpload. For legacy code reasons only swfUpload is mentined but not used.
* improved: core Ajax call simplified

= 19.32 -  2017-07-12 = Vulnerability FIX

* Fixed few vulnerabilities that turned the plugin down on the repository
* Disabled temporarily upload zip files when creating galleries for vulnerability reasons
* Enforced parameter checking in many places.

= 1.9.31 - 2016-02-09 = FIX

* Added more help documentation
* Fix Add new page button
* Style improvement
* Enable different size thumbnails only if the option is set
* Wrong url fixed
* Updated cropper library to the latest version
* Fixed few things now working with several PHP versions.
* Few css fixes
* Update setting(s) class(es)
* Several fixes
** All credits for Niko Strijbol **

= 1.9.30 - 2016-02-02 =
* Completely admin rewrite (Credits to Niko Strijbol. See details on https://bitbucket.org/wpgetready/nextcellent/pull-requests/62/rewrite-admin-section)
* Several fixes (Credits to Niko Strijbol)
* Bios4 provided also German translation (Late credits (included in previous version))
* Etard the Live Caster found a XSS vulnerability (Late credits (included in previous version))
* Thomas Bloomberg Hansen: Dashicon in TinyMCE

= Versions 1.9.28 & 1.9.29 - Skipped

= 1.9.27 - 2015-10-01 =
* Fixes for multisite  (credits to Niko Strijbol)
* Fix for slideshow (credits to Niko Strijbol)
* Fix for widget (credits to Niko Strijbol)
* Fix for var_dump in network options (credits to Fernando Zorrilla)
* Manually set ajaxurl in the TinyMCE window (credits to Niko Strijbol)
* Fix injection in albums (credits to Niko Strijbol)
* Fix ajax gallery select in TinyMCE window (credits to Niko Strijbol)
* Fix for PHP warnings (credits to Niko Strijbol)
* Photo swipe integration (credits to Niko Strijbol)
* Change styling PHP Constructor according https://gist.github.com/chriscct7/d7d077afb01011b1839d (credits to Fernando Zorrilla)
* Fix correction suppressed var_dump (Fernando Zorrilla)
* Fix/workaround new WP_List_Table implementation in WP 4.3 (credits to Fernando Zorrilla)
* Danish Translation (credits to Thomas Blomberg Hansen)

= 1.9.26 - 27/03/2015 =
* Improved Watermark (credits to Niko Strijbol)
* fix: Albums: preview image now correctly shows images from inside the album, not the last uploaded ones.

= 1.9.25.3 - 06/02/2015 FIX (last round) =
* Missing jQuery UI (again), now included
* find_images_in_list incorrect parameter call causing other plugins to break

= 1.9.25.2 - 06/02/2015 - FIX =
* Missing jQuery UI, now included
* Widgets not saving options
* Animation glitches fix

= 1.9.25.1 - 01/02/2015 - FIX =
* Fix: nextcellent crash with an error on some sites with following error:
 Parse error: syntax error, unexpected T_FUNCTION in /home/wpgetrea/public_html/wp-content/plugins/nextcellent-gallery-nextgen-legacy/widgets/class-ngg-slideshow-widget.php on line 174
 Even the problem seems to be related with some old installations using PHP 5.2, we found the same problem with PHP 5.4.x installed. So this is fix is a MUST.
* Fix: AJAX pagination stopped working

= 1.9.25 - 26/01/2015 =
* Tested up to Wordpress 4.1.
* Fix for zip upload (Niko Strijbol)
* More visual upgrade message (Niko Strijbol)
* Preserve gallery's manual sort order in nggtags shortcode (Frank P. Walentynowicz) 
* Add a description field when adding a gallery (Niko Strijbol)
* Show ID when editing a gallery (Niko Strijbol)
* Fix for long album names (Niko Strijbol)
* Update and fix translations (Niko Strijbol)
* Improved Wordpress Integration (Niko Strijbol)
* Image manipulation corrections (Frank P. Walentynowicz)
* Fix manual sort (Niko Strijbol)
* Fixes for multiuser (Niko Strijbol)
* Slideshow Flash retired, replaced with CSS slideshow (Niko Strijbol)
* Code refactoring and optimization (Fernando Zorrilla S.M.)
* Adding QUnit for Javascript Unit testing (Fernando Zorrilla S.M.)

= 1.9.24 - Skipped

= 1.9.23 - 24/09/2014 =
* Fix for missing gallery stylesheets

= 1.9.22 - 22/09/2014 =
* Tested up to WordPress 4.0.
* Javascript validation before uploading. (credits to Niko Strijbol)
* Fixed issue with style tabs. (credits to Niko Strijbol)
* Fix: Correction on overview.php in order to make the files translatable. (credits to Balázs Németh)
* Hungarian translation added. (credits to  Balázs Németh)
* Additional added from previous version: improved TinyMCE window. (credits to Niko Strijbol)
* Re-added missed translated files.
* Rewrote the style management. (credits to Niko Strijbol)
* Fixed AJAX in the TinyMCE window. (credits to Niko Strijbol)
* Fixed readme.txt.
* A typo was preventing the slideshow from functioning correctly when the dimensions are changed. (credits to Niko Strijbol)

= 1.9.21 - 14/09/2014 =
* The uploader did not use the quality set in the options. Now it does. (credits to Niko Strijbol)
* Fix: When a gallery is deleted, the ID is removed from albums. (credits to Niko Strijbol)
* Small changes to the readme.txt to make it look better. (credits to Matthew)
* Support for the ngg_styles folder (introduced in NextGEN 2.x), solving issues with updates. (credits to Niko Strijbol)

= 1.9.20 - 20/06/2014 =
* Fixes on uploader. (credits to Niko Strijbol)
* Fixes for nggtag shortcode. (credits to Niko Strijbol)
* Refactored code in few places.
* Fix vulnerability which disallowed html text & sanitize_taglist function. (credits to NS & FZ)
* Fix for 3.9 and typos for strict warning.
* Improved injections prevention. (credits to jayque9)
* Improve spelling & error message. (credits to Niko Strijbol)

= 1.9.19 - 22/05/2014 =
* New uploader. Flash uploader deprecated (credits to Niko Strijbol)
* Improved image folder importer. Now fixes folder & files with spaces (credits to Niko Strijbol)
* Removed dead code
* Wikipedia docs in progress http://wpgetready.com/wiki/nextcellent-plugin/ Be free to contribute! (contact us to request permission to edit it)
* Re-fix vuln (previous fix also filtered html data, now fixed)

= 1.9.18 - 23/04/2014 =
* Fixes compatibility with TinyMCE 1.4, for WordPress 3.9. (credits to Niko Strijbol)
* Fixes use of mysql_*(), which isn't allowed anymore. (credits to Niko Strijbol)
* Fixes some style stuff from the datepicker interfering with the style of the tabs. (credits to Niko Strijbol)
* Fixes "flash" upload. This does remove the resize option, but it wasn't working anyway. (credits to Niko Strijbol)
* Vulnerability fix: data isn't properly sanitized before being printed ona Alt & Title (credits to Larry W. Cashdollar)
* Changing date for uploaded images improved (credits to Richard Bale)

= 1.9.17 - 17/03/2014 =
* Fixes to layout and more (credits to Niko Strijbol)
* Added ability to change image upload + Ajax  (credits to Richard Bale)
* Russian translation (credits to Vladimir Vasilenko)
* Finnish translation (credits to Vesa Tiirikainen)
* Album and gallery template extension (currently in revision, credits to Stefano Sudati)
* Improved nggtags shortcode implementing Tony Howden's suggestions (see http://howden.net.au/thowden/2012/12/nextgen-gallery-wordpress-nggtags-template-caption-option/)
  added modes ASC,DESC and RAND

= 1.9.16 - 08/01/2014 =
* Folder and Image Management improved (credits to Niko Strijbol)
* German translation (credits to Niko Strijbol)
* Improved style for WP 3.8 (credits to Niko Strijbol)
* Improper call to method as static when method belongs to instance. Fixed
* Code cleaning, proper call to static method fixed

= 1.9.15 - 03/10/2013 =
* Code simplification: code supporting PHP4 has no use. Deprecated.
* Plugin should work with PHP strict standard enabled.
* Improper call to static functions corrected all over the code
* Disabled donator metabox since link is dead

= 1.9.14 - 01/09/2013 =
* The plugin will deactivate if NextGEN (all versions) plugin is installed & activated
* text messages were adjusted to this plugin, to avoid user confusion.