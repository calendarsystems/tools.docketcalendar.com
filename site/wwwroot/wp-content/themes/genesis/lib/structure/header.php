<?php
/**
 * Genesis Framework.
 *
 * WARNING: This file is part of the core Genesis Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package Genesis\Header
 * @author  StudioPress
 * @license GPL-2.0+
 * @link    http://my.studiopress.com/themes/genesis/
 */

add_action( 'genesis_doctype', 'genesis_do_doctype' );
/**
 * Echo the doctype and opening markup.
 *
 * If you are going to replace the doctype with a custom one, you must remember to include the opening <html> and
 * <head> elements too, along with the proper attributes.
 *
 * It would be beneficial to also include the <meta> tag for content type.
 *
 * The default doctype is XHTML v1.0 Transitional, unless HTML support os present in the child theme.
 *
 * @since 1.3.0
 */
function genesis_do_doctype() {

	if ( genesis_html5() ) {
		genesis_html5_doctype();
	} else {
		genesis_xhtml_doctype();
	}

}

/**
 * XHTML 1.0 Transitional doctype markup.
 *
 * @since 2.0.0
 */
function genesis_xhtml_doctype() {

	?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes( 'xhtml' ); ?>>
<head profile="http://gmpg.org/xfn/11">
<meta http-equiv="Content-Type" content="<?php bloginfo( 'html_type' ); ?>; charset=<?php bloginfo( 'charset' ); ?>" />
<?php

}

/**
 * HTML5 doctype markup.
 *
 * @since 2.0.0
 */
function genesis_html5_doctype() {

	?><!DOCTYPE html>
<html <?php language_attributes( 'html' ); ?>>
<head <?php echo genesis_attr( 'head' ); ?>>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<?php

}

add_action( 'genesis_title', 'genesis_do_title' );
/**
 * Output the title, wrapped in title tags.
 *
 * @since 2.1.0
 */
function genesis_do_title() {

	if ( get_theme_support( 'title-tag' ) ) {
		return;
	}

	echo '<title>';
	wp_title( '' );
	echo '</title>';

}

add_filter( 'wp_title', 'genesis_default_title', 10, 3 );
/**
 * Return filtered post title.
 *
 * This function does 3 things:
 *  1. Pulls the values for `$sep` and `$seplocation`, uses defaults if necessary.
 *  2. Determines if the site title should be appended.
 *  3. Allows the user to set a custom title on a per-page or per-post basis.
 *
 * @since 0.1.3
 *
 * @global WP_Query $wp_query Query object.
 *
 * @param string $title       Existing page title.
 * @param string $sep         Optional. Separator character(s). Default is `–` if not set.
 * @param string $seplocation Optional. Separator location - "left" or "right". Default is "right" if not set.
 * @return string Page title, formatted depending on context.
 */
