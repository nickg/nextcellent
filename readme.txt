=== NextCellent Gallery - NextGEN Legacy ===
Contributors: wpready
Tags:gallery,image,images,photo,photos,picture,pictures,slideshow,flash,media,thumbnails,photo-albums,NextGEN-gallery,NextGEN,nextcellent-gallery,nextcellent
Requires at least: 3.5
Tested up to: 3.9.1
Stable tag: trunk
License: GPLv2

== Description ==

= 1.9.21 - 14/09/2014 =

 What's in it for you?

* The uploader did not use the quality set in the options. Now it does (credits to Niko Strijbol)
* Fix: When a gallery is deleted, the ID is removed from albums. (credits to Niko Strijbol)
* Small changes readme.txt to look better (Matthew's Random Stuff)
* Support for the ngg_styles folder (introduced in NextGEN 2.x), solving issues with updates (credits to Niko Strijbol)

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

- Backward compatibility with NextGEN plugin version (1.9.13)

- Slow evolving code path, made only by NextGEN user's suggestions. Yep, you read it right: *slow* in counterpart as *fast*. Older code is good enough to keep a community and it worked (and works) for most people. Versions will rollup about once a month.

- A reliable way to work with already installed NextGEN galleries.

- A place for updating the plugin without using FTP manual updates, but Wordpress plugin repository.

- Alternative path preserving backward compatibility (while possible).

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

NextGEN has been the dominant WordPress gallery plugin for years. 
Being said that, NextCellent will provide backward compatibility for NextGEN 1.9.13 and it will evolve according user requirements.

As a result, there is large and great community of users and developers, as well as a large number of dedicated extension plugins. For a list of extension plugins, just search for NextGEN in the WordPress.org plugin repository, or visit our <a href="http://www.NextGEN-gallery.com/NextGEN-gallery-extension-plugins/">Complete List of NextGEN Extension Plugins</a>.

== Credits ==

Copyright:<br>
WpGetReady 2013-2014<br>
Photocrati Media 2012<br>
Alex Rabe 2007-2011<br>

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

** Please note **

NextCellent Gallery's flash slideshow option is powered by the JW Image Rotator from Long Tail Video. The Image Rotator is NOT provided with this plugin. For more information, see the Long Tail Video website: http://www.longtailvideo.com.

== Installation ==

1. 	Download, and install from the usual Wordpress Panel.

2. 	Remember: plugin won't activate if NextGEN is installed and activated. A message will remind you this situation. Please deactivate any NextGEN plugin before installing NextCellent.

3.	From your Wordpress Dashboard, go to Gallery > Add Gallery/Images > Follow the on-screen cues.

3. 	Go to a post/page, and select the NextCellent Gallery button from the Kitchen Sink. Follow the on-screen cues to select, adjust, and publish your gallery.

That's it ... have fun! 

== Screenshots ==

1. Screenshot Admin Area
2. Screenshot Album Selection
3. Screenshot Shutter Effect
4. Screenshot Watermark function
5. Screenshot Flexible template layout
6. Screenshot Show Exif data

== Shortcode ==

NextCellent Plugin Shortcodes are 100% backward compatible with older NGG shortcodes.

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

- Good question. NextCellent makes no significatives differences at this current stage, except minimal text changes and NextGEN detection to avoid user confusion. Aside that, it should be identical, handling your galleries without problems. This is a starting point to anyone using older NextGEN version.

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

- YES. NextCellent is a branch from NextGEN 1.9.13, I will do my best to keep it stable. I can assure the code will lack of dramatic changes over the time, unless there is a big need to do that.

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

- NextCellent will inherit all the code from version 1.9.13 which is both good and bad.  

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

= Are the galleries flash based? =

NextCellent Gallery uses Javascript (J-Query) based displays to ensure compatibility across the widest range of displays possible.

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

- Since the captions are fully HTML capable, you can add external links and any other type of mark up you wish.

= Is NextCellent Gallery available in foreign languages? =

- You should go to original NextGEN plugin to check this out. <a href="http://www.NextGEN-gallery.com/languages/" target="_blank">click here to find out more.</a>
- Many users are creating their respective translations and they will be included, along respective credits. Thanks to them!!!

== Changelog ==

= 1.9.21 - 14/09/2014 =
* The uploader did not use the quality set in the options. Now it does (credits to Niko Strijbol)
* Fix: When a gallery is deleted, the ID is removed from albums. (credits to Niko Strijbol)
* Small changes readme.txt to look better (Matthew's Random Stuff)
* Support for the ngg_styles folder (introduced in NextGEN 2.x), solving issues with updates (credits to Niko Strijbol)

= 1.9.20 - 20/06/2014 =
* Fixes on uploader (credits to Niko Strijbol)
* Fixes for nggtag shortcode (credits to Niko Strijbol)
* Refactored code in few places
* Fix vulnerability which disallowed html text & sanitize_taglist function (credits to NS & FZ)
* Fix for 3.9 and typos for strict warning
* Improved injections prevention (credits to jayque9)
* Improve spelling & error message (credits to Niko Strijbol)

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