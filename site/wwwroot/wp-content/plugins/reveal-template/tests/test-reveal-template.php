<?php

defined( 'ABSPATH' ) or die();

class Reveal_Template_Test extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();
		$this->post_id = $this->factory->post->create();
		$this->user_id = $this->factory->user->create();

		$theme = wp_get_theme( 'twentyseventeen' );
		switch_theme( $theme->get_stylesheet() );
	}

	public function tearDown() {
		parent::tearDown();
		$this->unset_current_user();
	}


	//
	//
	// DATA PROVIDERS
	//
	//


	public static function get_templates() {
		return array(
			array( '404' ),
			array( 'archive' ),
			array( 'attachment' ),
			array( 'author' ),
			array( 'category' ),
			array( 'date' ),
			array( 'embed' ),
			array( 'frontpage' ),
			array( 'home' ),
			array( 'index' ),
			array( 'page' ),
			array( 'paged' ),
			array( 'search' ),
			array( 'single' ),
			array( 'singular' ),
			array( 'tag' ),
			array( 'taxonomy' ),
		);
	}

	public static function get_template_path_types() {
		$ret = array();
		foreach ( c2c_RevealTemplate::get_instance()->get_template_path_types() as $type => $desc ) {
			$ret[] = array( $type );
		}
		return $ret;
	}

	public static function get_option_defaults() {
		return array(
			array( 'display_in_footer', true ),
			array( 'format'           , '<p>Rendered template: %template%</p>' ),
			array( 'template_path'    , 'theme-relative' ),
		);
	}



	//
	//
	// HELPER FUNCTIONS
	//
	//


	private function create_user( $role, $set_as_current = true ) {
		$user_id = $this->factory->user->create( array( 'role' => $role ) );
		if ( $set_as_current ) {
			wp_set_current_user( $user_id );
		}
		return $user_id;
	}

	// helper function, unsets current user globally. Taken from post.php test.
	private function unset_current_user() {
		global $current_user, $user_ID;

		$current_user = $user_ID = null;
	}

	private function set_option( $settings = array() ) {
		$defaults = $this->get_option_defaults();
		$defaults2 = array();
		foreach ( $defaults as $item ) {
			$defaults2[ $item[0] ] = $item[1];
		}
		$settings = wp_parse_args( $settings, $defaults2 );
		c2c_RevealTemplate::get_instance()->update_option( $settings, true );
	}

	public function get_output( $template_path_type, $args = array(), $use_function = true ) {
		ob_start();
		if ( $use_function ) {
			c2c_reveal_template( true, $template_path_type, $args );
		} else {
			$args['echo'] = true;
			c2c_RevealTemplate::get_instance()->reveal( $template_path_type, $args );
		}
		$out = ob_get_contents();
		ob_end_clean();
		return $out;
	}

	public function get_template_path_part( $absolute_path, $template_type ) {
		$path = pathinfo( $absolute_path );
		$parts = explode( DIRECTORY_SEPARATOR, $absolute_path );

		switch ( $template_type ) {
			case 'absolute':
				$part = get_stylesheet_directory() . '/' . $path['basename'];
				break;
			case 'filename' :
				$part = $path['basename'];
				break;
			case 'relative' :
				$part = str_replace( ABSPATH, '', $absolute_path );
				break;
			case 'theme-relative':
				$i = array_search( 'themes', $parts );
				$part = implode( DIRECTORY_SEPARATOR, array_slice( $parts, $i+1 ) );
				break;
			default:
				$part = "unexpected template type $template_type";
		}

		return $part;
	}

	public function assert_template_types( $absolute_path ) {
		foreach ( c2c_RevealTemplate::get_instance()->get_template_path_types() as $template_type => $desc ) {
			$expected = $this->get_template_path_part( $absolute_path, $template_type );
			$this->assertEquals( $expected, c2c_reveal_template( false, $template_type ) );
			$this->assertEquals( $expected, c2c_RevealTemplate::get_instance()->reveal( $template_type, array( 'echo' => false ) ) );
		}
	}


	//
	//
	// TESTS
	//
	//


	public function test_class_exists() {
		$this->assertTrue( class_exists( 'c2c_RevealTemplate' ) );
	}

	public function test_version() {
		$this->assertEquals( '3.4.1', c2c_RevealTemplate::get_instance()->version() );
	}

	public function test_plugin_framework_class_name() {
		$this->assertTrue( class_exists( 'c2c_RevealTemplate_Plugin_048' ) );
	}

	public function test_plugin_framework_version() {
		$this->assertEquals( '048', c2c_RevealTemplate::get_instance()->c2c_plugin_version() );
	}

	public function test_instance_object_is_returned() {
		$this->assertTrue( is_a( c2c_RevealTemplate::get_instance(), 'c2c_RevealTemplate' ) );
	}

	/* Widget */

	public function test_widget_class_exists() {
		$this->assertTrue( class_exists( 'c2c_RevealTemplateWidget' ) );
	}

	public function test_widget_version() {
		$this->assertEquals( '004', c2c_RevealTemplateWidget::version() );
	}

	public function test_widget_framework_class_name() {
		$this->assertTrue( class_exists( 'c2c_Widget_013' ) );
	}

	public function test_widget_framework_version() {
		$this->assertEquals( '013', c2c_Widget_013::version() );
	}

	public function test_widget_hooks_widgets_init() {
		$this->assertEquals( 10, has_filter( 'widgets_init', array( 'c2c_RevealTemplateWidget', 'register_widget' ) ) );
	}

	/**
	 * @dataProvider get_template_path_types
	 */
	public function test_template_tag_echoes_nothing_for_non_user( $template_path_type ) {
		apply_filters( 'single_template', get_stylesheet_directory() . '/single.php' );

		$this->assertFalse( is_user_logged_in() );
		$this->assertEmpty( $this->get_output( $template_path_type ) );
		$this->assertEmpty( $this->get_output( $template_path_type, array(), false ) );
	}

	/**
	 * @dataProvider get_templates
	 */
	public function test_template_tag_for_path_types( $template ) {
		$this->create_user( 'administrator' );

		$full_path = get_stylesheet_directory() . "/$template.php";
		apply_filters( $template . '_template', $full_path );

		$this->assertTrue( is_user_logged_in() );
		$this->assertTrue( c2c_RevealTemplate::get_instance()->reveal_to_current_user() );
		$this->assert_template_types( $full_path );
	}

	/**
	 * @dataProvider get_templates
	 */
	public function test_template_tag_for_path_types_with_fallback_template( $template ) {
		$this->create_user( 'administrator' );

		$full_path = get_stylesheet_directory() . "/index.php";
		apply_filters( $template . '_template', $full_path );

		$this->assertTrue( is_user_logged_in() );
		$this->assertTrue( c2c_RevealTemplate::get_instance()->reveal_to_current_user() );
		$this->assert_template_types( $full_path );
	}

	/**
	 * @dataProvider get_templates
	 */
	public function test_hooks_template_filter( $template ) {
		$this->assertNotFalse( has_filter( $template . '_template', array( c2c_RevealTemplate::get_instance(), 'template_handler' ) ) );
	}

	public function test_c2c_reveal_template_arg_admin_only() {
		apply_filters( 'single_template', get_stylesheet_directory() . '/single.php' );

		$this->assertEmpty( $this->get_output( 'filename', array( 'admin_only' => true ) ) );
		$this->assertEmpty( $this->get_output( 'filename', array( 'admin_only' => true ), false ) );
		$this->assertEquals( 'single.php', $this->get_output( 'filename', array( 'admin_only' => false ) ) );
		$this->assertEquals( 'single.php', $this->get_output( 'filename', array( 'admin_only' => false ), false ) );

		$this->create_user( 'administrator' );

		$this->assertEquals( 'single.php', $this->get_output( 'filename', array( 'admin_only' => true ) ) );
		$this->assertEquals( 'single.php', $this->get_output( 'filename', array( 'admin_only' => true ), false ) );
		$this->assertEquals( 'single.php', $this->get_output( 'filename', array( 'admin_only' => false ) ) );
		$this->assertEquals( 'single.php', $this->get_output( 'filename', array( 'admin_only' => false ), false ) );

	}

	/**
	 * Currently c2c_reveal_template() has an $echo arg that takes precedence
	 * over 'echo' specified via $args, so verify as much.
	 */
	public function test_c2c_reveal_template_arg_echo() {
		$this->create_user( 'administrator' );

		apply_filters( 'single_template', get_stylesheet_directory() . '/single.php' );

		$this->assertEquals( 'single.php', $this->get_output( 'filename', array( 'echo' => false ) ) );
		$this->assertEquals( 'single.php', $this->get_output( 'filename', array( 'echo' => false ), false ) );

		ob_start();
		c2c_reveal_template( false, 'filename', array( 'echo' => true ) );
		$out = ob_get_contents();
		ob_end_clean();

		$this->assertEmpty( $out );
	}

	public function test_c2c_reveal_template_arg_format() {
		apply_filters( 'category_template', get_stylesheet_directory() . '/category.php' );

		$this->assertEquals(
			'Abc (category.php)',
			c2c_reveal_template( false, 'filename', array( 'format' => 'Abc (%template%)', 'format_from_settings' => false ) )
		);
		$this->assertEquals(
			'Abc (category.php)',
			c2c_RevealTemplate::get_instance()->reveal( 'filename', array( 'format' => 'Abc (%template%)', 'format_from_settings' => false, 'echo' => false ) )
		);
	}

	public function test_c2c_reveal_template_arg_format_from_settings() {
		apply_filters( 'category_template', get_stylesheet_directory() . '/category.php' );

		$options = c2c_RevealTemplate::get_instance()->get_options();

		$this->assertEquals(
			str_replace( '%template%', 'category.php', $options['format'] ),
			c2c_reveal_template( false, 'filename', array( 'format' => 'Abc (%template%)', 'format_from_settings' => true ) )
		);
		$this->assertEquals(
			str_replace( '%template%', 'category.php', $options['format'] ),
			c2c_RevealTemplate::get_instance()->reveal( 'filename', array( 'format' => 'Abc (%template%)', 'format_from_settings' => true, 'echo' => false ) )
		);
	}

	public function test_c2c_reveal_template_arg_return() {
		apply_filters( 'category_template', get_stylesheet_directory() . '/category.php' );

		$this->assertEmpty( '', c2c_reveal_template( false, 'filename', array( 'return' => false ) ) );
		$this->assertEmpty( '', c2c_reveal_template( false, 'filename', array( 'return' => 'gibberish' ) ) );
		$this->assertEquals( 'category.php', c2c_reveal_template( false, 'filename', array( 'return' => true ) ) );

		$this->assertEmpty( '', c2c_RevealTemplate::get_instance()->reveal( 'filename', array( 'return' => false, 'echo' => false ) ) );
		$this->assertEmpty( '', c2c_RevealTemplate::get_instance()->reveal( 'filename', array( 'return' => 'gibberish', 'echo' => false ) ) );
		$this->assertEquals( 'category.php', c2c_RevealTemplate::get_instance()->reveal( 'filename', array( 'return' => true, 'echo' => false ) ) );

		$this->create_user( 'administrator' );

		$this->assertEquals( 'category.php', c2c_reveal_template( false, 'filename', array( 'return' => false ) ) );
		$this->assertEquals( 'category.php', c2c_reveal_template( false, 'filename', array( 'return' => true ) ) );
		$this->assertEquals( 'category.php', c2c_reveal_template( false, 'filename', array( 'return' => 'gibberish' ) ) );

		$this->assertEquals( 'category.php', c2c_RevealTemplate::get_instance()->reveal( 'filename', array( 'return' => false, 'echo' => false ) ) );
		$this->assertEquals( 'category.php', c2c_RevealTemplate::get_instance()->reveal( 'filename', array( 'return' => true, 'echo' => false ) ) );
		$this->assertEquals( 'category.php', c2c_RevealTemplate::get_instance()->reveal( 'filename', array( 'return' => 'gibberish', 'echo' => false ) ) );
	}

	public function test_page_specific_template_is_returned_when_set() {
		$template = 'comments.php'; // Something non-standard but the template actually exists.
		$post_id = $this->factory->post->create( array( 'post_type' => 'page' ) );
		add_post_meta( $post_id, '_wp_page_template', $template, true );

		$this->go_to( get_permalink( $post_id ) );
		$templates = get_page_template();

		$this->assertEquals( $template, get_page_template_slug( $post_id ) );
		$this->assertEquals( $template, c2c_reveal_template( false, 'filename', array( 'return' => true ) ) );
		$this->assertEquals( $template, c2c_RevealTemplate::get_instance()->reveal( 'filename', array( 'return' => true, 'echo' => false ) ) );
	}

	/**
	 * @dataProvider get_template_path_types
	 */
	public function test_shortcode_echoes_nothing_for_non_user( $template_path_type ) {
		$str = 'The template used to render this page is [revealtemplate type="' . $template_path_type . '"].';
		$expected = 'The template used to render this page is .';
		apply_filters( 'category_template', get_stylesheet_directory() . '/category.php' );

		$this->assertEquals( $expected, do_shortcode( $str ) );
	}

	/**
	 * @dataProvider get_template_path_types
	 */
	public function test_shortcode_for_path_types( $template_path_type ) {
		$this->create_user( 'administrator' );

		$str = 'The template used to render this page is [revealtemplate type="' . $template_path_type . '"].';
		$full_path = get_stylesheet_directory() . "/category.php";
		$expected = 'The template used to render this page is ' . $this->get_template_path_part( $full_path, $template_path_type ) . '.';
		apply_filters( 'category_template', $full_path );

		$this->assertEquals( $expected, do_shortcode( $str ) );
	}

	/**
	 * @dataProvider get_template_path_types
	 */
	public function test_shortcode_for_admin_only( $template_path_type ) {
		$full_path = get_stylesheet_directory() . "/category.php";
		apply_filters( 'category_template', $full_path );

		$str_no_admin = 'The template used to render this page is [revealtemplate type="' . $template_path_type . '"].';
		$str_admin_0 = 'The template used to render this page is [revealtemplate admin="0" type="' . $template_path_type . '"].';
		$str_admin_1 = 'The template used to render this page is [revealtemplate admin="1" type="' . $template_path_type . '"].';
		$expected_no_display = 'The template used to render this page is .';
		$expected_display = 'The template used to render this page is ' . $this->get_template_path_part( $full_path, $template_path_type ) . '.';

		$this->assertEquals( $expected_no_display, do_shortcode( $str_no_admin ) );
		$this->assertEquals( $expected_display, do_shortcode( $str_admin_0 ) );
		$this->assertEquals( $expected_no_display, do_shortcode( $str_admin_1 ) );

		$this->create_user( 'administrator' );

		$this->assertEquals( $expected_display, do_shortcode( $str_no_admin ) );
		$this->assertEquals( $expected_display, do_shortcode( $str_admin_0 ) );
		$this->assertEquals( $expected_display, do_shortcode( $str_admin_1 ) );
	}

	/*
	 * reveal()
	 */

	/**
	 * @dataProvider get_template_path_types
	 */
	public function test_reveal_with_echo( $template_path_type ) {
		$this->create_user( 'administrator' );

		$template = 'category';
		$full_path = get_stylesheet_directory() . "/{$template}.php";
		apply_filters( $template . '_template', $full_path );

		$expected = $this->get_template_path_part( $full_path, $template_path_type );

		$this->assertEquals( $expected, $this->get_output( $template_path_type, array(), false ) );
	}

	/*
	 * Settings
	 */

	/**
	 * @dataProvider get_option_defaults
	 */
	public function test_setting_defaults( $name, $default ) {
		$options = c2c_RevealTemplate::get_instance()->get_options();
		$defaults = $this->get_option_defaults();

		$this->assertTrue( in_array( $name, array_keys( $options ) ) );
		$this->assertEquals( $default, $options[ $name ] );
	}

	/*
	 * Setting handling
	 */

	public function test_does_not_immediately_store_default_settings_in_db() {
		$option_name = c2c_RevealTemplate::SETTING_NAME;
		// Get the options just to see if they may get saved.
		$options     = c2c_RevealTemplate::get_instance()->get_options();

		$this->assertFalse( get_option( $option_name ) );
	}

	public function test_uninstall_deletes_option() {
		$option_name = c2c_RevealTemplate::SETTING_NAME;
		$options     = c2c_RevealTemplate::get_instance()->get_options();

		// Explicitly set an option to ensure options get saved to the database.
		$this->set_option( array( 'template_path' => 'absolute' ) );

		$this->assertNotEmpty( $options );
		$this->assertNotFalse( get_option( $option_name ) );

		c2c_RevealTemplate::uninstall();

		$this->assertFalse( get_option( $option_name ) );
	}

}