function genesis_default_title( $title, $sep = '&raquo;', $seplocation = '' ) {

	global $wp_query;
	$post_id = null;

	if ( is_feed() ) {
		return $title;
	}

	$sep = genesis_get_seo_option( 'doctitle_sep' ) ? genesis_get_seo_option( 'doctitle_sep' ) : '–';
	$seplocation = genesis_get_seo_option( 'doctitle_seplocation' ) ? genesis_get_seo_option( 'doctitle_seplocation' ) : 'right';

	// If viewing the root page.
	if ( genesis_is_root_page() ) {
		// Determine the doctitle.
		$title = genesis_get_seo_option( 'home_doctitle' ) ? genesis_get_seo_option( 'home_doctitle' ) : get_bloginfo( 'name' );

		// Append site description, if necessary.
		$title = genesis_get_seo_option( 'append_description_home' ) ? $title . " $sep " . get_bloginfo( 'description' ) : $title;
	}

	// When the page is set as the Posts Page in WordPress core, use the $post_id of the page when loading SEO values.
	if ( is_home() && get_option( 'page_for_posts' ) && get_queried_object_id() ) {
		$post_id = get_option( 'page_for_posts' );
	}

	// if viewing a post / page / attachment.
	if ( null !== $post_id || is_singular() ) {
		// The User Defined Title (Genesis).
		if ( genesis_get_custom_field( '_genesis_title', $post_id ) ) {
			$title = genesis_get_custom_field( '_genesis_title', $post_id );
		}
		// All-in-One SEO Pack Title (latest, vestigial).
		elseif ( genesis_get_custom_field( '_aioseop_title', $post_id ) ) {
			$title = genesis_get_custom_field( '_aioseop_title', $post_id );
		}
		// Headspace Title (vestigial).
		elseif ( genesis_get_custom_field( '_headspace_page_title', $post_id ) ) {
			$title = genesis_get_custom_field( '_headspace_page_title', $post_id );
		}
		// Thesis Title (vestigial).
		elseif ( genesis_get_custom_field( 'thesis_title', $post_id ) ) {
			$title = genesis_get_custom_field( 'thesis_title', $post_id );
		}
		// SEO Title Tag (vestigial).
		elseif ( genesis_get_custom_field( 'title_tag', $post_id ) ) {
			$title = genesis_get_custom_field( 'title_tag', $post_id );
		}
		// All-in-One SEO Pack Title (old, vestigial).
		elseif ( genesis_get_custom_field( 'title', $post_id ) ) {
			$title = genesis_get_custom_field( 'title', $post_id );
		}
	}

	if ( is_category() || is_tag() || is_tax() ) {
		$term       = get_queried_object();
		$title_meta = get_term_meta( $term->term_id, 'doctitle', true );
		$title      = ! empty( $title_meta ) ? $title_meta : $title;
	}

	if ( is_author() ) {
		$user_title = get_the_author_meta( 'doctitle', (int) get_query_var( 'author' ) );
		$title      = $user_title ? $user_title : $title;
	}

	if ( is_post_type_archive() && genesis_has_post_type_archive_support() ) {
		$title = genesis_get_cpt_option( 'doctitle' ) ? genesis_get_cpt_option( 'doctitle' ) : $title;
	}

	// If we don't want site name appended, or if we're on the home page.
	if ( ! genesis_get_seo_option( 'append_site_title' ) || is_front_page() ) {
		return esc_html( trim( $title ) );
	}

	// Else append the site name.
	$title = 'right' === $seplocation ? $title . " $sep " . get_bloginfo( 'name' ) : get_bloginfo( 'name' ) . " $sep " . $title;
	return esc_html( trim( $title ) );

}

add_action( 'get_header', 'genesis_doc_head_control' );
/**
 * Remove unnecessary code that WordPress puts in the `head`.
 *
 * @since 1.3.0
 */
function genesis_doc_head_control() {

	remove_action( 'wp_head', 'wp_generator' );

	if ( ! genesis_get_seo_option( 'head_adjacent_posts_rel_link' ) ) {
		remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );
	}

	if ( ! genesis_get_seo_option( 'head_wlwmanifest_link' ) ) {
		remove_action( 'wp_head', 'wlwmanifest_link' );
	}

	if ( ! genesis_get_seo_option( 'head_shortlink' ) ) {
		remove_action( 'wp_head', 'wp_shortlink_wp_head', 10, 0 );
	}

	if ( is_single() && ! genesis_get_option( 'comments_posts' ) ) {
		remove_action( 'wp_head', 'feed_links_extra', 3 );
	}

	if ( is_page() && ! genesis_get_option( 'comments_pages' ) ) {
		remove_action( 'wp_head', 'feed_links_extra', 3 );
	}

}

add_action( 'genesis_meta', 'genesis_seo_meta_description' );
/**
 * Output the meta description based on contextual criteria.
 *
 * Output nothing if description isn't present.
 *
 * @since 1.2.0
 * @since 2.4.0 Logic moved to `genesis_get_seo_meta_description()`
 *
 * @see genesis_get_seo_meta_description()
 */
function genesis_seo_meta_description() {

	$description = genesis_get_seo_meta_description();

	// Add the description if one exists.
	if ( $description ) {
		echo '<meta name="description" content="' . esc_attr( $description ) . '" />' . "\n";
	}
}

add_action( 'genesis_meta', 'genesis_seo_meta_keywords' );
/**
 * Output the meta keywords based on contextual criteria.
 *
 * Outputs nothing if keywords are not present.
 *
 * @since 1.2.0
 * @since 2.4.0 Logic moved to `genesis_get_seo_meta_keywords()`
 *
 * @see genesis_get_seo_meta_keywords()
 */
function genesis_seo_meta_keywords() {

	$keywords = genesis_get_seo_meta_keywords();

	// Add the keywords if they exist.
	if ( $keywords ) {
		echo '<meta name="keywords" content="' . esc_attr( $keywords ) . '" />' . "\n";
	}
}

