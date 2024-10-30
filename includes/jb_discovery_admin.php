<?php
/*

Copyright (c) 2023, Jellybooks Ltd.

Jellybooks DISCOVERY for Publishers Plugin is free software: you can
redistribute it and/or modify it under the terms of the GNU General Public
License as published by the Free Software Foundation, either version 2 of the
License, or any later version.

Jellybooks DISCOVERY for Publishers Plugin is distributed in the hope that it
will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General 
Public License for more details.

You should have received a copy of the GNU General Public License
along with Jellybooks DISCOVERY for Publishers Plugin. If not, see
https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt.

*/

// Prevent direct script access.
if (!defined('ABSPATH')) {
	exit;
}

class JbDiscoveryAdmin
{
	private $jellybooks_discovery_options;

	public function __construct()
	{
		add_action('admin_menu', array($this, 'jellybooks_discovery_add_plugin_page'));
		add_action('admin_init', array($this, 'jellybooks_discovery_page_init'));
	}

	public function jellybooks_discovery_add_plugin_page()
	{
		add_menu_page(
			'Jellybooks DISCOVERY', // page_title
			'Jellybooks DISCOVERY', // menu_title
			'manage_options', // capability
			'jellybooks-discovery', // menu_slug
			array($this, 'jellybooks_discovery_create_admin_page'), // function
			'dashicons-cloud', // icon_url
			65 // position
		);
	}

	public function jellybooks_discovery_page_init()
	{
		register_setting(
			'jellybooks_discovery_option_group', // option_group
			'jellybooks_discovery_excerpt_options', // option_name
			array($this, 'jellybooks_discovery_sanitize') // sanitize_callback
		);

		add_settings_section(
			'jellybooks_discovery_setting_section', // id
			'Settings', // title
			array($this, 'jellybooks_discovery_section_info'), // callback
			'jellybooks-discovery-admin' // page
		);

		add_settings_field(
			'jellybooks_discovery_api_key_0', // id
			'API Key', // title
			array($this, 'api_key_0_callback'), // callback
			'jellybooks-discovery-admin', // page
			'jellybooks_discovery_setting_section' // section
		);

		add_settings_field(
			'jellybooks_discovery_segments_1', // id
			'Segments', // title
			array($this, 'segments_1_callback'), // callback
			'jellybooks-discovery-admin', // page
			'jellybooks_discovery_setting_section' // section
		);

		add_settings_field(
			'jellybooks_discovery_cover_image_xpath_2', // id
			'Cover image Xpath', // title
			array($this, 'cover_image_xpath_2_callback'), // callback
			'jellybooks-discovery-admin', // page
			'jellybooks_discovery_setting_section' // section
		);
		
		add_settings_field(
			'jellybooks_discovery_isbn_xpath_3', // id
			'ISBN Xpath', // title
			array($this, 'isbn_xpath_3_callback'), // callback
			'jellybooks-discovery-admin', // page
			'jellybooks_discovery_setting_section' // section
		);
		
		add_settings_field(
			'jellybooks_discovery_label_style', // id
			'Label Style', // title
			array($this, 'label_style_callback'), // callback
			'jellybooks-discovery-admin', // page
			'jellybooks_discovery_setting_section' // section
		);
		
		add_settings_field(
			'jellybooks_discovery_excerpts_label_4', // id
			'Label Text', // title
			array($this, 'excerpts_label_4_callback'), // callback
			'jellybooks-discovery-admin', // page
			'jellybooks_discovery_setting_section' // section
		);
		
		add_settings_field(
			'jellybooks_discovery_vertical_placement_5', // id
			'Vertical Label Placement', // title
			array($this, 'vertical_placement_5_callback'), // callback
			'jellybooks-discovery-admin', // page
			'jellybooks_discovery_setting_section' // section
		);
		
		add_settings_field(
			'jellybooks_discovery_horizontal_placement_6', // id
			'Horizontal Label Placement', // title
			array($this, 'horizontal_placement_6_callback'), // callback
			'jellybooks-discovery-admin', // page
			'jellybooks_discovery_setting_section' // section
		);
		
		add_settings_field(
			'jellybooks_discovery_debug_7', // id
			'Enable Debug Mode', // title
			array($this, 'debug_7_callback'), // callback
			'jellybooks-discovery-admin', // page
			'jellybooks_discovery_setting_section' // section
		);
	}

