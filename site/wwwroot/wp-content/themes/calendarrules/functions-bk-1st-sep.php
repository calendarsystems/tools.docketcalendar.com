<?php
/** Start the engine */
require_once( TEMPLATEPATH . '/lib/init.php' );

/** Child theme (do not remove) */
define( 'CHILD_THEME_NAME', 'Agency Theme' );
define( 'CHILD_THEME_URL', 'http://www.studiopress.com/themes/agency' );

$content_width = apply_filters( 'content_width', 590, 410, 910 );


remove_action( 'genesis_doctype', 'genesis_do_doctype' );
add_action( 'genesis_doctype', 'genesis_do_custom_doctype' );

function genesis_do_custom_doctype() {
	
	$this_template= c2c_reveal_template(false);
//echo $this_template;	

if (file_exists('include/inc_top_'.str_replace('calendarrules/','',$this_template))) {
	include ('include/inc_top_'.str_replace('calendarrules/','',$this_template));
}
	include ('include/inc_top_pages.php');
	
 ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes( 'xhtml' ); ?>>
<head>
<meta http-equiv="Content-Type" content="<?php bloginfo( 'html_type' ); ?>; charset=<?php bloginfo( 'charset' ); ?>" />
<!-- before JS include -->
<script src="SpryAssets/SpryValidationCheckbox.js" type="text/javascript"></script>
<script src="SpryAssets/SpryValidationTextField.js" type="text/javascript"></script>
<script src="SpryAssets/SpryValidationSelect.js" type="text/javascript"></script>
<link href="SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css" />
<link href="SpryAssets/SpryValidationSelect.css" rel="stylesheet" type="text/css" />
<link href="SpryAssets/SpryValidationCheckbox.css" rel="stylesheet" type="text/css" />
<link href="jquery/css/ui-lightness/jquery-ui.css" rel="stylesheet" type="text/css" />


<?php
if (file_exists('include/inc_js_head_'.str_replace('calendarrules/','',$this_template))) {
	include ('include/inc_js_head_'.str_replace('calendarrules/','',$this_template));
}
?>
<!-- after JS include--> <?php //echo session_id();
}


/** Customize the credits */
add_filter('genesis_footer_creds_text', 'custom_footer_creds_text');
function custom_footer_creds_text() {
    echo '<div class="creds"><p>';
    echo 'Copyright &copy; ';
    echo date('Y');
    echo ' &middot; <a href="http://calendarrules.com">CalendarRules.com</a>';
    echo '</p></div>';
}

/** Customize the entire footer */
add_filter( 'genesis_footer_output', 'child_footer_output', 10, 3 );
function child_footer_output( $output, $backtotop_text, $creds_text ) {
    return '';
}

/** Add Viewport meta tag for mobile browsers */
add_action( 'genesis_meta', 'agency_viewport_meta_tag' );
function agency_viewport_meta_tag() {
	echo '<meta name="viewport" content="width=device-width, initial-scale=1.0"/>';
}

/** Create additional color style options */
add_theme_support( 'genesis-style-selector', array( 'agency-green' => 'Green', 'agency-orange' => 'Orange', 'agency-red' => 'Red' ) );

/** Add support for structural wraps */
add_theme_support( 'genesis-structural-wraps', array( 'header', 'nav', 'subnav', 'inner', 'footer-widgets', 'footer' ) );

/** Add new image sizes */
add_image_size( 'home-featured', 280, 100, TRUE );

/** Add support for custom header */
add_theme_support( 'genesis-custom-header', array( 'width' => 960, 'height' => 115 ) );

/** Add product post type support for Geneiss layouts */
add_theme_support( 'genesis-connect-woocommerce' );
add_post_type_support( 'product', 'genesis-layouts' );

/** Set Genesis Responsive Slider defaults */
add_filter( 'genesis_responsive_slider_settings_defaults', 'agency_responsive_slider_defaults' );
function agency_responsive_slider_defaults( $defaults ) {
	$defaults['slideshow_height'] = '300';
	$defaults['slideshow_width'] = '950';
	return $defaults;
}