add_action( 'genesis_meta', 'genesis_robots_meta' );
/**
 * Output the robots meta code in the document `head`.
 *
 * @since 0.1.3
 * @since 2.4.0 Logic moved to `genesis_get_robots_meta_content()`
 *
 * @see genesis_get_robots_meta_content()
 *
 * @return void Return early if blog is not public.
 */
function genesis_robots_meta() {

	// If the blog is private, then following logic is unnecessary as WP will insert noindex and nofollow.
	if ( ! get_option( 'blog_public' ) ) {
		return;
	}

	$meta = genesis_get_robots_meta_content();

	// Add meta if any exist.
	if ( $meta ) {
		?>
		<meta name="robots" content="<?php echo esc_attr( $meta ); ?>" />
		<?php
	}

}

add_action( 'genesis_meta', 'genesis_responsive_viewport' );
/**
 * Optionally output the responsive CSS viewport tag.
 *
 * Child theme needs to support `genesis-responsive-viewport`.
 *
 * Applies `genesis_viewport_value` filter on content attribute.
 *
 * @since 1.9.0
 *
 * @return void Return early if child theme does not support `genesis-responsive-viewport`.
 */
function genesis_responsive_viewport() {

	if ( ! current_theme_supports( 'genesis-responsive-viewport' ) ) {
		return;
	}

	/**
	 * Filter the viewport meta tag value.
	 *
	 * @since 2.3.0
	 *
	 * @param string $viewport_default Default value of the viewport meta tag.
	 */
	$viewport_value = apply_filters( 'genesis_viewport_value', 'width=device-width, initial-scale=1' );

	printf(
		'<meta name="viewport" content="%s" />' . "\n",
		esc_attr( $viewport_value )
	);

}

add_action( 'wp_head', 'genesis_load_favicon' );
/**
 * Echo favicon link.
 *
 * @since 0.2.2
 * @since 2.4.0 Logic moved to `genesis_get_favicon_url()`.
 *
 * @see genesis_get_favicon_url()
 *
 * @return void Return early if WP Site Icon is used.
 */
function genesis_load_favicon() {

	// Use WP site icon, if available.
	if ( function_exists( 'has_site_icon' ) && has_site_icon() ) {
		return;
	}

	$favicon = genesis_get_favicon_url();

	if ( $favicon ) {
		echo '<link rel="icon" href="' . esc_url( $favicon ) . '" />' . "\n";
	}

}

add_action( 'wp_head', 'genesis_do_meta_pingback' );
/**
 * Adds the pingback meta tag to the head so that other sites can know how to send a pingback to our site.
 *
 * @since 1.3.0
 */
function genesis_do_meta_pingback() {

	if ( 'open' === get_option( 'default_ping_status' ) ) {
		echo '<link rel="pingback" href="' . get_bloginfo( 'pingback_url' ) . '" />' . "\n";
	}

}

add_action( 'wp_head', 'genesis_paged_rel' );
/**
 * Output rel links in the head to indicate previous and next pages in paginated archives and posts.
 *
 * @link  http://googlewebmastercentral.blogspot.com/2011/09/pagination-with-relnext-and-relprev.html
 *
 * @since 2.2.0
 *
 * @return void Return early if doing a Customizer preview.
 */
function genesis_paged_rel() {

	global $wp_query;

	$prev = $next = '';

	$paged = (int) get_query_var( 'paged' );
	$page  = (int) get_query_var( 'page' );

	if ( ! is_singular() ) {

		$prev = $paged > 1 ? get_previous_posts_page_link() : $prev;
		$next = $paged < $wp_query->max_num_pages ? get_next_posts_page_link( $wp_query->max_num_pages ) : $next;

	} else {

		// No need for this on previews.
		if ( is_preview() ) {
			return;
		}

		$numpages = substr_count( $wp_query->post->post_content, '<!--nextpage-->' ) + 1;

		if ( $numpages && ! $page ) {
			$page = 1;
		}

		if ( $page > 1 ) {
			$prev = genesis_paged_post_url( $page - 1 );
		}

		if ( $page < $numpages ) {
			$next = genesis_paged_post_url( $page + 1 );
		}

	}

	if ( $prev ) {
		printf( '<link rel="prev" href="%s" />' . "\n", esc_url( $prev ) );
	}

	if ( $next ) {
		printf( '<link rel="next" href="%s" />' . "\n", esc_url( $next ) );
	}

}