	public function api_key_0_callback()
	{
		printf(
			'<input class="regular-text" type="text" name="jellybooks_discovery_excerpt_options[jellybooks_discovery_api_key_0]" id="jellybooks_discovery_api_key_0" value="%s">',
			esc_attr($this->jellybooks_discovery_isset('api_key_0'))
		);

		echo '<p class="description">Copy the API key provided to you by Jellybooks.</p>';
	}

	public function segments_1_callback()
	{
		printf(
			'<input class="regular-text" type="text" name="jellybooks_discovery_excerpt_options[jellybooks_discovery_segments_1]" id="jellybooks_discovery_segments_1" value="%s">',
			esc_attr($this->jellybooks_discovery_isset('segments_1'))
		);
		echo '<p class="description">Comma separated list of URL segments; plugin only activates on pages containing a segment within the URL.</p>';
	}

	public function cover_image_xpath_2_callback()
	{
		printf(
			'<input class="regular-text" type="text" name="jellybooks_discovery_excerpt_options[jellybooks_discovery_cover_image_xpath_2]" id="jellybooks_discovery_cover_image_xpath_2" value="%s">',
			esc_attr($this->jellybooks_discovery_isset('cover_image_xpath_2'))
		);
		echo '<p class="description">Xpath to the cover image on your page, e.g. <code>//div[@class=\'cover_image\']</code>. Note: many covers are clickable via an anchor (&lt;a&gt;) tag that wraps the image in order to present a larger version of the cover. In these cases, ensure the Xpath points to the anchor tag. <strong>(Optional for WooCommerce sites.)</strong></p>';
	}

	public function isbn_xpath_3_callback()
	{
		printf(
			'<input class="regular-text" type="text" name="jellybooks_discovery_excerpt_options[jellybooks_discovery_isbn_xpath_3]" id="jellybooks_discovery_isbn_xpath_3" value="%s">',
			esc_attr($this->jellybooks_discovery_isset('isbn_xpath_3'))
		);
		echo '<p class="description">Xpath to the node containing the ISBN13 text on your page, e.g. <code>//span[@class=\'isbn13\']</code> <strong>(Optional for WooCommerce sites.)</strong></p>';
	}

	public function excerpts_label_4_callback()
	{
		printf(
			'<input class="regular-text" type="text" name="jellybooks_discovery_excerpt_options[jellybooks_discovery_excerpts_label_4]" id="jellybooks_discovery_excerpts_label_4" value="%s">',
			esc_attr($this->jellybooks_discovery_isset('excerpts_label_4'))
		);

		echo '<p class="description">The text you want to use for the label.</p>';
	}

	public function vertical_placement_5_callback()
	{
		$allowed_html = array('select' => array( 'name' => array(),
'id' => array() ), 'option' => array( 'value' => true, 'selected' => true ));
		$selected = esc_attr($this->jellybooks_discovery_isset('vertical_placement_5'));
		$element = '<select name="jellybooks_discovery_excerpt_options[jellybooks_discovery_vertical_placement_5]" id="jellybooks_discovery_vertical_placement_5">';
		$element .= '<option value="top"';
		$element .= $selected == 'top' ? ' selected' : '';
		$element .= '>Top</option>';
		$element .= '<option value="top-outside"';
		$element .= $selected == 'top-outside' ? ' selected' : '';
		$element .= '>Top Outside</option>';
		$element .= '<option value="top-edge"';
		$element .=  $selected == 'top-edge' ? ' selected' : '';
		$element .=  '>Top Edge</option>';
		$element .= '<option value="vertical-center"';
		$element .=  $selected == 'vertical-center' ? ' selected' : '';
		$element .=  '>Middle</option>';
		$element .= '<option value="bottom"';
		$element .=  $selected == 'bottom' ? ' selected' : '';
		$element .=  '>Bottom</option>';
		$element .= '<option value="bottom-outside"';
		$element .=  $selected == 'bottom-outside' ? ' selected' : '';
		$element .=  '>Bottom Outside</option>';
		$element .= '<option value="bottom-edge"';
		$element .=  $selected == 'bottom-edge' ? ' selected' : '';
		$element .=  '>Bottom Edge</option>';
		$element .=  '</select>';
		printf(wp_kses($element , $allowed_html));

		echo '<p class="description">The vertical positioning of the label on the cover.</p>';
	}

