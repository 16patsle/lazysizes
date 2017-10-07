<?php
/**
 * @package Lazysizes
 * @version 0.1.0
 */
/*
Plugin Name: lazysizes
Plugin URI: http://wordpress.org/plugins/lazysizes/
Description: High performance and SEO friendly lazy loader for images (responsive and normal), iframes and more using <a href="https://github.com/aFarkas/lazysizes" target="_blank">lazysizes</a>.
Author: Patrick Sletvold
Author URI: https://www.multitek.no/
Version: 0.1.0
Text Domain: lazysizes
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


class Lazysizes {

  protected $dir; // Plugin directory
  protected $lazysizes_ver = '3.0.0'; // Version of lazysizes (the script, not this plugin)
  protected $settingsClass; // Settings class for admin area
  protected $settings; // Settings for this plugin

  function __construct() {

    // If we're in the admin area, load the settings class
    if (is_admin()) {
      require dirname(__FILE__).'/settings.php';
      $settingsClass = new LazysizesSettings;
      // If this is the first time we've enabled the plugin, setup default settings
      register_activation_hook(__FILE__,array($settingsClass,'first_time_activation'));
      add_filter( 'plugin_action_links_'.plugin_basename(__FILE__), array($settingsClass,'lazysizes_action_links'));
    } else {

      // Store our settings in memory to reduce mysql calls
      $this->settings = $this->get_settings();
      $this->dir = plugin_dir_url(__FILE__);

      // Enqueue lazysizes scripts and styles
      add_action( 'wp_enqueue_scripts', array($this,'load_scripts') );

      // Replace the 'src' attr with 'data-src' in the_content
      add_filter( 'the_content', array($this,'filter_html') );
      // If enabled replace the 'src' attr with 'data-src' in text widgets
      if ($this->settings['textwidgets']) {
        add_filter( 'widget_text', array($this,'filter_html') );
      }
      // If enabled replace the 'src' attr with 'data-src' in the_post_thumbnail
      if ($this->settings['thumbnails']) {
        add_filter( 'post_thumbnail_html', array($this,'filter_html') );
      }
      // If enabled replace the 'src' attr with 'data-src' in the_post_thumbnail
      if ($this->settings['avatars']) {
        //add_filter( 'get_avatar', array($this,'filter_html') );
      }
    }


  }

  function get_settings() {

    // Get setting options from the db
    $general = get_option('lazysizes_general');
    $effects = get_option('lazysizes_effects');
    $addons = get_option('lazysizes_addons');
    $advanced = get_option('lazysizes_advanced');

    // Set the array of options
    $settings_arr = array(
        'minimize_scripts',
        'footer',
        'load_extras',
        'thumbnails',
        'avatars',
        'textwidgets',
        'excludeclasses',
        'fade_in',
        'spinner',
        'auto_load',
      );

    // Start fresh
    $settings = array();
    // Loop through the settings we're looking for, and set them if they exist
    foreach ($settings_arr as $setting) {
      if ($general && array_key_exists('lazysizes_'.$setting,$general)){
        $return = $general['lazysizes_'.$setting];
      } elseif ($effects && array_key_exists('lazysizes_'.$setting,$effects)){
        $return = $effects['lazysizes_'.$setting];
      } elseif ($addons && array_key_exists('lazysizes_'.$setting,$addons)){
        $return = $addons['lazysizes_'.$setting];
      } else {
        // Otherwise set the option to false
        $return = false;
      }
      $settings[$setting] = $return;
    }

    // If enabled, set the advanced settings to an array
    if ($advanced && array_key_exists('lazysizes_enabled', $advanced) && $advanced['lazysizes_enabled']) {
      foreach ($advanced as $key => $val) {
        if ( $key != 'lazysizes_enabled' ) {
          $settings['advanced'][str_replace('lazysizes_','',$key)] = $val;
        }
      }
    } else {
      // Otherwise set it to false
      $settings['advanced'] = false;
    }

    $settings['excludeclasses'] = ($settings['excludeclasses']) ? explode(' ',$settings['excludeclasses']) : array();

    // Return the settings
    return $settings;

  }

  function load_scripts() {

    // Are these minified?
    $min = ($this->settings['minimize_scripts']) ? '.min' : '';
    // Load in footer?
    $footer =  $this->settings['footer'];

    // Set the URLs
    $style_url_pre = $this->dir.'css/lazysizes';
    $script_url_pre = $this->dir.'js/lazysizes';

    // Enqueue fade-in if enabled
    if ( $this->settings['fade_in'] ) {
      wp_enqueue_style( 'lazysizes-fadein-style', $style_url_pre.'.fadein'.$min.'.css', false, $this->lazysizes_ver );
    }
    // Enqueue spinner if enabled
    if ( $this->settings['spinner'] ) {
      wp_enqueue_style( 'lazysizes-spinner-style', $style_url_pre.'.spinner'.$min.'.css', false, $this->lazysizes_ver );
    }

    // Enqueue auto load if enabled
    if ( $this->settings['auto_load'] ) {
      wp_enqueue_script( 'lazysizes-auto', $script_url_pre.'.auto'.$min.'.js', false, $this->lazysizes_ver, $footer );
    }

    wp_enqueue_script( 'lazysizes', $script_url_pre.$min.'.js', false, $this->lazysizes_ver, $footer );

    // Enqueue extras enabled.
    if ( $this->settings['load_extras'] ) {
      wp_enqueue_script( 'lazysizes-unveilhooks', $script_url_pre.'.unveilhooks'.$min.'.js', array('lazysizes'), $this->lazysizes_ver, $footer );
    }
  }

  function filter_html($content) {

    if (is_feed()) {
      return $content;
    }

    if(function_exists('is_amp_endpoint')){
      if (is_amp_endpoint()) {
        return $content;
      }
    }

    // If there's anything there, replace the 'src' with 'data-src'
    if (strlen($content)) {
      $newcontent = $content;
      // Replace 'src' with 'data-src' on images
      $newcontent = $this->preg_replace_html($newcontent,array('img'));
      // If enabled, replace 'src' with 'data-src' on extra elements
      if ($this->settings['load_extras']) {
        $newcontent = $this->preg_replace_html($newcontent,array('iframe', 'video','audio'));
      }
      return $newcontent;
    } else {
      // Otherwise, carry on
      return $content;
    }
  }

  function preg_replace_html($content,$tags) {

    $search = array();
    $replace = array();

    $attrs_array = array('src','poster','srcset');

    // Attributes to search for
    $attrs = implode('|',$attrs_array);
    // Elements requiring a 'src' attribute to be valid HTML
    $src_req = array('img','video');

    // Loop through tags
    foreach($tags as $tag) {

      // Is the tag self closing?
      $self_closing = in_array($tag, array('img','embed','source'));
      // Set tag end, depending of if it's self-closing
      $tag_end = ($self_closing) ? '\/' : '<\/'.$tag;

      // Look for tag in content
      preg_match_all('/<'.$tag.'[\s\r\n]([^<]+)('.$tag_end.'>)(?!<noscript>|<\/noscript>)/is',$content,$matches);

      // If tags exist, loop through them and replace stuff
      if (count($matches[0])) {
        foreach ($matches[0] as $match) {
          preg_match('/[\s\r\n]class=[\'"](.*?)[\'"]/', $match, $classes);
          // If it has assigned classes, explode them
          $classes_r = (array_key_exists(1,$classes)) ? explode(' ',$classes[1]) : array();
          // But first, check that the tag doesn't have any excluded classes
          if (count(array_intersect($classes_r, $this->settings['excludeclasses'])) == 0) {
            // Set the original version for <noscript>
            $original = $match;
            // And add it to the $search array.
            array_push($search, $original);

            // If the element requires a 'src', set the src to default image
            $src = (in_array($tag, $src_req)) ? ' src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"' : '';
            // If the element is an audio tag, set the src to a blank mp3
            $src = ($tag == 'audio') ? ' src="'.$this->dir.'assets/empty.mp3"' : $src;

            // Set replace html
            $replace_markup = $match;
            // Now replace attr with data-attr
            $replace_markup = preg_replace('/[\s\r\n]('.$attrs.')?=/', $src.' data-$1=', $replace_markup);
            // Add lazyload class
            $replace_markup = preg_replace('/class="(.*?)"/', 'class="$1 lazyload"', $replace_markup);
            // And add the original in as <noscript>
            $replace_markup .= '<noscript>'.$original.'</noscript>';
            // And add it to the $replace array.
            array_push($replace, $replace_markup);
          }
        }
      }
    }

    // Replace all the $search items with the $replace items
    $newcontent = str_replace($search, $replace, $content);
    return $newcontent;
  }

}

// Init
$lazysizes = new Lazysizes();

/* API */

// Pass HTML to this function to filter it for lazy loading
function get_lazysizes_html($html = '') {
  global $lazysizes;
  return $lazysizes->filter_html($html);
}