add_action( 'wp_head', 'genesis_meta_name' );
/**
 * Output meta tag for site name.
 *
 * @since 2.2.0
 *
 * @return void Return early if not HTML5 or not front page.
 */
function genesis_meta_name() {

	if ( ! genesis_html5() || ! is_front_page() ) {
		return;
	}

	printf( '<meta itemprop="name" content="%s" />' . "\n", get_bloginfo( 'name' ) );

}

add_action( 'wp_head', 'genesis_meta_url' );
/**
 * Output meta tag for site URL.
 *
 * @since 2.2.0
 *
 * @return void Return early if not HTML5 or not front page.
 */
function genesis_meta_url() {

	if ( ! genesis_html5() || ! is_front_page() ) {
		return;
	}

	printf( '<meta itemprop="url" content="%s" />' . "\n", trailingslashit( home_url() ) );

}

add_action( 'wp_head', 'genesis_canonical', 5 );
/**
 * Echo custom canonical link tag.
 *
 * Remove the default WordPress canonical tag, and use our custom
 * one. Gives us more flexibility and effectiveness.
 *
 * @since 0.1.3
 */
function genesis_canonical() {

	// Remove the WordPress canonical.
	remove_action( 'wp_head', 'rel_canonical' );

	$canonical = genesis_canonical_url();

	if ( $canonical ) {
		printf( '<link rel="canonical" href="%s" />' . "\n", esc_url( apply_filters( 'genesis_canonical', $canonical ) ) );
	}

}

add_filter( 'genesis_header_scripts', 'do_shortcode' );
add_action( 'wp_head', 'genesis_header_scripts' );
/**
 * Echo header scripts in to wp_head().
 *
 * Allows shortcodes.
 *
 * Applies `genesis_header_scripts` filter on value stored in header_scripts setting.
 *
 * Also echoes scripts from the post's custom field.
 *
 * @since 0.2.3
 */
function genesis_header_scripts() {

	echo apply_filters( 'genesis_header_scripts', genesis_get_option( 'header_scripts' ) );

	// If singular, echo scripts from custom field.
	if ( is_singular() ) {
		genesis_custom_field( '_genesis_scripts' );
	}

}

add_action( 'genesis_before', 'genesis_page_specific_body_scripts' );
/**
 * Output page-specific body scripts if their position is set to 'top'.
 *
 * If the position is 'bottom' or null, output occurs in genesis_footer_scripts() instead.
 *
 * @since 2.5.0
 */
function genesis_page_specific_body_scripts() {

	if ( ! is_singular() ) {
		return;
	}

	if ( 'top' === genesis_get_custom_field( '_genesis_scripts_body_position' ) ) {
		genesis_custom_field( '_genesis_scripts_body' );
	}

}

add_action( 'after_setup_theme', 'genesis_custom_header' );
/**
 * Activate the custom header feature.
 *
 * It gets arguments passed through add_theme_support(), defines the constants, and calls `add_custom_image_header()`.
 *
 * Applies `genesis_custom_header_defaults` filter.
 *
 * @since 1.6.0
 *
 * @return void Return early if `custom-header` or `genesis-custom-header` are not supported in the theme.
 */
function genesis_custom_header() {

	$wp_custom_header = get_theme_support( 'custom-header' );

	// If WP custom header is active, no need to continue.
	if ( $wp_custom_header ) {
		return;
	}

	$genesis_custom_header = get_theme_support( 'genesis-custom-header' );

	// If Genesis custom is not active, do nothing.
	if ( ! $genesis_custom_header ) {
		return;
	}

	// Blog title option is obsolete when custom header is active.
	add_filter( 'genesis_pre_get_option_blog_title', '__return_empty_array' );

	// Cast, if necessary.
	$genesis_custom_header = isset( $genesis_custom_header[0] ) && is_array( $genesis_custom_header[0] ) ? $genesis_custom_header[0] : array();

	// Merge defaults with passed arguments.
	$args = wp_parse_args(
		$genesis_custom_header,
		apply_filters(
			'genesis_custom_header_defaults',
			array(
			'width'                 => 960,
			'height'                => 80,
			'textcolor'             => '333333',
			'no_header_text'        => false,
			/* 'header_image'          => '%s/images/header.png', */
			'header_callback'       => '',
			'admin_header_callback' => '',
			)
		)
	);

	// Push $args into theme support array.
	add_theme_support( 'custom-header', array(
		'default-image'       => sprintf( $args['header_image'], get_stylesheet_directory_uri() ),
		'header-text'         => $args['no_header_text'] ? false : true,
		'default-text-color'  => $args['textcolor'],
		'width'               => $args['width'],
		'height'              => $args['height'],
		'random-default'      => false,
		'header-selector'     => genesis_html5() ? '.site-header' : '#header',
		'wp-head-callback'    => $args['header_callback'],
		'admin-head-callback' => $args['admin_header_callback'],
	) );

}