	public function horizontal_placement_6_callback()
	{
		$allowed_html = array('select' => array( 'name' => array(),
'id' => array() ), 'option' => array( 'value' => true, 'selected' => true ));
		$selected = esc_attr($this->jellybooks_discovery_isset('horizontal_placement_6'));
		$element = '<select name="jellybooks_discovery_excerpt_options[jellybooks_discovery_horizontal_placement_6]" id="jellybooks_discovery_horizontal_placement_6">';
		$element .= '<option value="left"';
		$element .= $selected == 'left' ? ' selected="selected"' : '';
		$element .= '>Left</option>';
		$element .= '<option value="left-outside"';
		$element .= $selected == 'left-outside' ? ' selected="selected"' : '';
		$element .= '>Left Outside</option>';
		$element .= '<option value="left-edge"';
		$element .=  $selected == 'left-edge' ? ' selected="selected"' : '';
		$element .=  '>Left Edge</option>';
		$element .= '<option value="center"';
		$element .=  $selected == 'center' ? ' selected="selected"' : '';
		$element .=  '>Center</option>';
		$element .= '<option value="right"';
		$element .=  $selected == 'right' ? ' selected="selected"' : '';
		$element .=  '>Right</option>';
		$element .= '<option value="right-outside"';
		$element .=  $selected == 'right-outside' ? ' selected="selected"' : '';
		$element .=  '>Right Outside</option>';
		$element .= '<option value="right-edge"';
		$element .=  $selected == 'right-edge' ? ' selected="selected"' : '';
		$element .=  '>Right Edge</option>';
		$element .=  '</select>';
		printf(wp_kses($element , $allowed_html));

		printf( '<p class="description">The horizontal positioning of the label on the cover</p>');
	}

	public function debug_7_callback()
	{
		printf(
			'<input class="regular-text" type="checkbox" name="jellybooks_discovery_excerpt_options[jellybooks_discovery_debug_7]" id="jellybooks_discovery_debug_7_callback" value="debug_enabled" %s>',
			esc_attr($this->jellybooks_discovery_isset('debug_7') == "debug_enabled" ? "checked" : "")
		);
		echo '<p class="description">Turn on to include debug HTML in the page.</p>';
	}




	public function label_style_callback()
	{
		$allowed_html = array('label' => array( 'for' => array(),
'id' => array() ), 'input' => array( 'value' => true,'type' => true, 'id' => true, 'name' => true, 'checked' => true ), 'p' => array( 'id' => array()), 'div' => array( 'id' => array()));
		$selected = esc_attr($this->jellybooks_discovery_isset('label_style'));
		$element = '<div>';
		
		$element .= '<p><label for="labelStyleChoice1">None</label> <input type="radio" id="labelStyleChoice1" name="jellybooks_discovery_excerpt_options[jellybooks_discovery_label_style]" value="none"';
		$element .= $selected == 'none' ? ' checked' : '';
		$element .= ' /></p>';
		
		$element .= '<p><label for="labelStyleChoice2">Button</label> <input type="radio" id="labelStyleChoice2" name="jellybooks_discovery_excerpt_options[jellybooks_discovery_label_style]" value="button" ';
		$element .= $selected == 'button' ? ' checked' : '';
		$element .= ' /></p>';
		
		$element .= '<p><label for="labelStyleChoice3">Sticker</label> <input type="radio" id="labelStyleChoice3" name="jellybooks_discovery_excerpt_options[jellybooks_discovery_label_style]" value="sticker" ';
		$element .= $selected == 'sticker' ? ' checked' : '';
		$element .= ' /></p></div>';
		printf(wp_kses($element , $allowed_html));
	}

