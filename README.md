# NextCellent Gallery - NextGEN Legacy

This is a fork of the Nextcellent Gallery wordpress plugin from [Bitbucket](https://bitbucket.org/wpgetready/nextcellent/src/master/)

## What is Nextcellent?

NextCellent is a image gallery plugin, based on older NextGen gallery code

NextCellent provides an alternative for traditional NextGEN users to keep their sites updated without breaking compatibility.

Older subplugins NextGen-compatible will be compatible (prior NextGen 1.9.13 or earlier).

## What do you get with NextCellent Gallery?

This is a compatibility branch with the older NextGen 1.9.13. As such, it will steadily improving and keeping update with software upgrades.
For example, Nextcellent is not supporting Flash slideshow as 2017 for vulnerability reasons. In the same way Nextcellent should work fine with PHP 7.

Backward compatibility with NextGEN plugin version (1.9.13). When we say 'backward' we mean to software level: most filters, actions and shortcodes should work.

Slow evolving code path. Yep, you read it right: _slow_ in counterpart as _fast_. Older code is good enough to keep a community and it worked (and works) for most people. Versions will rollup about once a month. There is another reason for that: we don't have resources to keep a fast pace. So we'll try to improve the code as much as possible, keeping a stable plugin instead developing new features here and there.

A reliable way to work with already installed NextGEN galleries.

Being said that, here are the usual classic features:

NextCellent Gallery provides a powerful engine for uploading and managing galleries of images, with the ability to batch upload, import meta data, add/delete/rearrange/sort images, edit thumbnails, group galleries into albums, and more. It also provides two front-end display styles (slideshows and thumbnail galleries), both of which come with a wide array of options for controlling size, style, timing, transitions, controls, lightbox effects, and more.

## NextCellent WordPress Gallery Plugin Features

### Upload Galleries

- Centralized gallery management. Enjoy a single location where you can see and manage all your galleries.
- Edit galleries. Add or exclude images, change gallery title and description, reorder of images, resize thumbnails.
- Thumbnail Management. Turn thumbnail cropping on and off, customize how individual thumbnails are cropped, and bulk resize thumbnails across one or more galleries.
- Edit Individual Images. Edit meta data and image tags, rotate images, and exclude images.
- Watermarks. Quickly add watermarks to batches or galleries of images.
- Albums. Create and organize collections of galleries, and display them in either compact or extended format.

### Display Galleries

- Two Gallery Types. Choose between two main display styles: Slideshow and Thumbnail, and allow visitors to toggle between the two.
- Slideshow Galleries. Choose from a vast array of options for slideshows, including slideshow size, transition style, speed, image order, and optional navigation bar.
- Thumbnail Galleries. Choose from a wide range of options to customize thumbnail galleries, including 5 different lightboxes for individual images, optional thumbnail cropping and editing, thumbnail styles, captions, and more.
- Single Image Displays. Display and format single images.
- Work with Options Panel or Shortcodes.

## NextCellent WordPress Gallery Plugin Community & Extensions

NextCellent will provide backward compatibility for NextGEN 1.9.13 and it will evolve according user requirements.

As a result, there is large and great community of users and developers, as well as a large number of dedicated extension plugins. For a list of extension plugins, just search for NextGEN in the WordPress.org plugin repository.

## Creating build zip

**Requirements:**

- NodeJS with a package manager like npm

1. Clone the repo.
2. Install packages

```
npm install
```

3. Create a build

```
npm run build
```

4. Create a zip

```
npm run plugin-zip
```

## Contributions

- You are free to download, test and make suggestions and requests.
- Pull requests for documented bugs are highly appreciated.
- If you think you’ve found a bug (e.g. you’re experiencing unexpected behavior), please create a [new issue](https://github.com/nickg/nextcellent/issues).

### Setting up development environment

**Requirements:**

- Composer
- NodeJS with a package manager like npm

**Local development environment with Docker:**

1. Clone the repo.
2. Install packages

```
composer install && npm install
```

3. Setup the local environment with:

```
docker-compose up -d
```

After that the site is available under: [http://localhost:8999](http://localhost:8999)

To use WP-CLI, Docker environment needs to be set up and then you can execute any wp-cli command with (replace [command]):

```
docker-compose run --rm wp-cli [command]
```

To create the JSON language files execute:

```
docker-compose run --rm wp-cli i18n make-json lang block-editor/lang --no-purge
```