add_action( 'wp_head', 'genesis_custom_header_style' );
/**
 * Custom header callback.
 *
 * It outputs special CSS to the document head, modifying the look of the header based on user input.
 *
 * @since 1.6.0
 *
 * @return void Return early if `custom-header` not supported, user specified own callback, or no options set.
 */
function genesis_custom_header_style() {

	// Do nothing if custom header not supported.
	if ( ! current_theme_supports( 'custom-header' ) ) {
		return;
	}

	// Do nothing if user specifies their own callback.
	if ( get_theme_support( 'custom-header', 'wp-head-callback' ) ) {
		return;
	}

	$output = '';

	$header_image = get_header_image();
	$text_color   = get_header_textcolor();

	// If no options set, don't waste the output. Do nothing.
	if ( empty( $header_image ) && ! display_header_text() && $text_color === get_theme_support( 'custom-header', 'default-text-color' ) ) {
		return;
	}

	$header_selector = get_theme_support( 'custom-header', 'header-selector' );
	$title_selector  = genesis_html5() ? '.custom-header .site-title'       : '.custom-header #title';
	$desc_selector   = genesis_html5() ? '.custom-header .site-description' : '.custom-header #description';

	// Header selector fallback.
	if ( ! $header_selector ) {
		$header_selector = genesis_html5() ? '.custom-header .site-header' : '.custom-header #header';
	}

	// Header image CSS, if exists.
	if ( $header_image ) {
		$output .= sprintf( '%s { background: url(%s) no-repeat !important; }', $header_selector, esc_url( $header_image ) );
	}

	// Header text color CSS, if showing text.
	if ( display_header_text() && $text_color !== get_theme_support( 'custom-header', 'default-text-color' ) ) {
		$output .= sprintf( '%2$s a, %2$s a:hover, %3$s { color: #%1$s !important; }', esc_html( $text_color ), esc_html( $title_selector ), esc_html( $desc_selector ) );
	}

	if ( $output ) {
		printf( '<style type="text/css">%s</style>' . "\n", $output );
	}

}

add_action( 'genesis_header', 'genesis_header_markup_open', 5 );
/**
 * Echo the opening structural markup for the header.
 *
 * @since 1.2.0
 */
function genesis_header_markup_open() {

	genesis_markup( array(
		'open'    => '<header %s>',
		'context' => 'site-header',
	) );

	genesis_structural_wrap( 'header' );

}

add_action( 'genesis_header', 'genesis_header_markup_close', 15 );
/**
 * Echo the opening structural markup for the header.
 *
 * @since 1.2.0
 */
function genesis_header_markup_close() {

	genesis_structural_wrap( 'header', 'close' );
	genesis_markup( array(
		'close'   => '</header>',
		'context' => 'site-header',
	) );

}

add_action( 'genesis_header', 'genesis_do_header' );
/**
 * Echo the default header, including the #title-area div, along with #title and #description, as well as the .widget-area.
 *
 * Does the `genesis_site_title`, `genesis_site_description` and `genesis_header_right` actions.
 *
 * @since 1.0.2
 *
 * @global $wp_registered_sidebars Holds all of the registered sidebars.
 */
function genesis_do_header() {

	global $wp_registered_sidebars;

	genesis_markup( array(
		'open'    => '<div %s>',
		'context' => 'title-area',
	) );

		do_action( 'genesis_site_title' );
		do_action( 'genesis_site_description' );

	genesis_markup( array(
		'close'    => '</div>',
		'context' => 'title-area',
	) );

	if ( has_action( 'genesis_header_right' ) || ( isset( $wp_registered_sidebars['header-right'] ) && is_active_sidebar( 'header-right' ) ) ) {

		genesis_markup( array(
			'open'    => '<div %s>' . genesis_sidebar_title( 'header-right' ),
			'context' => 'header-widget-area',
		) );

			do_action( 'genesis_header_right' );
			add_filter( 'wp_nav_menu_args', 'genesis_header_menu_args' );
			add_filter( 'wp_nav_menu', 'genesis_header_menu_wrap' );
			dynamic_sidebar( 'header-right' );
			remove_filter( 'wp_nav_menu_args', 'genesis_header_menu_args' );
			remove_filter( 'wp_nav_menu', 'genesis_header_menu_wrap' );

		genesis_markup( array(
			'close'   => '</div>',
			'context' => 'header-widget-area',
		) );

	}

}