	public function jellybooks_discovery_create_admin_page()
	{
		$this->jellybooks_discovery_options = get_option('jellybooks_discovery_excerpt_options');
		 ?>

		<div class="wrap">
			<h2>Jellybooks DISCOVERY</h2>
			<p><em><strong>This plugin is still in beta.</strong>. Please <a href="https://www.jellybooks.com/contact_messages/new">contact Jellybooks</a> if you encounter any problems.</em></p>

			<p>The purpose of this plugin is to enhance your site's existing book product pages by integrating the Jellybooks DISCOVERY excerpt popup so that customers can preview a book before they buy.</p>

			<h3>Experimental WooCommerce Support</h3>

			<p>With our experimental WooCommerce support, the plugin will use built-in WooCommerce APIs to discover the ISBN and modify the product image.</p>

			<p>This should not require any configuration and for most WooCommerce sites the settings below are entirely optional.</p>

			<p>WooCommerce requirements for Jellybooks DISCOVERY: </p>

			<ol>
				<li>The ISBN <strong>must</strong> be saved as the book product's <em>SKU</em>.</li>
				<li>Product image zoom conflicts directly with this plugin's preview modal. Activating this plugin will disable the theme's product zoom if it hasn't already.</li>
	</ol>

			<h3>Wordpress Support</h3>

			<p>This plugin works on three assuptions:</p>
			<ol>
				<li>Clicking the book's cover image will trigger the popup.</li>
				<li>Each product page contains the book's IBSN13.</li>
				<li>Product pages can be distinguished from other pages containing book cover images.</li>
			</ol>

			<p>Each site will have a different way of laying out and constructing their webpages. In order for this plugin to be flexible enough to work on <em>any</em> page no matter the HTML, we require a small set of configuration settings to target the relevant pieces of the page. These settings are highly technical so please ask for advice from Jellybooks if unsure.</p>
			
			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
				settings_fields('jellybooks_discovery_option_group');
				do_settings_sections('jellybooks-discovery-admin');
				submit_button();
				?>
			</form>
		</div>
<?php
	}

	public function jellybooks_discovery_section_info()
	{
		
		echo "<p>Enter your settings below.</p>";
	}
		
	private function jellybooks_discovery_isset($field_name) {
		if (isset($this->jellybooks_discovery_options[$field_name])) {
			$set_value = $this->jellybooks_discovery_options[$field_name];
		} else if (isset($this->jellybooks_discovery_options["jellybooks_discovery_" . $field_name])) {
			$set_value = $this->jellybooks_discovery_options["jellybooks_discovery_" . $field_name];
		} else {
			$set_value = "";
		}
		return $set_value;
	}

	private function jellybooks_discovery_sanitize($input)
	{
		$sanitary_values = array();
		if (isset($input['api_key_0'])) {
			$sanitary_values['api_key_0'] = sanitize_text_field($input['api_key_0']);
		}

		if (isset($input['segments_1'])) {
			$sanitary_values['segments_1'] = sanitize_text_field($input['segments_1']);
		}

		if (isset($input['cover_image_xpath_2'])) {
			$sanitary_values['cover_image_xpath_2'] = sanitize_text_field($input['cover_image_xpath_2']);
		}

		if (isset($input['isbn_xpath_3'])) {
			$sanitary_values['isbn_xpath_3'] = sanitize_text_field($input['isbn_xpath_3']);
		}



		if (isset($input['excerpts_label_4'])) {
			$sanitary_values['excerpts_label_4'] = sanitize_text_field($input['excerpts_label_4']);
		}

		if (isset($input['vertical_placement_5'])) {
			$sanitary_values['vertical_placement_5'] = sanitize_text_field($input['vertical_placement_5']);
		}

		if (isset($input['horizontal_placement_6'])) {
			$sanitary_values['horizontal_placement_6'] = sanitize_text_field($input['horizontal_placement_6']);
		}

		return $sanitary_values;
	}
}

