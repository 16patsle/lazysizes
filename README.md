# lazysizes
Contributors: 16patsle
Tags: Lazy Load, lazysizes, iframe, image, media, video, YouTube, Vimeo, audio
Requires at least: 3.9
Requires PHP: 5.6
Tested up to: 5.4
Stable tag: 1.2.1
License: GPLv3 or later
License URI: <http://www.gnu.org/licenses/gpl-3.0.html>

[![Build Status](https://travis-ci.org/16patsle/lazysizes.svg?branch=master)](https://travis-ci.org/16patsle/lazysizes)

High performance, easy to use and SEO friendly lazy loader for images, iframes and more

## Description

**lazysizes** is a WordPress plugin for the fast (jank-free), SEO-friendly and self-initializing lazyloader [with the same name](https://github.com/aFarkas/lazysizes). Support includes images (including responsive images with `srcset` and the `picture` tag), iframes, scripts/widgets and much more. It also prioritizes resources by differentiating between crucial in view and near view elements to make perceived performance even faster.

This plugin works by loading the lazysizes script and replacing the `src` and `srcset` attributes with `data-src` and `data-srcset` on the front end of a WordPress site. When a post or page is loaded, the lazysizes javascript will load the images, iframes etc. dynamically when needed.

Thanks to aFarkas and contributors for making the [lazysizes project](https://github.com/aFarkas/lazysizes) possible, and for letting me use the same name.

Also thanks to dbhynds who made the Lazy Load XT plugin, which this plugin is based on.

## Installation

1. Install and activate the plugin through the 'Plugins' menu in WordPress

or

1. Download and unzip lazysizes.
2. Upload the `lazysizes` folder to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress

## Frequently Asked Questions

### Why aren't my images lazy loading?

Lazysizes filters images added to the page using `the_content`, `post_thumbnail_html`, `widget_text` and `get_avatar`. If your images are added using another function (`wp_get_attachment_image` for example), lazysizes does not filter them by default. There are several options for changing what lazysizes filters, like enabling it to filter `acf_the_content` for WYSIWYG content from Advanced Custom Fields, and enabling `wp_get_attachment_image` support (somewhat limited, see below). For unsupported use cases you can also filter the HTML yourself by passing it to `get_lazysizes_html`.

While this plugin has opt-in support for `wp_get_attachment_image`, it doesn't add a no-Javascript fallback, which causes images to become invisible for users where Javascript is disabled or unsupported. We cannot fix this for you automatically, but you can fix this with a couple of changes to the code that uses `wp_get_attachment_image`. For example, if a theme has: `echo wp_get_attachment_image($id);`, changing it to the following would lazy load the image and add no-Javascript fallback if enabled in settings: `echo get_lazysizes_html( wp_get_attachment_image($id) );`

### But this plugin looks like Lazy Load XT!

Yes, it does. The PHP code for this plugin is heavily based on that of Lazy Load XT. The main difference is that this plugin is a bit simplified, and is using a completely different lazy loading library, with no jQuery dependency.

Thanks to dbhynds for making the Lazy Load XT plugin. Without that project, this one would not be possible.

### Why is this plugin called the same as the lazysizes JS library?

There are a couple of reasons:

1. I think it's a good name.
2. I'm hoping it will help people discovering the plugin. I originally tried searching for a WordPress plugin using the library myself, and other people might be trying the same.

This plugin is not affiliated with the lazysizes project. I got permission by aFarkas to use the name, but that's as far as any connection between the two go.

## Changelog

### 1.3.0

- Various performance tweaks.
- Add experimental option for skipping adding a src attribute to images, and letting the browser load the image progressively instead.

### 1.2.1

- Improve logic for skipping transforming images inside noscript tags. Should fix compatibility issues with Envira Gallery's noscript fallback. Thanks to snippet24 for reporting.
- Fix default options not being selected. If you were affected by this bug, see a list of [recommended default options here](https://wordpress.org/support/topic/recommended-starting-settings-perhaps/#post-12886169). Thanks to snippet24 for reporting.

### 1.2.0

- Upgrade lazysizes library to version 5.2.0.
- Add opt-in support for Advanced Custom Fields.
- The plugin now uses namespaces for PHP classes.
- Confirmed working with WordPress 5.3 and PHP 7.4.

### 1.1.0

- Upgrade lazysizes library to version 5.0.0.
- Add experimental support for native lazy loading.
- Fix fatal error during ajax processing. Thanks to @eastgate for reporting.
- Fix PHP warning on certain pages, like the events page from the plugin The Events Calendar. Thanks @julian_wave for reporting.

### 1.0.0

Big thanks to martychc23 and dutze for their help and patience in making this release as good as it is.

- Proper support for the picture tag, by popular request. Big refactoring of the HTML transforming code was done to make picture element support possible.
- Improve and fix support for audio/video elements. The plugin now handles the preload attribute and leaves the src attribute alone on source elements inside video/audio.
- Opt-in support for get_attachment_image. Please note that the plugin cannot add a no-js fallback for images lazy-loaded using this method.
- Add option to enable/disable noscript fallback
- Fix plugin action links
- Several fixes to improve compatibility

### 0.3.0

- Add support for the aspectratio plugin for lazysizes, which makes images have the right height while loading. Thanks to Teemu Suoranta (@teemusuoranta) for implementing.
- If Javascript is turned off, the image tag that would normally be lazy loaded is now hidden properly. Thanks to @diegocanal for reporting and fixing.

### 0.2.0

- Update the lazysizes library to version 4.1.5
- Fix lazy loading of elements without a class attribute, like some iframes
- Fix translation loading

### 0.1.3

- Remove unused code for advanced settings

### 0.1.2

- Fix text domain loading

### 0.1.1

- Updated readme

### 0.1.0

- Initial version of the plugin
