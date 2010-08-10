<?php
/**
 * @package MagicTheGatheringCardTooltips
 * @author Sebastian Zaha
 * @version 2.0.0
 */
/*
Plugin Name: Magic the Gathering Card Tooltips
Plugin URI: http://deckbox.org/help/tooltips
Description: Easily transform Magic the Gathering card names into links that show the card image in a tooltip when hovering over them. You can also quickly create deck listings.
Author: Sebastian Zaha
Version: 2.0.0
Author URI: http://sebi.tla.ro
*/


function deckbox_register_button($buttons) {
   array_push($buttons, "separator", "deckbox");
   return $buttons;
}

function deckbox_add_tinymce_plugin($plugin_array) {
   $plugin_array['deckbox'] = get_bloginfo('wpurl') . '/wp-content/plugins/magic-the-gathering-card-tooltips/resources/tinymce3/editor_plugin.js';
   return $plugin_array;
}

function deckbox_parse_mtg_card($atts, $content=null, $code="") {
  return '<a class="deckbox_link" href="http://deckbox.org/mtg/' . $content . '">' . $content . '</a>';
}

function deckbox_parse_mtg_deck($atts, $content=null, $code="") {
  // Clean array of card names 
  $dirty_lines = preg_split("/[\n\r]/", $content);
  $lines = array();

  foreach ($dirty_lines as $line) {
    $clean = trim(strip_tags($line));
    if ($clean != "") {
      $lines[] = $clean;
    }
  }
  
  if ($atts['title']) {
    $response = '<h3 class="mtg_deck_title">' . $atts['title'] . '</h3>';
  }
  $response = $response . '<table class="mtg_deck" cellspacing="0" cellpadding="0" style="width:100%"><tr><td style="vertical-align:top">';

  $current_count = 0; $current_title = ''; $current_body = '';
  for ($i = 0; $i < count($lines); $i++) {
    $line = $lines[$i];

    if (preg_match('/^([0-9]+)(.*)/', $line, $bits)) {
      $card_name = trim($bits[2]);
      $line = $bits[1] . '&nbsp;<a href="http://deckbox.org/mtg/'. $card_name . '">' . $card_name . '</a><br/>';
      $current_body = $current_body . $line;
      $current_count = $current_count + intval($bits[1]);
    } else {
      // Beginning of a new category. If this was not the first one, we put the previous one into the response body.
      if ($current_title != "") {
        $response = $response . '<span style="font-weight:bold">' . $current_title . ' (' . $current_count . ')</span><br/>';
        $response = $response . $current_body;
        if (preg_match("/Sideboard/", $line)) {
          $response = $response . '</td><td style="vertical-align:top">';
        } else {
          $response = $response . '<br/>';
        }
      }
      $current_title = $line; $current_count = 0; $current_body = '';
    }    
  }
  $response = $response . '<span style="font-weight:bold">' . $current_title . ' (' . $current_count . ')</span><br/>' . $current_body;
  $response = $response . '</td></tr></table>';

  return $response;
}

function deckbox_add_buttons() {
   wp_enqueue_script('deckbox', 'http://deckbox.org/javascripts/bin/tooltip.js');

   if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
     return;
 
   // Add only in Rich Editor mode
   if ( get_user_option('rich_editing') == 'true') {
     add_filter("mce_external_plugins", "deckbox_add_tinymce_plugin");
     add_filter('mce_buttons', 'deckbox_register_button');
   }
}

add_action('init', 'deckbox_add_buttons');
add_shortcode('mtg_card', 'deckbox_parse_mtg_card');
add_shortcode('mtg_deck', 'deckbox_parse_mtg_deck');
