<?php
/*
Plugin Name: Magic the Gathering Card Tooltips
Plugin URI: https://github.com/SebastianZaha/wordpress_mtg_tooltips
Description: Easily transform Magic the Gathering card names into links that show the card
image in a tooltip when hovering over them. You can also quickly create deck listings.
Author: Sebastian Zaha
Version: 3.7.0
Author URI: https://deckbox.org
*/
include('lib/bbp-do-shortcodes.php');


add_action('init', 'deckbox_launch_tooltip_plugin');


function deckbox_launch_tooltip_plugin() {
    $tp = new Deckbox_Tooltip_plugin();
}


if (! class_exists('Deckbox_Tooltip_plugin')) {
    class Deckbox_Tooltip_plugin {
        private $_name;
        private $_value;
        private $_initialValue;
        private $_optionName;
        private $_styles;
        private $_resources_dir;
        private $_images_dir;

        // Default settings constants
        const DEFAULT_STYLE = 0;
        const DEFAULT_DECK_WIDTH = 510;
        const DEFAULT_FONT_SIZE = 100;
        const DEFAULT_LINE_HEIGHT = 140;

        function __construct() {
            $this->_name = 'Magic the Gathering Card Tooltips';
            $this->_optionName = 'deckbox_tooltip_options';
            $this->_value = array();
            $this->_styles = array('tooltip', 'embedded');
            $this->_resources_dir = plugins_url().'/magic-the-gathering-card-tooltips/resources/';
            $this->_images_dir = plugins_url().'/magic-the-gathering-card-tooltips/images/';

            $this->loadSettings();
            $this->init();
            $this->handlePostback();
        }

        function init() {
            add_action('admin_menu', array($this, 'add_option_menu'));
            $this->add_shortcodes();
            $this->add_scripts();
            $this->add_buttons();
        }

        function init_css() {
            echo '<link type="text/css" rel="stylesheet" href="' . $this->_resources_dir .
                'css/wp_deckbox_mtg.css" media="screen" />' . "\n";
        }

        function add_shortcodes() {
            add_shortcode('mtg_card', array($this,'parse_mtg_card'));
            add_shortcode('card', array($this,'parse_mtg_card'));
            add_shortcode('c', array($this,'parse_mtg_card'));
            add_shortcode('mtg_deck', array($this,'parse_mtg_deck'));
            add_shortcode('deck', array($this,'parse_mtg_deck'));
            add_shortcode('d', array($this,'parse_mtg_deck'));
        }

        function add_buttons() {
            if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
                return;

            // Add only in Rich Editor mode
            if ( get_user_option('rich_editing') == 'true') {
                add_filter("mce_external_plugins", array($this,"add_tinymce_plugin"));
                add_filter('mce_buttons', array($this,'register_button'));
            }
        }

        function register_button($buttons) {
            array_push($buttons, "separator", "deckbox");
            return $buttons;
        }

        function add_tinymce_plugin($plugin_array) {
            $plugin_array['deckbox'] = $this->_resources_dir.'tinymce3/editor_plugin.js';
            return $plugin_array;
        }

        function add_scripts() {
            wp_enqueue_script('deckbox', 'https://deckbox.org/javascripts/tooltip.js');
            wp_enqueue_script('deckbox_extensions', $this->_resources_dir.'tooltip_extension.js', array('jquery'));
            add_action('wp_head', array($this, 'init_css'));
        }

        function parse_mtg_card($atts, $content=null) {
            if (preg_match('/^(.+?)(?:\s+\(([A-Za-z0-9]+)\)(?:\s+(\d+))?)?$/', $content, $bits)) {
                $card_name = trim($bits[1]);
                $set_code = isset($bits[2]) && $bits[2] !== '' ? strtoupper($bits[2]) : null;
                $collector_nr = isset($bits[3]) && $bits[3] !== '' ? $bits[3] : null;

                $url = 'https://deckbox.org/mtg/' . $card_name;
                if ($set_code) {
                    $url .= '?set=' . $set_code;
                    if ($collector_nr) {
                        $url .= '&nr=' . $collector_nr;
                    }
                }

                return '<a class="deckbox_link" target="_blank" href="' . esc_attr($url) . '">' . $card_name . '</a>';
            }

            return '<a class="deckbox_link" target="_blank" href="https://deckbox.org/mtg/' . $content . '">' . $content . '</a>';
        }

        function cleanup_shortcode_content($content) {
            $dirty_lines = preg_split("/[\n\r]/", $content);
            $lines = array();

            foreach ($dirty_lines as $line) {
                $clean = trim(strip_tags($line));
                if ($clean != "") {
                    $lines[] = $clean;
                }
            }

            return $lines;
        }

        function parse_mtg_deck($atts, $content=null) {
            extract(shortcode_atts(array(
                        "title" => null,
                        "style" => null,
                    ), $atts));

            $response = '';
            if ($title) {
                $response = '<h3 class="mtg_deck_title">' . esc_html($title) . '</h3>';
            }
            $style = $this->get_clean_style($style);

            $response .= '<table class="mtg_deck mtg_deck_' . $style .
                '" cellspacing="0" cellpadding="0" style="max-width:' .
                $this->get_setting('deck_width') .'px;font-size:' . $this->get_setting('font_size') .
                '%;line-height:' .$this->get_setting('line_height'). '%"><tr><td>';

            $lines = $this->cleanup_shortcode_content($content);
            $response .= $this->parse_mtg_deck_lines($lines, $style) . '</td>';
            $response .= '</tr></table>';

            return $response;
        }

        function parse_deck_structure($lines) {
            $categories = array();
            $current_category = array('name' => '', 'cards' => array());

            foreach ($lines as $line) {
                if (preg_match('/^(\d+)\s+(.+?)(?:\s+\(([A-Za-z0-9]+)\)(?:\s+(\d+))?)?$/', $line, $bits)) {
                    // This is a card line with optional Arena format: "1 Card Name (SET) 123"
                    $card_count = intval($bits[1]);
                    $card_name = trim($bits[2]);
                    $card_name = str_replace("'", "'", $card_name);

                    $set_code = isset($bits[3]) && $bits[3] !== '' ? strtoupper($bits[3]) : null;
                    $collector_nr = isset($bits[4]) && $bits[4] !== '' ? $bits[4] : null;

                    $current_category['cards'][] = array(
                        'count' => $card_count,
                        'name' => $card_name,
                        'set' => $set_code,
                        'nr' => $collector_nr
                    );
                } else {
                    // This is a category header
                    // If we have a previous category with cards, save it
                    if (!empty($current_category['cards']) || $current_category['name'] !== '') {
                        $categories[] = $current_category;
                    }

                    // Start a new category
                    $current_category = array('name' => $line, 'cards' => array());
                }
            }

            // Don't forget the last category
            if (!empty($current_category['cards']) || $current_category['name'] !== '') {
                $categories[] = $current_category;
            }

            return $categories;
        }

        function parse_mtg_deck_lines($lines, $style) {
            $categories = $this->parse_deck_structure($lines);

            $first_card = null;
            $second_column = false;
            $html = '';

            foreach ($categories as $index => $category) {
                $category_name = $category['name'];
                $cards = $category['cards'];

                // Calculate total count for this category
                $total_count = 0;
                $category_body = '';

                foreach ($cards as $card) {
                    if ($first_card === null) {
                        $first_card = $card['name'];
                    }

                    $url = 'https://deckbox.org/mtg/' . $card['name'];
                    if ($card['set']) {
                        $url .= '?set=' . $card['set'];
                        if ($card['nr']) {
                            $url .= '&nr=' . $card['nr'];
                        }
                    }

                    $category_body .= $card['count'] . '&nbsp;<a class="deckbox_link" target="_blank" href="' .
                        esc_attr($url) . '">' . $card['name'] . '</a><br />';
                    $total_count += $card['count'];
                }

                // Only render category if it has cards
                if (!empty($cards)) {
                    // Render category header (skip if first category has no name)
                    if ($index > 0 || $category_name !== '') {
                        $html .= '<span style="font-weight:bold">' . $category_name . ' (' .
                            $total_count . ')</span><br />';
                    }

                    $html .= $category_body;

                    // Handle column breaks
                    if ($index < count($categories) - 1) {
                        $next_category_name = $categories[$index + 1]['name'];
                        if (preg_match("/Sideboard/", $next_category_name) && !$second_column) {
                            $html .= '</td><td>';
                            $second_column = true;
                        } else if (preg_match("/Lands/", $next_category_name) && !$second_column) {
                            $html .= '</td><td>';
                            $second_column = true;
                        } else {
                            $html .= '<br />';
                        }
                    }
                }
            }

            if ($style == 'embedded') {
                $html .= '<td class="card_box"><img class="on_page" src="https://deckbox.org/mtg/' .
                    $first_card . '/tooltip" /></td>';
            }

            return $html;
        }

        function add_option_menu() {
            $title = '';
            if ( version_compare(get_bloginfo('version'), '2.6.999', '>')) {
                $title = '<img src="'.$this->_images_dir.'deckbox_logo.png" alt="deckbox.org" /> ';
            }
            $title .= ' Deckbox Tooltips';

            add_options_page('Deckbox Tooltips', $title, 'read', 'magic-the-gathering-card-tooltips',
                array($this, 'draw_menu'));
        }

        function draw_menu() {
            echo '
              <div class="wrap">
                <h2>Deckbox MtG Card Tooltips Settings</h2><br/>
                <div id="poststuff" class="ui-sortable"><div class="postbox">
                    <h3 style="font-size:14px;">General Settings</h3>
                    <div class="inside">
                      <form action="" method="post" class="deckbox_form" style="padding:20px 0;">
                        '.wp_nonce_field('deckbox_settings_action', 'deckbox_settings_nonce', true, false).'
                        <table class="form-table">
                          <tr>
                            <th class="scope">
                              <label for="tooltip_style">Default Deck Display Style:</label>
                            </th>
                            <td>
                              <select name="tooltip_style">'.$this->get_style_options().'</select>
                              <input type="hidden" name="isPostback" value="1" />
                            </td>
                          </tr><tr>
                            <th class="scope">
                              <label for="tooltip_deck_width">Maximum deck width:</label>
                            </th>
                            <td>
                              <input type="text" size="3" name="tooltip_deck_width" value="' .
                                 $this->get_setting('deck_width') . '"/> px
                            </td>
                          </tr><tr>
                            <th class="scope">
                              <label for="tooltip_font_size">Font size:</label>
                            </th>
                            <td>
                              <input type="text" size="3" name="tooltip_font_size" value="'
                                 . $this->get_setting('font_size') . '"/> %
                            </td>
                          </tr><tr>
                            <th class="scope">
                              <label for="tooltip_line_height">Line height:</label>
                            </th>
                            <td>
                              <input type="text" size="3" name="tooltip_line_height" value="'
                                 . $this->get_setting('line_height') . '"/> %
                            </td>
                          </tr>
                        </table>
                        <p class="submit"><input type="submit" value="Save Changes" class="button" /></p>
                      </form>
                    </div>
                </div></div>
              </div>
			';
        }

        function get_clean_style($style) {
            // Check if $style is provided and exists in predefined styles
            if ($style && in_array($style, $this->_styles)) {
                return $style;
            }

            // Get the default style index from settings
            $default_index = $this->get_setting('style') - 1;

            // Ensure the default index exists in the styles array
            return isset($this->_styles[$default_index]) ? $this->_styles[$default_index] : $this->_styles[0];
        }

        function get_setting($setting) {
            return $this->_value['tooltip'][0][$setting];
        }

        function get_style_options() {
            $options = '';
            for ($i = 0; $i < count($this->_styles); $i++) {
                $n = $i + 1;
                $selected = $this->get_setting('style') == $n ? ' selected="selected"' : '';
                $options .= '<option value="'.$n.'"'.$selected.'>'.$this->_styles[$i].'</option>';
            }
            return $options;
        }

        function loadSettings() {
            $dbValue = get_option($this->_optionName);
            if (strlen($dbValue) > 0) {
                $this->_value = json_decode($dbValue,true);

                if (empty($this->_value['tooltip'][0]['style'])) {
                    $this->_value['tooltip'][0]['style'] = self::DEFAULT_STYLE;
                }
                if (empty($this->_value['tooltip'][0]['deck_width'])) {
                    $this->_value['tooltip'][0]['deck_width'] = self::DEFAULT_DECK_WIDTH;
                }
                if (empty($this->_value['tooltip'][0]['font_size'])) {
                    $this->_value['tooltip'][0]['font_size'] = self::DEFAULT_FONT_SIZE;
                }
                if (empty($this->_value['tooltip'][0]['line_height'])) {
                    $this->_value['tooltip'][0]['line_height'] = self::DEFAULT_LINE_HEIGHT;
                }

                $this->_initialValue = $this->_value;
            } else {
                $deprecated = ' ';
                $autoload = 'yes';
                $value = '{"tooltip":[{"style":"", "deck_width":"", "font_size":"", "line_height":""}]}';
                $result = add_option( $this->_optionName, $value, $deprecated, $autoload );
                $this->loadSettings();
            }
        }

        function handlePostback() {
            if (isset($_POST['isPostback'])) {
                // Validate the nonce
                if (!isset($_POST['deckbox_settings_nonce']) ||
                    !wp_verify_nonce($_POST['deckbox_settings_nonce'], 'deckbox_settings_action')) {
                    wp_die('Security check failed!');
                }

                // Check user capabilities
                if (!current_user_can('manage_options')) {
                    wp_die('You do not have sufficient permissions to access this page.');
                }

                $v = array();
                $v['tooltip'][] = array(
                    'style' => isset($_POST['tooltip_style']) ? absint($_POST['tooltip_style']) : self::DEFAULT_STYLE,
                    'deck_width' => isset($_POST['tooltip_deck_width']) ? absint($_POST['tooltip_deck_width']) : self::DEFAULT_DECK_WIDTH,
                    'font_size' => isset($_POST['tooltip_font_size']) ? absint($_POST['tooltip_font_size']) : self::DEFAULT_FONT_SIZE,
                    'line_height' => isset($_POST['tooltip_line_height']) ? absint($_POST['tooltip_line_height']) : self::DEFAULT_LINE_HEIGHT);
                $this->_value = $v;
                $this->save();
            }
        }

        function save() {
            if (($this->_initialValue != $this->_value)) {
                update_option($this->_optionName, json_encode($this->_value));
                echo '<div class="updated"><p><strong>settings saved</strong></p></div>';
            }
        }
    }
}
