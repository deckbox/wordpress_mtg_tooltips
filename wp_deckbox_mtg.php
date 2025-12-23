<?php
/*
Plugin Name: Magic the Gathering Card Tooltips
Plugin URI: https://github.com/SebastianZaha/wordpress_mtg_tooltips
Description: Easily transform Magic the Gathering card names into links that show the card
image in a tooltip when hovering over them. You can also quickly create deck listings.
Author: Sebastian Zaha
Version: 3.6.0
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

        private $validSymbols = [
                                    '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15','16', '17', '18', '19', '20', '100', '1000000',
                                    'W', 'U', 'B', 'R', 'G', 'C',
                                    'WU', 'WB', 'UR', 'UB', 'BR', 'BG', 'RG', 'RW', 'GW', 'GU',
                                    '2W', '2U', '2B', '2R', '2G',
                                    'WP', 'UP', 'BP', 'RP', 'GP',
                                    'UB', 'GW', 'BG', 'BR', 'GU', 'GW', 'RG', 'RW', 'UB', 'UR', 'WB', 'WU',
                                    'X', 'Y', 'Z',
                                    'TAP', 'UNTAP',
                                    'UNTAPCOST',
                                    'TAPOLD', 'WOLD', 'HALF', 'INF'
                                ];

        private $validShadowsDisablement = ['false', '0'];

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
			add_shortcode('s', array($this,'parse_mtg_symbol'));
			add_shortcode('symbol', array($this, 'parse_mtg_symbol'));
			add_shortcode('color_identity', array($this, 'parse_color_identity'));
			add_shortcode('ci', array($this, 'parse_color_identity'));
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
			extract(shortcode_atts(array(
				"meta_custom_field" => null
			), $atts));

            if ($content === '') {
				$content = get_post_meta(get_the_id(), $meta_custom_field, true);
			}

            return '<a class="deckbox_link" target="_blank" href="https://deckbox.org/mtg/' . $content . '">' . $content . '</a>';
        }

		function parse_color_identity($atts, $content=null) {
            extract(shortcode_atts(array(
                        "size" => null,
                        "colors" => null,
                        "shadow" => null,
						"meta_custom_field" => null
                    ), $atts));

			if ($colors === null) {
				$colors = get_post_meta(get_the_id(), $meta_custom_field, true);
			}

			$colorsArray = str_split($colors);

			$line = '';

			foreach ($colorsArray as $color) {
				$line .= $this->parse_mtg_symbol(['symbol' => $color, 'size' => $size, 'shadow' => $shadow]).' ';
			}

			return $line;
		}

        function parse_mtg_symbol($atts, $content=null) {
            extract(shortcode_atts(array(
                        "size" => null,
                        "symbol" => null,
                        "shadow" => null
                    ), $atts));

            if (in_array(strtoupper($symbol), $this->validSymbols)) {
                $symbol = strtolower($symbol);
            } else {
                return '';
            }

			if (!$size) {
				$size = '1em';
			}

            $sizeUnits = preg_replace('/[0-9]/', '', $size);

            preg_match('^(auto|0|(\d*\.?\d+(px|em|ex|%|in|cm|mm|pt|pc|vh|vw|vmin|vmax)?))$', $size, $matches);

            if ($matches === 0) {
                return '';
            }

            $sizeh = $sizew = (float) filter_var($size, FILTER_SANITIZE_NUMBER_FLOAT);

            if ($symbol == '1000000') {
                $sizew = $sizeh * 5.07;
            } else if ($symbol === '100') {
                $sizew = $sizeh * 1.88;
            }

            if (!in_array(strtolower($shadow), $this->validShadowsDisablement)) {
                $sizeShadow = $sizeh * 0.05;

                $shadow = 'box-shadow: -'.$sizeShadow.$sizeUnits.' '.$sizeShadow.$sizeUnits.' 0 #000000;border-radius:'.$sizeh.$sizeUnits.';';
            } else {
                $shadow = '';
            }

            return '<img src="'.plugins_url( 'images/'.$symbol.'.svg', __FILE__ ).'" style="height:'.$sizeh.$sizeUnits.';width:'.$sizew.$sizeUnits.';'.$shadow.'" >';
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
						"meta_custom_field" => null
                    ), $atts));

            if ($title) {
                $response = '<h3 class="mtg_deck_title">' . esc_html($title) . '</h3>';
            }
            $style = $this->get_clean_style($style);

            $response .= '<table class="mtg_deck mtg_deck_' . $style .
                '" cellspacing="0" cellpadding="0" style="max-width:' .
                $this->get_setting('deck_width') .'px;font-size:' . $this->get_setting('font_size') .
                '%;line-height:' .$this->get_setting('line_height'). '%"><tr><td>';

			$lines = $this->cleanup_shortcode_content($content);

			if ($content === '' && $meta_custom_field !== null) {
				$lines = $this->cleanup_shortcode_content(get_post_meta(get_the_ID(), $meta_custom_field, true));
			}

            $response .= $this->parse_mtg_deck_lines($lines, $style) . '</td>';
            $response .= '</tr></table>';

            return $response;
        }

        function parse_mtg_deck_lines($lines, $style) {
            $current_count = 0;
            $current_title = '';
            $current_body = '';
            $first_card = null;
            $second_column = false;
            $html = '';

            for ($i = 0; $i < count($lines); $i++) {
                $line = $lines[$i];

                if (preg_match('/^([0-9]+)(.*)/', $line, $bits)) {
                    $card_name = trim($bits[2]);
                    $first_card = $first_card == null ? $card_name : $first_card;
                    $card_name = str_replace("’", "'", $card_name);
                    $line = $bits[1] . '&nbsp;<a class="deckbox_link" target="_blank" href="https://deckbox.org/mtg/'. $card_name .
                        '">' . $card_name . '</a><br />';
                    $current_body .= $line;
                    $current_count += intval($bits[1]);
                } else {
                    // Beginning of a new category. If this was not the first one, we put the previous one
                    // into the response body.
                    if ($current_title != "") {
                        $html .= '<span style="font-weight:bold">' . $current_title . ' (' .
                            $current_count . ')</span><br />';
                        $html .= $current_body;
                        if (preg_match("/Sideboard/", $line) && !$second_column) {
                            $html .= '</td><td>';
                            $second_column = true;
                        } else if (preg_match("/Lands/", $line) && !$second_column) {
                            $html .= '</td><td>';
                            $second_column = true;
                        } else {
                            $html .= '<br />';
                        }
                    }
                    $current_title = $line; $current_count = 0; $current_body = '';
                }
            }
            $html .= '<span style="font-weight:bold">' . $current_title . ' (' . $current_count .
                ')</span><br />' . $current_body;

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

