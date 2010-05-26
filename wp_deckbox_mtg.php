<?php
/**
 * @package MagicTheGatheringCardTooltips
 * @author Sebastian Zaha
 * @version 1.0.3
 */
/*
Plugin Name: Magic the Gathering Card Tooltips
Plugin URI: http://deckbox.org/help/tooltips
Description: Easily transform Magic the Gathering card names into links that show the card image in a tooltip when hovering over then.
Author: Sebastian Zaha
Version: 1.0.3
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
