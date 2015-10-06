<?php
/*
Plugin Name: Magic the Gathering Card Tooltips
Plugin URI: https://github.com/SebastianZaha/wordpress_mtg_tooltips
Description: Easily transform Magic the Gathering card names into links that show the card
image in a tooltip when hovering over them. You can also quickly create deck listings.
Author: Sebastian Zaha
Version: 3.1.3
Author URI: http://deckbox.org
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
            wp_enqueue_script('deckbox', 'http://deckbox.org/javascripts/tooltip.js');
            wp_enqueue_script('deckbox_extensions', $this->_resources_dir.'tooltip_extension.js', array('jquery'));
            add_action('wp_head', array($this, 'init_css'));
        }

        function parse_mtg_card($atts, $content=null) {
            return '<a class="deckbox_link" target="_blank" href="http://deckbox.org/mtg/' . $content . '">' . $content . '</a>';
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
                        "style" => $this->get_style_name(),
                    ), $atts));

            if ($title) {
                $response = '<h3 class="mtg_deck_title">' . $title . '</h3>';
            }
            $response .= '<table class="mtg_deck mtg_deck_' . $style .
                '" cellspacing="0" cellpadding="0" style="width:' .
                $this->get_setting('deck_width') .'px;font-size:' . $this->get_setting('font_size') .
                '%;line-height:' .$this->get_setting('line_height'). '%"><tr><td>';

            $lines = $this->cleanup_shortcode_content($content);
            $response .= $this->parse_mtg_deck_lines($lines, $style) . '</td>';
            $response .= '</tr></table>';

            return $response;
        }

        function parse_mtg_deck_lines($lines, $style) {
            $current_count = 0;
            $current_title = '';
            $current_body = '';
            $first_card = null;

            for ($i = 0; $i < count($lines); $i++) {
                $line = $lines[$i];

                if (preg_match('/^([0-9]+)(.*)/', $line, $bits)) {
                    $card_name = trim($bits[2]);
                    $first_card = $first_card == null ? $card_name : $first_card;
                    $card_name = str_replace("’", "'", $card_name);
                    $line = $bits[1] . '&nbsp;<a class="deckbox_link" target="_blank" href="http://deckbox.org/mtg/'. $card_name .
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
                        if (preg_match("/Sideboard/", $line)) {
                            $html .= '</td><td>';
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
                $html .= '<td class="card_box"><img class="on_page" src="http://deckbox.org/mtg/' .
                    $first_card . '/tooltip" /></td>';
            }

            return $html;
        }

        function add_option_menu() {
            $title = '';
            if ( version_compare(get_bloginfo('version'), '2.6.999', '>')) {
                $title = '<img src="'.$this->_images_dir.'deckbox_dragon.jpg" alt="deckbox.org" /> ';
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

        function get_style_name() {
            return $this->_styles[$this->get_setting('style') - 1];
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
                    $this->_value['tooltip'][0]['style'] = 0;
                }
                if (empty($this->_value['tooltip'][0]['deck_width'])) {
                    $this->_value['tooltip'][0]['deck_width'] = 510;
                }
                if (empty($this->_value['tooltip'][0]['font_size'])) {
                    $this->_value['tooltip'][0]['font_size'] = 100;
                }
                if (empty($this->_value['tooltip'][0]['line_height'])) {
                    $this->_value['tooltip'][0]['line_height'] = 140;
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
                $v = array();
                $v['tooltip'][] = array('style' => $_POST['tooltip_style'],
                                  'deck_width' => $_POST['tooltip_deck_width'],
                                  'font_size' => $_POST['tooltip_font_size'],
                                  'line_height' => $_POST['tooltip_line_height']);
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
