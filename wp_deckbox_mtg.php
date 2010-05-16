<?php
/**
 * @package DeckboxTooltips
 * @author Sebastian Zaha
 * @version 0.1.0
 */
/*
Plugin Name: Deckbox Tooltips
Plugin URI: http://deckbox.org/help/tooltips
Description: This plugin adds a button that creates a card link to deckbox.org, with a card image tooltip.
Author: Sebastian Zaha
Version: 0.1.0
Author URI: http://deckbox.org
*/


function deckbox_register_button($buttons) {
   array_push($buttons, "separator", "deckbox");
   return $buttons;
}

function deckbox_add_tinymce_plugin($plugin_array) {
   $plugin_array['deckbox'] = get_bloginfo('wpurl') . '/wp-content/plugins/wp_deckbox_mtg/resources/tinymce3/editor_plugin.js';
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
