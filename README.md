# acf_get_field_recursive

Adds 'acf_recursive' shortcode for use with Advanced Custom Fields (ACF)

This plugin adds a shortcode for use with Advanced Custom Fields (ACF) that 
works like the 'acf' shortcode (e.g., calling get_field()); however, if the
requested field is empty, the parent of the given post will be examined for 
said field.  If the parent doesn't have a value, its parent (i.e., the 
post's ancestors) are each examined until either a value is found or there 
are no more ancestors to call.  If nothing is found in the post or any of 
its ancestors, false is returned.

This was originally based on Jacob Rudenstam's Github Gist posting 
"get-field-recursive.php" at https://gist.github.com/jrudenstam/7551729