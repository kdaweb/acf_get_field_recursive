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
 * @copyright         2017 KDA Web Technologies, Inc.
 * @link              http://kdaweb.com/ KDA Web Technologies, Inc.
 * @author            KDA Web Technologies, Inc. <info@kdaweb.com>
 * @license           http://directory.fsf.org/wiki/License:BSD_3Clause Modified BSD (3-Clause) License
 * @package           acf_get_field_recursive
 * @version           1.0.1
 *
 * @wordpress-plugin
 * Plugin Name:       Recursive ACF shortcode
 * Plugin URI:        http://kdaweb.com/
 * Description:       Adds a recursive shortcode that searches the post and its ancestors
 * Version:           1.0.1
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

/**
 * function to accept attributes from the shortcode and get_field_recursive
 * 
 * This function acquires the attributes sent to the shortcode, unpacks them,
 * and calls get_field_recursive() with the requested field.
 * 
 * @return string the value of the requested field or false if not found
 * @since 1.0.1
 */
function acf_recursive () {
  $attributes = shortcode_atts (
    array (
      'field'        => null,
      'post_id'      => get_queried_object_id(),
      'format_value' => true,
    ),
    $attributes,
    'acf_recursive'
  );
  
  return get_field_recursive ($attributes['field'], 
                              $attributes['post_id'],
                              $attributes['format_value']);
}


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
  // see https://codex.wordpress.org/Function_Reference/get_queried_object
  if ($post_id == null) {
    $post_id = get_queried_object_id();
  }
  
  // call ACF's get_field to get the requested custom field
  // see https://www.advancedcustomfields.com/resources/get_field/
  $return_value = get_field ($field, $post_id, $format_value);

  // if we get back a value, return it
  if ($return_value) {

    return $return_value;

  } else {

    // get the post's closest ancestor (its parent)
    // see http://codex.wordpress.org/Function_Reference/get_post_ancestors
    $ancestors = get_post_ancestors ($post_id);

    if ($ancestors) {

      $post_id = $ancestors[0];
      return get_field_recursive ($field, $post_id, $format_value);

    } else {

      // if value is not found and there are no more ancestors, return false
      return false;
      
    } // end if ($ancestors)
  } // end if ($return_value) } else {
} // end function

// finally, register the shortcode
// see https://codex.wordpress.org/Function_Reference/add_shortcode
add_shortcode('acf_recursive', 'acf_recursive');
?>