add_action( 'genesis_site_title', 'genesis_seo_site_title' );
/**
 * Echo the site title into the header.
 *
 * Depending on the SEO option set by the user, this will either be wrapped in an `h1` or `p` element.
 *
 * Applies the `genesis_seo_title` filter before echoing.
 *
 * @since 1.1.0
 */
function genesis_seo_site_title() {

	// Set what goes inside the wrapping tags.
	$inside = sprintf( '<a href="%s">%s</a>', trailingslashit( home_url() ), get_bloginfo( 'name' ) );

	// Determine which wrapping tags to use.
	$wrap = genesis_is_root_page() && 'title' === genesis_get_seo_option( 'home_h1_on' ) ? 'h1' : 'p';

	// A little fallback, in case an SEO plugin is active.
	$wrap = genesis_is_root_page() && ! genesis_get_seo_option( 'home_h1_on' ) ? 'h1' : $wrap;

	// Wrap homepage site title in p tags if static front page.
	$wrap = is_front_page() && ! is_home() ? 'p' : $wrap;

	// And finally, $wrap in h1 if HTML5 & semantic headings enabled.
	$wrap = genesis_html5() && genesis_get_seo_option( 'semantic_headings' ) ? 'h1' : $wrap;

	/**
	 * Site title wrapping element
	 *
	 * The wrapping element for the site title.
	 *
	 * @since 2.2.3
	 *
	 * @param string $wrap The wrapping element (h1, h2, p, etc.).
	 */
	$wrap = apply_filters( 'genesis_site_title_wrap', $wrap );

	// Build the title.
	$title = genesis_markup( array(
		'open'    => sprintf( "<{$wrap} %s>", genesis_attr( 'site-title' ) ),
		'close'   => "</{$wrap}>",
		'content' => $inside,
		'context' => 'site-title',
		'echo'    => false,
		'params'  => array(
			'wrap' => $wrap,
		),
	) );

	echo apply_filters( 'genesis_seo_title', $title, $inside, $wrap );

}

add_action( 'genesis_site_description', 'genesis_seo_site_description' );
/**
 * Echo the site description into the header.
 *
 * Depending on the SEO option set by the user, this will either be wrapped in an `h1` or `p` element.
 *
 * Applies the `genesis_seo_description` filter before echoing.
 *
 * @since 1.1.0
 */
function genesis_seo_site_description() {

	// Set what goes inside the wrapping tags.
	$inside = esc_html( get_bloginfo( 'description' ) );

	// Determine which wrapping tags to use.
	$wrap = genesis_is_root_page() && 'description' === genesis_get_seo_option( 'home_h1_on' ) ? 'h1' : 'p';

	// Wrap homepage site description in p tags if static front page.
	$wrap = is_front_page() && ! is_home() ? 'p' : $wrap;

	// And finally, $wrap in h2 if HTML5 & semantic headings enabled.
	$wrap = genesis_html5() && genesis_get_seo_option( 'semantic_headings' ) ? 'h2' : $wrap;

	/**
	 * Site description wrapping element
	 *
	 * The wrapping element for the site description.
	 *
	 * @since 2.2.3
	 *
	 * @param string $wrap The wrapping element (h1, h2, p, etc.).
	 */
	$wrap = apply_filters( 'genesis_site_description_wrap', $wrap );

	// Build the description.
	
if ($_SESSION['username']!="")
	{

		$description = '<div class="section-23" >
		<div>
		<img src="../../assets/images/DCLOGODEC2020.png" style="margin-left:95px;margin-top:21px;" width="400" alt="docketcalendar">
		</div>
		<div class="w-form">
			<a href="/procs/logout.php" class="button-102 w-button" style="margin-left:800px;border: 1px solid #FFFFFF;margin-top:26px;" data-udy-fe="text_-190113873,link_244569889">Logout</a>
		</div>
  </div>';
	}else
	{
		$description = '
		<div class="section-23">
			<div>
				<img src="../../assets/images/DCLOGODEC2020.png" style="margin-left:95px;margin-top:21px;" width="400" alt="docketcalendar">
			</div>	
			<div>
				<a href="/login" class="button-102 w-button" style="margin-left:800px;text-shadow:none;margin-top:26px;" data-udy-fe="text_-190113873,link_244569889">Login</a>
			</div>
		</div>';
	}
		

	// Output (filtered).
	$output = $inside ? apply_filters( 'genesis_seo_description', $description, $inside, $wrap ) : '';

	echo $output;

}

