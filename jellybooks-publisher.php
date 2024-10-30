<?php
/*

Copyright (c) 2024, Jellybooks Ltd.

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


/**
 * Plugin Name: Jellybooks DISCOVERY for Publishers
 * Description: Jellybooks DISCOVERY Wordpress plugin
 * Version: 1.1.6
 * Author: Jellybooks Ltd.
 * Author URI: https://www.jellybooks.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires PHP: 7.0
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
} 

if (is_admin()) {
    require_once plugin_dir_path( __FILE__ ) . 'includes/jb_discovery_admin.php';
    $jellybooks_discovery_discovery_admin = new JbDiscoveryAdmin();
}

// add hook to post content render
add_filter( 'the_content', 'jellybooks_discovery_pub_display', 999 );

add_action( 'woocommerce_init', 'jellybooks_discovery_woocommerce_init' );

function jellybooks_discovery_woocommerce_init() {
	add_filter( 'woocommerce_single_product_image_thumbnail_html', 'jellybooks_discovery_image_link_external_url', 100, 2 );
	add_filter( 'woocommerce_single_product_zoom_enabled', '__return_false' );
}

 
function jellybooks_discovery_image_link_external_url( $html, $post_thumbnail_id ) {

	$jellybooks_discovery_options = get_option( 'jellybooks_discovery_excerpt_options' );
	$api_key = jellybooks_discovery_options_set($jellybooks_discovery_options, 'api_key_0');
	$segments_1 = jellybooks_discovery_options_set($jellybooks_discovery_options, 'segments_1');
	$label_text = jellybooks_discovery_options_set($jellybooks_discovery_options, 'excerpts_label_4');
	$vertical_placement = jellybooks_discovery_options_set($jellybooks_discovery_options, 'vertical_placement_5');
	$horizontal_placement = jellybooks_discovery_options_set($jellybooks_discovery_options, 'horizontal_placement_6');
	$label_visual_style = jellybooks_discovery_options_set($jellybooks_discovery_options, 'label_style');
	
	if (empty($api_key)) {
		return $html;
	}
	$allowed_segments = array();

	if (!empty($segments_1)) {
		// segments option is stored as a string, so split into array
		$allowed_segments = explode(",", $segments_1);
	}
	if (empty($allowed_segments) || jellybooks_discovery_is_segment_present($allowed_segments)) {
		global $product;
		$isbn = $product->get_sku();

		$excerpt_url = jellybooks_discovery_get_excerpt_url($isbn, $api_key);

		if (empty($excerpt_url)) {
			return $html;
		}

		jellybooks_discovery_add_assets($label_text, $vertical_placement, $horizontal_placement, $label_visual_style);
		
		$pattern = "/(?<=href=(\"|'))[^\"']+(?=(\"|'))/";
		$html = preg_replace( $pattern, $excerpt_url, $html );
		$anchorpattern = "/\<a\ /";
		$html = preg_replace( $anchorpattern, '<a data-jb-modal="true" ', $html );
		return $html;
	} else {
		return $html;
	}
}

function jellybooks_discovery_options_set ($jellybooks_discovery_options, $fieldname) {
	if (isset($jellybooks_discovery_options["jellybooks_discovery_" . $fieldname])) {
    return $jellybooks_discovery_options["jellybooks_discovery_" . $fieldname];
	} else if (isset($jellybooks_discovery_options[$fieldname])) {
			return $jellybooks_discovery_options[$fieldname];
	} else {
			return "";
	}
}

function jellybooks_discovery_pub_display($content) {

    if (!is_main_query() || !in_the_loop() || !is_singular()) {
        return $content;
    }

	// Shouldn't happen, but possible an upstream plugin has already modified the content to an 
	// unprocessable state 
	if (empty($content)) {
		return $content;
	}

	$jellybooks_discovery_options = get_option( 'jellybooks_discovery_excerpt_options' );
	$api_key = jellybooks_discovery_options_set($jellybooks_discovery_options, 'api_key_0');
	$segments_1 = jellybooks_discovery_options_set($jellybooks_discovery_options, 'segments_1');
	$cover_image_xpath_2 = jellybooks_discovery_options_set($jellybooks_discovery_options, 'cover_image_xpath_2');
	$isbn_xpath_3 = jellybooks_discovery_options_set($jellybooks_discovery_options, 'isbn_xpath_3');
	$label_text = jellybooks_discovery_options_set($jellybooks_discovery_options, 'excerpts_label_4');
	$vertical_placement = jellybooks_discovery_options_set($jellybooks_discovery_options, 'vertical_placement_5');
	$horizontal_placement = jellybooks_discovery_options_set($jellybooks_discovery_options, 'horizontal_placement_6');
	$label_visual_style = jellybooks_discovery_options_set($jellybooks_discovery_options, 'label_style');
	$debug_enabled = jellybooks_discovery_options_set($jellybooks_discovery_options, 'debug_7');

	// Bail out if no API key is set
	if (empty($api_key)) {
		return $content;
	}

	$allowed_segments = array();

	if (!empty($segments_1)) {
		// segments option is stored as a string, so split into array
		$allowed_segments = explode(",", $segments_1);
	}
    
    // if no segments are specified, then allow all
    if (empty($allowed_segments) || jellybooks_discovery_is_segment_present($allowed_segments)) {
		$doc = jellybooks_discovery_load_doc($content);

		if (empty($doc)) {
			return $content . "<!-- Jellybooks Discovery Plugin: Empty Document -->" . "<template id='jellybooks-content-before-processing'><!--" . $content . "--></template>";
		}

		$isbn_node_string = jellybooks_discovery_find_isbn_node($doc, $isbn_xpath_3);
		$isbn = jellybooks_discovery_extract_isbn13($isbn_node_string);

		if (empty($isbn)) {
			return $content . "<!-- Jellybooks Discovery Plugin: No ISBN found -->" . "<template id='jellybooks-content-before-processing'><!--" . $content . "--></template>";
		}

		// search for cover image
		$node = jellybooks_discovery_find_element($doc, $cover_image_xpath_2);

		if (empty($node)) {
			return $content . "<!-- Jellybooks Discovery Plugin: No cover image found -->" . "<template id='jellybooks-content-before-processing'><!--" . $content . "--></template>";
		}

		$excerpt_url = jellybooks_discovery_get_excerpt_url($isbn, $api_key);

		if (empty($excerpt_url)) {
			return $content . "<!-- Jellybooks Discovery Plugin: No excerpt URL found -->" . "<template id='jellybooks-content-before-processing'><!--" . $content . "--></template>";
		}

		jellybooks_discovery_add_assets($label_text, $vertical_placement, $horizontal_placement, $label_visual_style); // Add the JS script from assets dir to the page
		
		jellybooks_discovery_update_document($node, $excerpt_url);

		if ($debug_enabled) {
			$processed_content = $doc->saveHTML();
			return $processed_content . "<template id='jellybooks-content-before-processing'><!--" . $content . "--></template>" . "<template id='jellybooks-content-after-processing'><!--" . $processed_content . "--></template>";
		} else {
			return $doc->saveHTML();
		}
    }

    return $content;
}

function jellybooks_discovery_extract_isbn13($node_string) {
	// remove dashes
	$node_string = str_replace("-", "", $node_string);

	// remove spaces
	$node_string = str_replace(" ", "", $node_string);

	// look for 13 digit ISBN
	if (preg_match('/\d{13}/', $node_string, $matches)) {
		return $matches[0];
	}

	return "";
}

function jellybooks_discovery_add_assets($label_text, $vertical_placement, $horizontal_placement, $label_visual_style) {
	wp_enqueue_script( 'jb-excerpts-script', plugin_dir_url( __FILE__ ) . 'assets/excerpts-1.1.0.min.js', array(), '1.1.0', true );
	wp_enqueue_style( 'jb-excerpts-style', plugin_dir_url( __FILE__ ) . 'assets/excerpts-1.1.0.css', array(), '1.1.0', 'all' );
		$selector = $label_visual_style == "none" ? '' : '    selector: "[data-jb-modal] img",';
	$message = 'try{excerpts.init(' . PHP_EOL;
	$message .= '{ label: {' . PHP_EOL;
	$message .= $selector . PHP_EOL;
	$message .= '    style: "' . $label_visual_style .'",' . PHP_EOL;
	$message .= '    placement: ' . PHP_EOL;
	$message .= '      { ' . PHP_EOL;
	$message .= '        x: "' . $horizontal_placement . '",' . PHP_EOL;
	$message .= '        y: "' . $vertical_placement .'",' . PHP_EOL;
	$message .= '      },' . PHP_EOL;
	$message .= '      text: "' . $label_text . '"' . PHP_EOL;
	$message .= '    } });}catch(e) {}' . PHP_EOL;

	wp_add_inline_script( 'jb-excerpts-script', $message );
}

function jellybooks_discovery_is_segment_present($allowed_segments) {
    $url = sanitize_url( $_SERVER['REQUEST_URI'] );

    $segments = explode('/', wp_parse_url($url, PHP_URL_PATH));
    // $segments now contains an array of URL segments
    if (jellybooks_discovery_array_contains_any($allowed_segments, $segments)) {
        return true;
    }
    return false;
}

function jellybooks_discovery_handle_encoding($content) {
	/* I've tried various methods to encode the content to UTF-8, but modern PHP doesn't like
	utf8_encode, and mb_convert_encoding doesn't seem to work as expected because it no longer
	accepts the 'HTML-ENTITIES' encoding. So I've resorted to convincing the HTML parser that
	to use the correct encoding by setting the charset in the declarion tag. */

	$charset = get_bloginfo('charset'); // Actually, don't assume UTF-8.
	$converted_content = '<?xml encoding="' . $charset . '" ?>' . $content;
	return $converted_content;
}

