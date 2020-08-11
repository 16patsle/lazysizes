# lazysizes
Contributors: 16patsle
Tags: lazy load, lazysizes, image, Blurhash, performance
Requires at least: 3.9
Requires PHP: 5.6
Tested up to: 5.5
Stable tag: 1.3.3
License: GPLv3 or later
License URI: <http://www.gnu.org/licenses/gpl-3.0.html>

[![Build Status](https://travis-ci.org/16patsle/lazysizes.svg?branch=master)](https://travis-ci.org/16patsle/lazysizes)

High performance and SEO friendly lazy loader for images, iframes and more. Many features, like low-res Blurhash placeholders and image fade-in

## Description

**lazysizes** is a WordPress plugin for the high performance, SEO-friendly and auto-triggering lazyloader [with the same name](https://github.com/aFarkas/lazysizes). Support includes images (including responsive images with `srcset` and the `picture` tag), iframes, scripts/widgets and much more. It also prioritizes resources by differentiating between crucial in view and near view elements to make perceived performance even faster. Additionally, you can add low-res/blurry placeholder images using the Blurhash algorithm.

This plugin works by loading the lazysizes script and replacing the `src` and `srcset` attributes with `data-src` and `data-srcset` on the front end of a WordPress site. When a post or page is loaded, the lazysizes javascript will load the images, iframes etc. dynamically when needed. All you need to do is to enable the plugin, and possibly tweak a few settings to your liking.

Thanks to aFarkas and contributors for making the [lazysizes library](https://github.com/aFarkas/lazysizes) possible, and for letting me use the same name.

Also thanks to dbhynds who made the Lazy Load XT plugin, which this plugin is based on.

## Frequently Asked Questions

### Why aren't my images lazy loading?

Lazysizes filters images added to the page using `the_content`, `post_thumbnail_html`, `widget_text` and `get_avatar`. If your images are added using another function (`wp_get_attachment_image` for example), lazysizes does not filter them by default. There are several options for changing what lazysizes filters, like enabling it to filter `acf_the_content` for WYSIWYG content from Advanced Custom Fields, and enabling `wp_get_attachment_image` support (somewhat limited, see below). For unsupported use cases you can also filter the HTML yourself by passing it to `get_lazysizes_html`.

While this plugin has opt-in support for `wp_get_attachment_image`, it doesn't add a no-Javascript fallback, which causes images to become invisible for users where Javascript is disabled or unsupported. We cannot fix this for you automatically, but you can fix this with a couple of changes to the code that uses `wp_get_attachment_image`. For example, if a theme has: `echo wp_get_attachment_image($id);`, changing it to the following would lazy load the image and add no-Javascript fallback if enabled in settings: `echo get_lazysizes_html( wp_get_attachment_image($id) );`

If a popular plugin is incompatible and has a filter for modifying the HTML output, lazysizes could most likely get support for that plugin. In that case, feel free to ask! If the plugin has no such way to filter the output, they would have to add one for lazysizes to leverage.

### What is the Blurhash placeholder feature, and how do I use it?

The low-res Blurhash placeholder feature generates a text string for each image using the [Blurhash](https://blurha.sh/) algorithm. This string includes all the information necessary for the Blurhash script running in the visitor's browser to decode it into a blurry image, which will be shown while the real image is loading.

Because the final image placeholder is generated in JavaScript, users on faster internet connections can sometimes see the full image directly for images that are above the fold. Images lower down on the page will have a placeholder ready by the time the user reaches them.

The placeholder Blurhash string is not computed on page load, as it can in some cases take several seconds to do so. Instead, it will need to be pregenerated. As long as Blurhash is enabled in the settings, all new images uploaded will have a Blurhash string generated automatically. Additionally, you can manage the Blurhash string for each attachment individually in the Media Library. There is an option to generate and store Blurhash strings on page load, which can be used to easily generate Blurhash strings for lots of images by visiting the page they're shown on. Just remember to turn that option back off, or your visitors may be slightly upset.

For technical reasons, Blurhash is only supported for local image attachments, and at the moment is not officially supported for picture elements. Images without a Blurhash string will behave just like they do with the option turned off. Blurhash placeholders work with the existing effects, like fade-in, but in some cases the perfect fade-in effect may not be possible. The Blurhash placeholder will still fade in, but it might not fade when transitioning to the full image. This is because of a few edge cases not supported by the advanced Blurhash reveal effect.

The advanced Blurhash reveal effect works by creating an additional image element positioned under the regular image. This gives the best result in combination with the fade effect, but might not support all WordPress themes. Safeguards exist to prevent using the advanced effect when not supported (by falling back to the slightly imperfect fade effect), but in some cases problems may still occur. If you see this type of problem, you can disable the advanced Blurhash reveal effect in the settings. Feel free to contact me on the support forums, and I may be able to work out what was going wrong.

### There's a plugin called Lazy Load XT. Why does this one look a bit similar?

The PHP code for this plugin was originally based on that of Lazy Load XT, with many changes since. The main difference is that this plugin is using a completely different lazy loading library (lazysizes), with no jQuery dependency. Additionally, this plugin is actively maintained and has advanced features like Blurhash support.

Thanks to dbhynds for making the Lazy Load XT plugin. Without that project, this one would not be possible.

### Why is this plugin called lazysizes, the same as the JS library it uses?

There are a few reasons:

1. I like the name name.
2. It is a fitting name, as it makes you think of lazy loading.
3. I'm hoping it will help people discover the plugin. I originally tried searching for a WordPress plugin using the library myself, and other people might be trying the same.

This plugin is not affiliated with the lazysizes project, but I asked for, and got, permission by aFarkas to use the name. That's as far as any connection between the two go.

## Changelog

### 1.3.3

- Add support for WordPress 5.5 and native lazy loading (see point below).
- Add option for full native lazyloading, which gives the browser full control over when to load the image. Not compatible with the old native lazy load option, which only gives the browser partial control over loading. Currently only supports images, other elements will be lazyloaded like previously.
- Add support for transforming HTML using single quotes instead of double quotes for attributes.
- Fix lazy loading for commenter avatars. This feature had actually been disabled for a while, because it was broken. The new support for single quote attributes fixes this.
- Fix positioning of the blurhash placeholder when the image is directly inside a link element.
- Fix Blurhash integration in the Media Library being incompatible with certain other plugins extending this area using JS. This mainly fixes compatibility with the plugin Smush, but should make other plugins more likely to work as well.
- Fix incompatibilities with certain older versions of WordPress. Please note that it is always recommended to use the latest version of WordPress, and that the next major version of this plugin will require a more up to date WordPress installation than it does now.

### 1.3.2

A partial deploy caused by human error led to a fatal error due to missing files for versions v.1.3.0 and v1.3.1. This has been fixed, along with the following:

- Fix warning caused by missing check for metadata existing.
- Fix script for the attachment page being minified even when SCRIPT_DEBUG is true.
- Properly implement Blurhash management support in the post editor.
- Fix non-existent CSS stylesheet being enqueued when no lazy load effect was selected.
- Improve logic for finding attachment id from an image url.
- Fix issue with minified lazysizes script.

### 1.3.0

Note that this is the last release that will support PHP 5.6 and WordPress 3.9. The next release will most likely require PHP 7.2 and WordPress 4.5 or newer.

- Add support for generating low-res placeholder images using [Blurhash](https://blurha.sh/), which stores the placeholder as a short text string. Computing this string does not happen on page load, as it's rather expensive, but when the blurhash placeholder option is enabled it can be controlled individually for each attachment, and new attachments will have a placeholder generated automatically. For more information, see the FAQ and the settings page.
- Add custom lazysizes script and styles feature, which uses scripts and styles optimized for size and fewer requests.
- Improve aspectratio calculation. Local images no longer need both width and height set, only one of them, since the aspect ratio can be calculated based on attachment metadata.
- Various performance tweaks.
- Add experimental option for skipping adding a src attribute to images, and letting the browser load the image progressively instead.
- Fix issue where a picture element with an excluded class could still be lazy loaded.
- Upgrade lazysizes library to 5.2.2.

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
