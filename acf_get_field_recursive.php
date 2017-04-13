<?php
/**
 * Adds a recursive version of get_field for use with Advanced Custom Fields (ACF)
 *
 * This plugin adds a shortcode for use with Advanced Custom Fields (ACF) that 
 * works like get_field(); however, if the requested field is empty, the parent
 * of the given post will be evaluated for said field.  If the parent doesn't 
 * have a value, its parent (i.e., the post's ancestors) are each examined until
 * either a value is found or there are no more ancestors to call.  If nothing is
 * found in the post or its ancestors, false is returned.
 *
 * @link              http://kdaweb.com
 * @since             1.0.0
 * @package           acf_get_field_recursive
 *
 * @wordpress-plugin
 * Plugin Name:       ACF get_field recursively
 * Plugin URI:        http://kdaweb.com/
 * Description:       Adds a shortcode, 'get_field_recursive', that recursively calls get_field on the post and its ancestors
 * Version:           1.0.0
 * Author:            KDA Web Technologies, Inc.
 * Author URI:        http://kdaweb.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       acf_get_field_recursive
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined ('WPINC')) {
  die;
}

if (! class_exists (acf_get_field_recursive_plugin)) {
  class acf_get_field_recursive_plugin {
    public static function get_field_recursive ($attributes) {

      $attributes = shortcode_atts (
        array (
          'field' => '',
        ),
        $attributes,
        'get_field_recursive'
      );

      return get_field_recursive_worker ($attributes['field'], null);
    }
  
    add_shortcode('get_field_recursive', 'get_field_recursive');

    /* 
     * Get ACF fields recursivly
     * (if post does not have value look upward until found)
     */
    private static function get_field_recursive_worker ($field, $level_id = null) {
      // If no ID is passed set to post ID
      $level_id = $level_id == null ? get_queried_object_id() : $level_id;
      $return_value = get_field ($field, $level_id);
      $ancestors = get_post_ancestors ($level_id);
  
      if (! trim ($field)) {

        // return false if no field was supplied
        return false;

      } else if ($return_value) {

        // Return field value when found
        return $return_value;

      } else if ($ancestors) {

        // Get closest ancecstor: http://codex.wordpress.org/Function_Reference/get_post_ancestors
        $level_id = $ancestors[0];
        return get_field_recursive_worker ($field, $level_id);

      } else {

        // Return false if value is not found nor have ancestors
        return false;

      }
    } // end function
  } // end class
} // end if (! class_exists)
?>