/** Relocate breadcrumbs */
remove_action( 'genesis_before_loop', 'genesis_do_breadcrumbs' );
add_action( 'genesis_before_content_sidebar_wrap', 'genesis_do_breadcrumbs' );

/** Customize the post info function */
add_filter( 'genesis_post_info', 'post_info_filter' );
function post_info_filter($post_info) {
if (!is_page()) {
    $post_info = '[post_date] by [post_author_posts_link] &middot; [post_comments] [post_edit]';
    return $post_info;
}}

/** Customize the post meta function */
add_filter( 'genesis_post_meta', 'post_meta_filter' );
function post_meta_filter($post_meta) {
if (!is_page()) {
    $post_meta = '[post_categories before="Filed Under: "] &middot; [post_tags before="Tagged: "]';
    return $post_meta;
}}

/** Modify the size of the Gravatar in the author box */
add_filter( 'genesis_author_box_gravatar_size', 'agency_author_box_gravatar_size' );
function agency_author_box_gravatar_size($size) {
    return '78';
}

/** Remove post meta */
remove_action('genesis_after_post_content', 'genesis_post_meta');

/** Remove the post info function */
remove_action( 'genesis_before_post_content', 'genesis_post_info' );

/** Add support for 3-column footer widgets */
add_theme_support( 'genesis-footer-widgets', 3 );

/** Register widget areas */
genesis_register_sidebar( array(
	'id'			=> 'home-welcome',
	'name'			=> __( 'Home Welcome', 'agency' ),
	'description'	=> __( 'This is the welcome section of the homepage.', 'agency' ),
) );
genesis_register_sidebar( array(
	'id'			=> 'home-slider',
	'name'			=> __( 'Home Slider', 'agency' ),
	'description'	=> __( 'This is the slider section of the homepage.', 'agency' ),
) );
genesis_register_sidebar( array(
	'id'			=> 'home-left',
	'name'			=> __( 'Home Left', 'agency' ),
	'description'	=> __( 'This is the left section of the homepage.', 'agency' ),
) );
genesis_register_sidebar( array(
	'id'			=> 'home-middle',
	'name'			=> __( 'Home Middle', 'agency' ),
	'description'	=> __( 'This is the middle section of the homepage.', 'agency' ),
) );
genesis_register_sidebar( array(
	'id'			=> 'home-right',
	'name'			=> __( 'Home Right', 'agency' ),
	'description'	=> __( 'This is the right section of the homepage.', 'agency' ),
) );

//datepicker
add_action( 'wp_print_scripts', 'wp33r01_date_picker' );

function wp33r01_date_picker() {
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script('jquery-ui-core');   
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_script('jquery-ui-tabs');
    wp_enqueue_style('jquery.ui.tabs');
    wp_enqueue_style('jquery.ui.theme');
}

add_action( 'wp_footer', 'wpapi_print_scripts');
function wpapi_print_scripts() {
    ?>


<!-- datepicker -->
 <script>
jQuery(function() {
jQuery( "#tabs" ).tabs();
});
</script>

<script type="text/javascript">
    jQuery(document).ready(function() {
	jQuery('#datepicker,#datepicker2').datepicker({dateFormat: 'mm/dd/yy'});
    });
	jQuery('#datepicker,#datepicker2').attr( 'readOnly' , 'true' );
</script>	

<!-- list display toggle for tools / courts -->

<script type="text/javascript">
    jQuery('ul.triggersnav').delegate('a', 'click', function(event) {
    	jQuery(this).parent().children('ul').slideToggle(250);
    	jQuery(this).parent().children('a').toggleClass("arrowdown");
	 }) 
		
	jQuery('ul.triggersnav').find('ul').hide();
</script>



<!-- hide and show account menu item if logged in-->


    <?php
	
}

