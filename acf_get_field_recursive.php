<?php
/**
 * Adds 'acf_recursive' shortcode for use with Advanced Custom Fields (ACF)
 *
 * This plugin adds a shortcode for use with Advanced Custom Fields (ACF) that 
 * works like the 'acf' shortcode (e.g., calling get_field()); however, if the
 * requested field is empty, the parent of the given post will be examined for 
 * said field.  If the parent doesn't have a value, its parent (i.e., the 
 * post's ancestors) are each examined until either a value is found or there 
 * are no more ancestors to call.  If nothing is found in the post or any of 
 * its ancestors, false is returned.
 * 
 * This was originally based on Jacob Rudenstam's Github Gist posting 
 * "get-field-recursive.php" at https://gist.github.com/jrudenstam/7551729
 * 
 * Changelog:
 *   v1.0.0: initial release; support for only 'field' attribute
 *   v1.0.1: full get_field compatability; logic and structure massage
 *   v1.0.2: added Plugin Update Checker (PUC) functionality
 *   v1.0.3: bug fixes
 *   v1.0.4: bug fixes
 *   v1.0.5: inclusion of test content, particularly for Travis CI
 *
 * @copyright         2017 KDA Web Technologies, Inc.
 * @link              http://kdaweb.com/ KDA Web Technologies, Inc.
 * @author            KDA Web Technologies, Inc. <info@kdaweb.com>
 * @license           http://directory.fsf.org/wiki/License:BSD_3Clause Modified BSD (3-Clause) License
 * @package           acf_get_field_recursive
 * @version           1.0.5
 *
 * @wordpress-plugin
 * Plugin Name:       Recursive ACF shortcode
 * Plugin URI:        http://kdaweb.com/
 * Description:       Adds a recursive shortcode that searches the post and its ancestors
 * Version:           1.0.5
 * Author:            KDA Web Technologies, Inc.
 * Author URI:        http://kdaweb.com/
 * License:           Modified BSD (3-Clause) License
 * License URI:       http://directory.fsf.org/wiki/License:BSD_3Clause
 * Text Domain:       acf_get_field_recursive
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined ('WPINC')) {
  die;
}

// allow updating of plugin from Github
// see: https://github.com/YahnisElsts/plugin-update-checker#github-integration
require 'plugin-update-checker/plugin-update-checker.php';

$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker (
	'https://github.com/kdaweb/acf_get_field_recursive/',
	__FILE__,
	'acf_get_field_recursive'
);


/**
 * function to accept attributes from the shortcode and get_field_recursive
 * 
 * This function acquires the attributes sent to the shortcode, unpacks them,
 * and calls get_field_recursive() with the requested field.
 * 
 * @param string[] $attributes attributes data structure provided by WordPress
 * @return string the value of the requested field or false if not found
 * @since 1.0.1
 */
function acf_recursive ($attributes) {
  
  // see: https://codex.wordpress.org/Function_Reference/shortcode_atts
  $atts = shortcode_atts (
    array (
      'field'        => null,
      'post_id'      => get_queried_object_id(), // see https://developer.wordpress.org/reference/functions/get_queried_object_id/
      'format_value' => true,
    ),
    $attributes,
    'acf_recursive'
  );
  
  return get_field_recursive ($atts['field'], 
                              $atts['post_id'],
                              $atts['format_value']);
} // end function acf_recursive


/** 
 * search Advanced Custom Fields (ACF) fields recursively and return value
 * 
 * Advanced Custom Fields (ACF) provides a function, get_field(), that accepts 
 * an attribute, 'field', that contains the field for which to search in the 
 * given page.  The ancestors of the post are not examined -- only the requested
 * post.
 * 
 * This function recursively examines the current post (as with get_field()) but
 * also examines the post's ancestors until either a value is found and 
 * returned or there are no more ancestors to examine in which case false is 
 * returned.
 * 
 * @param string $field the field for which to search
 * @param int $post_id the id of the post to search; defaults to current post
 * @param boolean $format_value true (default) to format results; false if not
 * @return string value of the custom field if found; false if not
 * @since 1.0.0
 * 
 */
function get_field_recursive ($field, 
                              $post_id = null, 
                              $format_value = true) {
  
  // if no post_id is provided, get the current post's id
  // see: https://codex.wordpress.org/Function_Reference/get_queried_object
  if ($post_id == null) {
    $post_id = get_queried_object_id();
  }
  
  // call ACF's get_field to get the requested custom field
  // see: https://www.advancedcustomfields.com/resources/get_field/
  $return_value = get_field ($field, $post_id, $format_value);

  // if we get back a value, return it
  if ($return_value) {

    return $return_value;

  } else {

    // get the post's ancestor
    // see: http://codex.wordpress.org/Function_Reference/get_post_ancestors
    $ancestors = get_post_ancestors ($post_id);

    if ($ancestors) {

      $parent_id = $ancestors[0];
      return get_field_recursive ($field, $parent_id, $format_value);

    } else {

      // if value is not found and there are no more ancestors, return false
      return false;
      
    } // end if ($ancestors)
  } // end if ($return_value) } else {
} // end function get_field_recursive


/**
 * verify that Advanced Custom Fields is active
 * 
 * This function verifies that the Advanced Custom Fields plugin is activated
 * and will wp_die otherwise
 * 
 * see: https://wordpress.stackexchange.com/questions/127818/how-to-make-a-plugin-require-another-plugin
 * 
 * @return boolean true if ACF is activate; wp_die otherwise
 * @since 1.0.5
 */
function verify_acf_activated () {

  // see: https://codex.wordpress.org/Function_Reference/is_plugin_active
  // see: https://codex.wordpress.org/Function_Reference/current_user_can
  if ((! is_plugin_active ('advanced-custom-fields/acf.php'))
  && (current_user_can ('activate_plugins' ))) {
    // Stop activation redirect and show error; see: https://codex.wordpress.org/Function_Reference/wp_die
    wp_die ('This plugin requires Advanced Custom Fields to be installed and active. <br /><a href="' . admin_url ('plugins.php') . '">&laquo; Return to Plugins</a>');
  } // end if
  
  return true;
  
} // end function verify_acf_activated

// register the shortcode
// see: https://codex.wordpress.org/Function_Reference/add_shortcode
add_shortcode('acf_recursive', 'acf_recursive');

// call the function to verify ACF activation
// see: https://codex.wordpress.org/Function_Reference/register_activation_hook
register_activation_hook ( __FILE__, 'verify_acf_activated');

?>