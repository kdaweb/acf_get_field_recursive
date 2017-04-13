<?php
if (! class_exists (acf_get_field_recursive_plugin)) {
  class acf_get_field_recursive_plugin {
// Add Shortcode
public static function get_field_recursive( $atts ) {

  // Attributes
  $atts = shortcode_atts(
    array(
      'field' => '',
    ),
    $atts,
    'get_field_recursive'
  );

  return get_field_recursive_worker ($atts['field'], null);
  
  /* 
   * Get ACF fields recursivly
   * (if post does not have value look upward until found)
   */
  
  private static function get_field_recursive_worker ( $field, $level_id = null ) {
    // If no ID is passed set to post ID
    $level_id = $level_id == null ? get_queried_object_id() : $level_id;
    $return_value = get_field($field, $level_id);
    $ancestors = get_post_ancestors($level_id);
  
    if (trim ($field) == '') {
        // return false if no field was supplied
        return false;
    } else if ( $return_value ) {
      // Return field value when found
      return $return_value;
    } else if ( $ancestors ) {
      // Get closest ancecstor: http://codex.wordpress.org/Function_Reference/get_post_ancestors
      $level_id = $ancestors[0];
      return get_field_recursive_worker($field, $level_id);
    } else {
      // Return false if value is not found nor have ancestors
      return false;
    }
  }

}
add_shortcode( 'get_field_recursive', 'get_field_recursive' );
}
?>
