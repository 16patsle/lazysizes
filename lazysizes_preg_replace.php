<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class LazysizesPregReplace {

  protected $dir; // Plugin directory
  protected $lazysizes_ver = '4.1.5'; // Version of lazysizes (the script, not this plugin)
  protected $settings; // Settings for this plugin

  function __construct($pluginSettings) {
    // Store our settings in memory to reduce mysql calls
    $this->settings = $pluginSettings;
    $this->dir = plugin_dir_url(__FILE__);
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
            // If there are no class attribute, add one
            if (!count($classes_r)){
              $replace_markup = preg_replace('/<(' . $tag . '.*?)>/', '<$1 class="lazyload">', $replace_markup);
            }

            // Set aspect ratio
            preg_match('/width="([^"]*)"/i', $replace_markup, $match_width);
            $width = !empty($match_width) ? $match_width[1] : '';
            preg_match('/height="([^"]*)"/i', $replace_markup, $match_height);
            $height = !empty($match_height) ? $match_height[1] : '';
            if (!empty($width) && !empty($height)) {
                $replace_markup = preg_replace('/ width="/', ' data-aspectratio="' . absint($width) . '/' . absint($height) . '" width="', $replace_markup);
            }

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