function jellybooks_discovery_load_doc($content) {
	$converted_content = jellybooks_discovery_handle_encoding($content);

	$doc = new DOMDocument();
	$doc->loadHTML($converted_content, LIBXML_NOERROR | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

	return $doc;
}

function jellybooks_discovery_find_isbn_node($doc, $xpath_query, $attribute="") {
	$xpath = new DOMXpath($doc);

	$result = $xpath->query($xpath_query);

	// return first result
	foreach ($result as $node) {
		if ($attribute == "") {
			return $node->nodeValue;
		} else {
			return $node->getAttribute($attribute);
		}
	}

	return "";
}

function jellybooks_discovery_find_element($doc, $xpath_query) {
	$xpath = new DOMXpath($doc);
	$result = $xpath->query($xpath_query);

	// return first result
	foreach ($result as $node) {
		return $node;
	}

	return NULL;
}

function jellybooks_discovery_wrap_node($node, $tag_name) {
	$wrapper = $node->ownerDocument->createElement($tag_name);
	$node->parentNode->insertBefore($wrapper, $node);
	$wrapper->appendChild($node);

	return $wrapper;
}

function jellybooks_discovery_update_document($node, $excerpt_url) {
	// get current node tag name
	$tag_name = $node->tagName;
	if ($tag_name != "a") {
		// if node is not an anchor, then wrap it in an anchor tag
		$node = jellybooks_discovery_wrap_node($node, "a");
	}

	// add data attribute to node
	$node->setAttribute("data-jb-modal", "true");
	$node->setAttribute("href", $excerpt_url);
}

function jellybooks_discovery_get_excerpt_url($isbn, $api_key) {
	// send HTTP GET request to Jellybooks API
	$excerpt_url = "https://www.jellybooks.com/discovery/api/excerpts/" . $isbn;

	// append utm_source to url
	$excerpt_url = add_query_arg( 'utm_source', 'wp_jellybooks_discovery_ex_lu', $excerpt_url );

	$args = array(
    'headers' => array(
        'Content-Type' => 'application/json',
        'JB-Discovery-Api-Key' => $api_key
    )
	);

	$response = wp_remote_get( $excerpt_url, $args );

	$http_status_code = wp_remote_retrieve_response_code( $response );
	$body     = wp_remote_retrieve_body( $response );
	
	switch($http_status_code) {
		case 200:
			$excerpt_payload = json_decode($body, true);
			// get excerpt key
			$excerpt_item = $excerpt_payload['excerpt'];
			$excerpt_url = sanitize_url($excerpt_item['url']);
			break;
		default:
			$excerpt_url = "";
	}

	return $excerpt_url;
}

// check if array contains any item from another array
function jellybooks_discovery_array_contains_any($needles, $haystack) {
    return count(array_intersect($needles, $haystack)) > 0;
}


