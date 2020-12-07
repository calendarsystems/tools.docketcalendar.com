<?php
/** Start the engine */
require_once( TEMPLATEPATH . '/lib/init.php' );

/** Child theme (do not remove) */
define( 'CHILD_THEME_NAME', 'Agency Theme' );
define( 'CHILD_THEME_URL', 'http://www.studiopress.com/themes/agency' );

$content_width = apply_filters( 'content_width', 590, 410, 910 );


remove_action( 'genesis_doctype', 'genesis_do_doctype' );
add_action( 'genesis_doctype', 'genesis_do_custom_doctype' );

function kana_init_session() {
	if( ! session_id() ) {
         ini_set('session.gc_maxlifetime', 10800); 
		session_set_cookie_params(10800);
		session_start();
    }
	
}

add_action('wp', 'kana_init_session', 1);

function genesis_do_custom_doctype() {
	
	$this_template= c2c_reveal_template(false);

    include ('include/inc_top_pages.php');
if ( file_exists('include/inc_top_'.str_replace('calendarrules/','',$this_template)) && ($this_template != 'calendarrules/docket-calculator.php') && ($this_template !='calendarrules/date-calculator.php') && ($this_template != 'calendarrules/docket-research.php') && ($this_template != 'calendarrules/update-docket-calendar.php')) {
	 include ('include/inc_top_'.str_replace('calendarrules/','',$this_template)); 
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes( 'xhtml' ); ?>>
<head>
<meta name="google-site-verification" content="MvYfZt_KYiBkBKwMvXC2gshotyUYyPntQGE7R3IwPAA" />
<meta http-equiv="Content-Type" content="<?php bloginfo( 'html_type' ); ?>; charset=<?php bloginfo( 'charset' ); ?>" />
<!-- before JS include -->
<script src="https://subscribe.docketlaw.com/SpryAssets/SpryValidationCheckbox.js" type="text/javascript"></script>
<script src="https://subscribe.docketlaw.com/SpryAssets/SpryValidationTextField.js" type="text/javascript"></script>
<script src="https://subscribe.docketlaw.com/SpryAssets/SpryValidationSelect.js" type="text/javascript"></script>
<link href="https://subscribe.docketlaw.com/SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css" />
<link href="https://subscribe.docketlaw.com/SpryAssets/SpryValidationSelect.css" rel="stylesheet" type="text/css" />
<link href="https://subscribe.docketlaw.com/SpryAssets/SpryValidationCheckbox.css" rel="stylesheet" type="text/css" />
<link href="https://subscribe.docketlaw.com/jquery/css/ui-lightness/jquery-ui.css" rel="stylesheet" type="text/css" />


<?php
if (file_exists('include/inc_js_head_'.str_replace('calendarrules/','',$this_template)) && ($this_template != 'calendarrules/docket-research.php') ) {
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
    echo ' &middot; <a href="https://calendarrules.com">CalendarRules.com</a>';
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

add_filter('wp_nav_menu_items', 'add_login_logout_link', 10, 2);
function add_login_logout_link($items, $args) {
    if (@$_SESSION['access_token'] == '') {
        $items .= '
		<li id="menu-item-151" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-home menu-item-has-children menu-item-151"><a href="https://tools.docketcalendar.com/">UTILITIES</a>
			<ul class="sub-menu">
				<li id="menu-item-153" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-153"><a href="/event-option/">Preference</a></li>
				<li id="menu-item-154" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-154"><a href="/excludevents/">Exclude Events</a></li>
				<li id="menu-item-164" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-164"><a href="/add-custom-contacts/">Contacts</a></li>

			</ul>
		</li>
		<li id="menu-item-156" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-home menu-item-has-children menu-item-156"><a href="https://tools.docketcalendar.com/">HELP</a>
			<ul class="sub-menu">
				<li id="menu-item-157" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-home menu-item-157"><a href="https://tools.docketcalendar.com/">User Guide</a></li>
				<li id="menu-item-159" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-home menu-item-159"><a href="https://tools.docketcalendar.com/">Knowledgebase</a></li>
				<li id="menu-item-160" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-160"><a href="https://www.calendarrules.com/help-center">Help Center</a></li>
			</ul>
		</li>
		<li id="menu-item-91" class="menu-item menu-item-type-custom menu-item-object-custom">
				<a  href="https://subscribe.docketlaw.com/subscribe?site=GOO" target="_blank">SUBSCRIBE</a>
		</li>
		';
    } else {
        if (isset($_SESSION['access_token']) && $_SESSION['account_exist'] == "Y") {
			
			$kbcalendarrules_url = "http://kb.calendarrules.com";
            $action = $_SESSION['action'];
			$user_email=$_SESSION['email'];
			$user_login = $_SESSION['username'];
			$user_site  = $_SESSION['usersite'];
			$email_encoded = rtrim(strtr(base64_encode($user_email), '+/', '-_'), '=');
			$user_login_encoded = rtrim(strtr(base64_encode($user_login), '+/', '-_'), '='); //username encryption
			$user_site_encoded = rtrim(strtr(base64_encode($user_site), '+/', '-_'), '=');
            $items .= '
			<li id="menu-item-151" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-home menu-item-has-children menu-item-151"><a href="https://tools.docketcalendar.com/">UTILITIES</a>
			<ul class="sub-menu">
				<li id="menu-item-153" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-153"><a href="/event-option/">Preference</a></li>
				<li id="menu-item-154" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-154"><a href="/excludevents/">Exclude Events</a></li>
				<li id="menu-item-164" class="menu-item menu-item-type-post_type menu-item-object-page menu-item-164"><a href="/add-custom-contacts/">Contacts</a></li>
				<li id="menu-item-165" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-home menu-item-165">
							<a target="_blank" href="https://subscribe.docketlaw.com/external/link.php?action=' . $action . '&eml='.$email_encoded.'" target="_blank" ">Manage Account</a></li>
			</ul>
			</li>
			<li id="menu-item-156" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-home menu-item-has-children menu-item-156"><a href="https://tools.docketcalendar.com/">HELP</a>
				<ul class="sub-menu">
					<li id="menu-item-157" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-home menu-item-157"><a href="'.$kbcalendarrules_url.'/sso.php?key='.$email_encoded.'&detail='.$user_login_encoded.'&site='.$user_site_encoded."&guideReqVal=goo".'"  target="_blank">User Guide</a></li>
					<li id="menu-item-159" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-home menu-item-159"><a href="'.$kbcalendarrules_url.'/sso.php?key='.$email_encoded.'&detail='.$user_login_encoded.'&site='.$user_site_encoded.'"  target="_blank">Knowledgebase</a></li>
					<li id="menu-item-160" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-160"><a href="https://www.calendarrules.com/help-center"  target="_blank">Help Center</a></li>
				</ul>
			</li>
			';
        }
    }
    return $items;
}
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
/*
wp_enqueue_style( 'notify', get_template_directory_uri() . '/notify.css' );
wp_enqueue_script('notify', get_template_directory_uri() . '/notify.js');
*/