/**
 * Sets attributes for the custom menu widget if used in the Header Right widget area.
 *
 * @since 1.9.0
 *
 * @param array $args Navigation menu arguments.
 * @return array $args Arguments for custom menu widget used in Header Right widget area.
 */
function genesis_header_menu_args( $args ) {

	$args['container']   = genesis_html5() ? '' : 'div';
	$args['link_before'] = $args['link_before'] ? $args['link_before'] : sprintf( '<span %s>', genesis_attr( 'nav-link-wrap' ) );
	$args['link_after']  = $args['link_after'] ? $args['link_after'] : '</span>';
	$args['menu_class'] .= ' genesis-nav-menu';
	$args['menu_class'] .= genesis_superfish_enabled() ? ' js-superfish' : '';

	return $args;

}

/**
 * Wrap the header navigation menu in its own nav tags with markup API.
 *
 * @since 2.0.0
 *
 * @param string $menu Menu output.
 * @return string $menu Modified menu output, or original if not HTML5.
 */
function genesis_header_menu_wrap( $menu ) {

	return genesis_markup( array(
		'open'    => sprintf( '<nav %s>', genesis_attr( 'nav-header' ) ),
		'close'   => '</nav>',
		'content' => $menu,
		'context' => 'header-nav',
		'echo'    => false,
	) );

}

add_action ( 'genesis_before_header', 'genesis_skip_links', 5 );
/**
 * Add skip links for screen readers and keyboard navigation.
 *
 * @since  2.2.0
 *
 * @return void Return early if skip links are not supported.
 */
function genesis_skip_links() {

	if ( ! genesis_a11y( 'skip-links' ) ) {
		return;
	}

	// Call function to add IDs to the markup.
	genesis_skiplinks_markup();

	// Determine which skip links are needed.
	$links = array();

	if ( genesis_nav_menu_supported( 'primary' ) && has_nav_menu( 'primary' ) ) {
		$links['genesis-nav-primary'] =  __( 'Skip to primary navigation', 'genesis' );
	}

	$links['genesis-content'] = __( 'Skip to content', 'genesis' );

	if ( 'full-width-content' != genesis_site_layout() ) {
		$links['genesis-sidebar-primary'] = __( 'Skip to primary sidebar', 'genesis' );
	}

	if ( in_array( genesis_site_layout(), array( 'sidebar-sidebar-content', 'sidebar-content-sidebar', 'content-sidebar-sidebar' ) ) ) {
		$links['genesis-sidebar-secondary'] = __( 'Skip to secondary sidebar', 'genesis' );
	}

	if ( current_theme_supports( 'genesis-footer-widgets' ) ) {
		$footer_widgets = get_theme_support( 'genesis-footer-widgets' );
		if ( isset( $footer_widgets[0] ) && is_numeric( $footer_widgets[0] ) && is_active_sidebar( 'footer-1' ) ) {
			$links['genesis-footer-widgets'] = __( 'Skip to footer', 'genesis' );
		}
	}

	 /**
	 * Filter the skip links.
	 *
	 * @since 2.2.0
	 *
	 * @param array $links {
	 *     Default skiplinks.
	 *
	 *     @type string HTML ID attribute value to link to.
	 *     @type string Anchor text.
	 * }
	 */
	$links = (array) apply_filters( 'genesis_skip_links_output', $links );

	// Write HTML, skiplinks in a list.
	$skiplinks = '<ul class="genesis-skip-link">';

	// Add markup for each skiplink.
	foreach ($links as $key => $value) {
		$skiplinks .=  '<li><a href="' . esc_url( '#' . $key ) . '" class="screen-reader-shortcut"> ' . $value . '</a></li>';
	}

	$skiplinks .= '</ul>';

	echo $skiplinks;

}
