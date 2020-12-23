<?php
/*
Plugin Name: WP jScrollPane
Plugin URI: https://github.com/cornfeed/WP-jScrollPane
Description: Easily add custom javascript scrollbars to your wordpress instance
Version: 2.0.3
Author: Jacob [cornfeed]
Author URI: http://www.alltechservices-ia.com/
License: GPL/GNU
*/

// Check if the class has been defined before
if (!class_exists('wp_jscrollpane') ) {
    
    // If not, define the class
    class wp_jscrollpane {
        // Used to identify and display errors
        var $errors = array();
        // Used for array manipulation for saving/parsing
        var $post = array();
        
        // Construct the object (initialize the plugin)
        function __construct() {
            
            // Check if this is the dashboard
            if ( is_admin() ) {
                
                // This action will handle the settings form
                add_action('admin_init', array($this, 'wpjsp_admin_init') );
                
                // This action will add the Dashboard->Settings->WP jScrollPane
                // menu item, enqueue the admin page scripts & styles,
                // and display the settings page html
                add_action('admin_menu', array($this, 'wpjsp_admin_menu') );
                
                // This will register the client side functions
                add_action('wp_ajax_getthemes', array($this, 'wpjsp_get_themes') );
                add_action('wp_ajax_gethtml', array($this, 'wpjsp_generate_scrollpane') );
                
                // Register the install hook (un-install is handled with uninstall.php)
                register_activation_hook(__FILE__, array(&$this, 'wpjsp_install'));
            }
            // If this is not the dashboard, load jScrollPane
            elseif( !is_admin() )
                add_action('init', array($this, 'wpjsp_page_init') );
            
        } // End __construct()
        
        // Install the database records
        function wpjsp_install() {
            add_option('wpjsp','','','no');
            add_option('wpjsp-mouse','','','no');
        }
        
        // Add jScrollPane to the Dashboard->Settings Menu
        function wpjsp_admin_menu() {
            
            // Add the menu item and call the settings page html when clicked
			$page = add_submenu_page(
                        'options-general.php', 'WP jScrollPane',
                        'WP jScrollPane', 'manage_options',
                        __FILE__, array($this, 'wpjsp_options_page')
                    );
            
            // This will only load the scripts on this config page
			add_action('admin_print_scripts-'.$page,
                    array($this, 'wpjsp_admin_scripts') );
            add_action('admin_print_styles-'.$page,
                    array($this, 'wpjsp_admin_styles') );
            
		} // End wpjsp_admin_menu()
        
        // Load the dashboard scripts and styles if viewing our page
        function wpjsp_admin_scripts() {
            wp_deregister_script('colorpicker');
            wp_register_script(
                    'colorpicker',
                    plugins_url( '/js/jquery.colorpicker.min.js' , __FILE__ ),
                    array('jquery')
                );
            wp_register_script(
                    'wpjsp-script',
                    plugins_url( '/js/admin.js' , __FILE__ ),
                    array('jquery','colorpicker')
                );
            wp_enqueue_script('wpjsp-script');
        }
        function wpjsp_admin_styles() {
            wp_register_style(
                    'colorpicker',
                    plugins_url( '/css/jquery.colorpicker.min.css' , __FILE__ )
                );
            wp_enqueue_style('colorpicker');
            wp_register_style(
                    'wpjsp-style',
                    plugins_url( '/css/admin.css' , __FILE__ )
                );
            wp_enqueue_style('wpjsp-style');
        }
        
        // Create the settings page html
        function wpjsp_options_page() {
?>

<div id="wpjsp-wrap" class="wrap">
    <?php screen_icon('options-general'); ?><h2>WP jScrollPane Settings</h2>
    <div class="form-wrap">

<?php
            // Check if there are errors to display
            if ( wp_verify_nonce($_POST['wpjsp-nonce'], 'wpjsp-nonce')
                    && 0 < count($this->errors) ) {
?>
        
        <div id="wpjsp-errors">
            <ul>
                
<?php
                foreach($this->errors as $err)
                        echo '<li>'.$err.'</li>';
?>
                
                <li>Since errors have been detected, nothing was saved.</li>
            </ul>
        </div>
        
<?php
                // Set form's values to the erroneous inputs
                $form_options = $this->post;
            } else {
                // If there are no errors, pull the stored db records
                $form_options = get_option('wpjsp');
                while( $form_options != is_array( $form_options ) )
                    $form_options = unserialize( $form_options );
            }
?>
        
        <form method="post" enctype="multipart/form-data" action="">
            <p class="submit">
                <input type="submit" class="button-primary" tabindex="2" value="<?php _e('Add New Scrollpane Set') ?>" id="wpjsp-add" />
                <input type="submit" class="button-secondary" tabindex="3" value="<?php _e('Reset to Current Saved Settings') ?>" />
            </p>
        </form>
        <form id="wpjsp-form" method="post" enctype="multipart/form-data" action="#">
            <div id="wpjsp-tips">
                <ul>
                    <li>When testing while logged-in, the Wordpress Admin Bar changes the whole-page behavior. Log-out and it will work fine.</li>
                    <li>"H" for Horizontal bar. "V" for Vertical bar. All sizes are in pixels (px)</li>
                    <li>I need someone to re-make this form's html to be displayed on normal 1024x768, with the appropriate styles included</li>
                    <li>The "WinXP" theme does not work yet. It was included so I could maybe get someone to help with it, and another called "OSX"</li>
                    <li>Start simple ;-)</li>
                    <li>
                        <input type="checkbox" name="_wpjsp_mouse" id="mousewheel" <?php if('on'==get_option('wpjsp-mouse')) echo 'checked="checked" '; ?>/>
                        <label for="mousewheel">Mouse Wheel Scrollable</label>
                    </li>
                </ul>
            </div>
            <div class="wpjsp-scrollbars">
            
<?php
            if( $form_options != FALSE )
                echo $this->wpjsp_generate_scrollpane($form_options, false);
            else
                echo $this->wpjsp_generate_scrollpane(null, false);
?>
            
            </div>
            <p class="submit">
                <input type="hidden" value="<?php echo wp_create_nonce('wpjsp-nonce'); ?>" name="wpjsp-nonce" />
                <input type="hidden" name="wpjspaction" value="submit" />
                <input type="submit" class="button-primary form-submit" tabindex="1" value="<?php _e('Save Changes') ?>" />
            </p>
        </form>
    </div>
</div>

<?php
        } // End wpjsp_options_page()

        // Handle the settings page form
        function wpjsp_admin_init() {
            
            // Make sure we are saving, could be resetting
            if( wp_verify_nonce( $_POST['wpjsp-nonce'] , 'wpjsp-nonce' )
                    && $_POST['wpjspaction'] == 'submit' ) {
                
                // Empty the errors array (count=0)
                $this->errors = array();
                
                // Used to make sure there is only one full page
                $fullpage = false;
                $themes = explode('|',$this->wpjsp_get_themes());
                
                // Loop through each scrollpane set for errors
                foreach( $_POST['_wpjsp'] as $id => $settings ) {
                    
                    // Check for the fullpage error
                    if( isset($settings['fullpage']) && $fullpage == false )
                        $fullpage = true;
                    elseif( isset($settings['fullpage']) && $fullpage == true )
                        $this->errors[] = 'The "Whole Page" setting applies to all pages, so there can only be one of them';

                    // Check for the selector error
                    if( $settings['selector'] == '' && $fullpage != true )
                        $this->errors[] = 'You need to include the selector';

                    // Check for the increment error
                    if( isset($settings['increment']) && !isset($settings['incrementlocation']) )
                        $this->errors[] = 'You need to tell me where the increment number can be found';

                    // Check for theme errors
                    if( !isset($settings['theme']) || ( isset($settings['theme'])
                            && $settings['theme'] == '' ) )
                        $this->errors[] = 'You need to select a theme';
                    elseif( isset($settings['theme']) ) {
                        $theme_exists = false;
                        foreach( $themes as $theme_name )
                            if( $theme_name == $settings['theme'] )
                                $theme_exists = true;
                        if( $theme_exists == false && $settings['theme'] != 'customcolors' )
                            $this->errors[] = 'I don\'t know how you did it, but you need to select a theme.';
                    }
                    if( $settings['theme'] == 'customcolors' ) {
                        if( $settings['barhoriz'] == '' || $settings['barvert'] == '' )
                            $this->errors[] = 'Please set the bar widths.';
                        if( isset($settings['arrows']) && $settings['arrowcolor'] == '' )
                            $this->errors[] = 'Please set the arrow colors.';
                        if( $settings['trackcolor'] == '' )
                            $this->errors[] = 'Please select a track color.';
                        if( $settings['dragcolor'] == '' )
                            $this->errors[] = 'Please select a scroll button color.';
                    }

                    // Check for the id/class errors
                    if( !isset($settings['fullpage'])
                            && ( !isset($settings['selectortype'])
                                || $settings['selectortype'] == '' ) )
                        $this->errors[] = 'I don\'t know how you did it, but you need to select a selector type.';

                    // Check for arrow position error
                    if( isset($settings['arrows'])
                            && ( $settings['arrowsvert']==''
                                || $settings['arrowshoriz']=='') )
                        $this->errors[] = 'I don\'t know how you did it, but you need to select an arrow position.';

                } // End error checking foreach()
                
                // Set our post array for updating or for error display
                $this->post = $_POST['_wpjsp'];
                
                // If still submiting and there are no errors, parse the scrollpane sets
                if( isset($_POST['_wpjsp']) && 0 == count($this->errors) ) {
                    foreach( $_POST['_wpjsp'] as $id => $settings ) {
                        $theme_exists = false;
                        // Loop through each scrollpane set
                        foreach( $settings as $setting_name => $setting_value ) {
                            // If there is a theme set, styles are included,
                            // so drop all user inputed styles
                            foreach( $themes as $theme_name )
                                if( $theme_name == $setting_value ) $theme_exists = true;
                                
                            if( $theme_exists == true && $settings['theme'] != 'customcolors' )
                                unset(
                                        $this->post[$id]['caphoriz'],
                                        $this->post[$id]['capvert'],
                                        $this->post[$id]['barhoriz'],
                                        $this->post[$id]['barvert'],
                                        $this->post[$id]['capcolor'],
                                        $this->post[$id]['caphover'],
                                        $this->post[$id]['arrowcolor'],
                                        $this->post[$id]['arrowhover'],
                                        $this->post[$id]['trackcolor'],
                                        $this->post[$id]['trackhover'],
                                        $this->post[$id]['dragcolor'],
                                        $this->post[$id]['draghover']
                                    );

                            // If we are going to use the fullpage,
                            // drop the useless user input
                            if( isset($settings['fullpage']) )
                                unset(
                                        $this->post[$id]['element'],
                                        $this->post[$id]['selector'],
                                        $this->post[$id]['selectortype'],
                                        $this->post[$id]['increment'],
                                        $this->post[$id]['incrementlocation']
                                    );
                            // Check if we are showing arrow buttons
                            if( !isset($settings['arrows'])
                                    || ( isset($settings['arrows'])
                                        && $settings['arrowsvert'] == 'none'
                                        && $settings['arrowshoriz'] == 'none' ) )
                                    unset(
                                            $this->post[$id]['arrowshoriz'],
                                            $this->post[$id]['arrowsvert'],
                                            $this->post[$id]['arrowcolor'],
                                            $this->post[$id]['arrowhover']
                                    );
                            
                            // Unset blank values
                            if( '' == trim($setting_value) )
                                unset($this->post[$id][$setting_name]);

                            // TODO: Unset anything that we don't expect
                            
                        } //End of unset() foreach()
                        
                    } // End scrollpane set foreach
                    
                    // Update the database value
                    update_option( 'wpjsp', serialize($this->post) );
                    update_option( 'wpjsp-mouse',
                            ( isset($_POST['_wpjsp_mouse']) ? $_POST['_wpjsp_mouse'] : 'off' ) );
                    
                    // Sort it by theme for easier processing
                    usort( $this->post, array($this, 'wpjsp_sort') );
                
                    // If there are no errors, Create the relevant JS files
                    $js_head  = 
                        'var $j = jQuery.noConflict();' .
                        '$j(function(){' .
                            'jQuery(document).ready(function($){';
                    $js_foot  = '});});';
                    $js_panes = '';
                    foreach( $this->post as $id => $settings ) {
                        if( isset($settings['fullpage']) ) {
                            $js_page =
                                '$(\'body\').wrapInner(\'<div id="full-page-container"></div>\');' .
                                'var win = $(window);' .
                                'var isResizing = false;' .
                                'win.bind(\'resize\',function(){' .
                                    'if(!isResizing){' .
                                        'isResizing = true;' .
                                        'var container = $(\'#full-page-container\');' .
                                        'container.css({\'width\':1,\'height\':1});' .
                                        'container.css({\'width\':win.width(),\'height\':win.height()});' .
                                        'isResizing = false;' .
                                        'container.jScrollPane(' .
                                        ( (isset($settings['arrows']) || isset($settings['gutterhoriz']) || isset($settings['guttervart']) || isset($settings['autoinit']) ) ? '{' : '' ) .
                                            ( isset($settings['arrows']) ? '\'showArrows\': true,' : '' ) .
                                            ( (isset($settings['arrows']) && $settings['arrowsvert']!='none') ? '\'verticalArrowPositions\':\''.$settings['arrowsvert'].'\',' : '' ) .
                                            ( (isset($settings['arrows']) && $settings['arrowshoriz']!='none') ? '\'horizontalArrowPositions\':\''.$settings['arrowshoriz'].'\',' : '' ) .
                                            ( isset($settings['gutterhoriz']) ? '\'horizontalGutter\':'.$settings['gutterhoriz'].',' : '' ) .
                                            ( isset($settings['guttervert']) ? '\'verticalGutter\':'.$settings['guttervert'].',' : '' ) .
                                            ( isset($settings['autoinit']) ? '\'autoReinitialise\': true' : '' ) .
                                        ( (isset($settings['arrows']) || isset($settings['gutterhoriz']) || isset($settings['guttervart']) || isset($settings['autoinit'])) ? '}' : '' ) .
                                        ');' .
                                    '}' .
                                '}).trigger(\'resize\');' .
                                '$(\'body\').css(\'overflow\',\'hidden\');' .
                                'if($(\'#full-page-container\').width() != win.width()){win.trigger(\'resize\');}';
                        }
                        elseif( !isset($settings['fullpage']) ) {
                            
                            $js_panes .= '$(\'';
                            
                            if( isset($settings['element']) )
                                    $js_panes .= $settings['element'];
                            
                            if( !isset($settings['increment']) ) {
                                if( $settings['selectortype']=='id' )
                                    $js_panes .= '#';
                                elseif( $settings['selectortype']=='class' )
                                    $js_panes .= '.';
                                
                            }
                            elseif( isset($settings['increment']) ) {
                                
                                if( $settings['selectortype']=='id' )
                                    $js_panes .= '[id';
                                elseif( $settings['selectortype']=='class' )
                                    $js_panes .= '[class';
                                
                                if( $settings['incrementlocation']=='end' )
                                    $js_panes .= '^="';
                                elseif( $settings['incrementlocation']=='mid' )
                                    $js_panes .= '*="';
                                elseif( $settings['incrementlocation']=='beg' )
                                    $js_panes .= '$="';
                                
                            }
                            
                            $js_panes .= $settings['selector'];

                            if( isset($settings['increment']) )
                                    $js_panes .= '"]';
                            
                            $js_panes .= '\').jScrollPane(' .
                                ( (isset($settings['arrows']) || isset($settings['gutterhoriz']) || isset($settings['guttervart']) || isset($settings['autoinit']) ) ? '{' : '' ) .
                                    ( isset($settings['arrows']) ? '\'showArrows\': true,' : '' ) .
                                    ( (isset($settings['arrows']) && $settings['arrowsvert']!='none') ? '\'verticalArrowPositions\':\''.$settings['arrowsvert'].'\',' : '' ) .
                                    ( (isset($settings['arrows']) && $settings['arrowshoriz']!='none') ? '\'horizontalArrowPositions\':\''.$settings['arrowshoriz'].'\',' : '' ) .
                                    ( isset($settings['gutterhoriz']) ? '\'horizontalGutter\':'.$settings['gutterhoriz'].',' : '' ) .
                                    ( isset($settings['guttervert']) ? '\'verticalGutter\':'.$settings['guttervert'].',' : '' ) .
                                    ( isset($settings['autoinit']) ? '\'autoReinitialise\': true' : '' ) .
//                                    ( isset($settings['scrollbyx']) ? '\'scrollByX'
                                ( (isset($settings['arrows']) || isset($settings['gutterhoriz']) || isset($settings['guttervart']) || isset($settings['autoinit']) ) ? '}' : '' ) .
                                ');';
                        }
                    } //End foreach
                    $js = $js_head.( isset($js_page) ? $js_page : '' ).$js_panes.$js_foot;
                    $file = fopen(plugin_dir_path(__FILE__).'js/wpjsp.js','w');
					fwrite($file, $js);	fclose($file);
                    
                    // If there are no errors, Create the relevant CSS files
                    $themes = array();
                    foreach( $this->post as $id => $settings )
                        $themes[] = $settings['theme'];
                    $count = array_count_values($themes);
                    $i = 0;
                    $css_custom = array();
                    $ccs_themed = array();
                    foreach( $this->post as $id => $settings ) {
                        
                        // Set the css handle we will use
                        if( isset($settings['fullpage']) )
                            $handle = 'div#full-page-container ';
                        else {
                        
                            $handle = ( isset($settings['element']) ? $settings['element'] : '' );
                        
                            if( isset($settings['increment']) )
                                $handle .= ( $settings['selectortype']=='id' ? '[id' : '' ) .
                                        ( $settings['selectortype']=='class' ? '[class' : '' ) .
                                        ( $settings['incrementlocation']=='end' ? '^="' : '' ) .
                                        ( $settings['incrementlocation']=='mid' ? '*="' : '' ) .
                                        ( $settings['incrementlocation']=='beg' ? '$="' : '' ) .
                                        $settings['selector'] . '"] ';
                            elseif( !isset($settings['increment']) )
                                $handle .= ( $settings['selectortype']=='class' ? '.' : '' ) .
                                            ( $settings['selectortype']=='id' ? '#' : '' ) .
                                            $settings['selector'] . ' ';
                        }
                        
                        if( $settings['theme'] == 'customcolors' ) {
                            
                            foreach( $settings as $name => $value )
                                switch( $name) {
                                    case 'fullpage':
                                        $css_custom[] = 'html{overflow:auto!important;}body{padding:0px !important;}';
                                        break;
                                    case 'capcolor':
                                        $css_custom[] = $handle.'> div > div > .jspCap{background:#'.$value.';}';
                                        break;
                                    case 'caphover':
                                        $css_custom[] = $handle.'> div > div > .jspCap:hover{background:#'.$value.';}';
                                        break;
                                    case 'capvert':
                                        $css_custom[] = $handle.'> div > div > .jspCapTop,'.$handle.'.jspCapBottom{display:block;height:'.$value.'px;}';
                                        break;
                                    case 'caphoriz':
                                        $css_custom[] = $handle.'> div > .jspHorizontalBar .jspCap{display:block;height:100%;width:'.$value.'px;}';
                                        break;
                                    case 'arrowcolor':
                                        $css_custom[] = $handle.'> div > div > .jspArrow{background:#'.$value.';}';
                                        break;
                                    case 'arrowhover':
                                        $css_custom[] = $handle.'> div > div > .jspArrow:hover{background:#'.$value.';}';
                                        break;
                                    case 'trackcolor':
                                        $css_custom[] = $handle.'> div > div > .jspTrack{background:#'.$value.';}';
                                        break;
                                    case 'trackhover':
                                        $css_custom[] = $handle.'> div > div > .jspTrack:hover{background:#'.$value.';}';
                                        break;
                                    case 'dragcolor':
                                        $css_custom[] = $handle.'> div > div > div > .jspDrag{background:#'.$value.';}';
                                        break;
                                    case 'draghover':
                                        $css_custom[] = $handle.'> div > div > div > .jspDrag:hover{background:#'.$value.';}';
                                        break;
                                    case 'barhoriz':
                                        $css_custom[] = $handle.'> div > .jspHorizontalBar{height:'.$value.'px;}';
                                        break;
                                    case 'barvert':
                                        $css_custom[] = $handle.'> div > .jspVerticalBar{width:'.$value.'px;}';
                                        break;
                                    case 'left':
                                        $css_custom[] = $handle.'> div > .jspVerticalBar{left:0;}';
                                        break;
                                } // End Switch CSS
                        } // End If Custom Colors
                        elseif( $settings['theme'] != 'customcolors' ) {
                            
                            if( isset($settings['fullpage']) )
                                $css_themed[] = 'html{overflow:auto!important;}body{padding:0px !important;}';
                            
                            $lines = file( plugin_dir_path(__FILE__).'themes/'.$settings['theme'].'/'.strtolower($settings['theme']).'.css');
                            
                            foreach ($lines as $line_num => $line) {
                                
                                preg_match('/^\.jsp.*/', $line, $match);
                                if( isset($match[0]) ) {
                                    preg_match('/^\.jsp[A-Za-z]*\b/', $match[0], $match2);
                                    switch( $match2[0] ) {
                                        case '.jspHorizontalBar':
                                            $css_themed[] = $handle.' > div > '.$match[0];
                                            break;
                                        case '.jspVerticalBar':
                                            $css_themed[] = $handle.' > div > '.$match[0];
                                            break;
                                        case '.jspTrack':
                                            $css_themed[] = $handle.' > div > div > '.$match[0];
                                            break;
                                        case '.jspDrag':
                                            $css_themed[] = $handle.' > div > div > div > '.$match[0];
                                            break;
                                        case '.jspArrow':
                                            $css_themed[] = $handle.' > div > div > '.$match[0];
                                            break;
                                        case '.jspCap':
                                            $css_themed[] = $handle.' > div > div > '.$match[0];
                                            break;
                                    }
                                }
                                elseif( !isset($match[0]) )
                                    $css_themed[] = trim($line);
                            }
                            if( isset($settings['left']) && $settings['left']=='on' )
                                $css_themed[] = $handle.' > div > .jspVerticalBar{left:0;}';
                            
                            $i = $i + 1;
                            
                            if( $count[$settings['theme']] == $i ) {
                                file_put_contents( plugin_dir_path(__FILE__).'themes/'.$settings['theme'].'/'.strtolower($settings['theme']).'.min.css', $css_themed );
                                $css_themed = array();
                                $i = 0;
                            }
                        }
                    } // End Foreach $this->post
                    
                    if( !empty($css_custom) )
                        file_put_contents(plugin_dir_path(__FILE__).'css/customcolors.css', $css_custom);
                    
                } // End if no errors
                
            } // End if wp_verify_nonce
            
        } // End wpjsp_admin_init()
        
        // Insert the relevant code into the frontend
        function wpjsp_page_init() {
            $themes = array();
            
            $opts = get_option('wpjsp');
            while( $opts != is_array($opts) ) $opts = unserialize($opts);
        
            if( isset($opts) && $opts != '' ) {
                wp_register_script('mousewheel', plugins_url('/js/jquery.mousewheel.min.js',__FILE__), array('jquery') );
                wp_register_script('jscrollpane', plugins_url('/js/jquery.jscrollpane.min.js',__FILE__), array('jquery') );
        
                // Create an array with all the themes we need to load
                foreach( $opts as $id => $set ) $theme[] = $set['theme'];
                array_unique($theme);
        
                wp_register_style('jscrollpane', plugins_url('/css/jquery.jscrollpane.min.css',__FILE__) );
        
                foreach( $theme as $name ) {
                    if( $name == 'customcolors' )
                        wp_register_style($name, plugins_url('/css/'.$name.'.css',__FILE__), array('jscrollpane') );
                    else {
                        wp_register_style($name, plugins_url('/themes/'.$name.'/'.strtolower($name).'.min.css',__FILE__), array('jscrollpane') );
                    }
                    wp_enqueue_style($name);
                }
                
                if( get_option('wpjsp-mouse') == 'on' )
                    $deps = array('jquery','jscrollpane','mousewheel');
                else
                    $deps = array('jquery','jscrollpane');
                wp_register_script('wpjsp', plugins_url('/js/wpjsp.js',__FILE__), $deps);
                wp_enqueue_script('wpjsp');
            }
        }
        
        // Creates html output for AJAX and admin.js adds it
        function wpjsp_generate_scrollpane( $panes = null ) {
            global $wpdb; // this is how you get access to the database
            $html == '';
            if( isset($panes) && is_array($panes) )
                foreach( $panes as $key => $value )
                    $html .= $this->wpjsp_generate_scrollpane_html($key,$value);
            elseif( $panes == null ) {
                if( isset($_POST['wpjspincr']) )
                    $html .= $this->wpjsp_generate_scrollpane_html($_POST['wpjspincr']);
                else
                    $html .= $this->wpjsp_generate_scrollpane_html(0);
            }
            
            if( isset($_POST['wpjspclient']) ) {
                echo $html;
                die(); // this is required to return a proper AJAX result
            } else
                return $html;
        }
        
        function wpjsp_generate_scrollpane_html( $i, $x = null ) {
            // Use to show what is being passed to the html parser
            //if( $x != null ) print_r($x);
            $html = '' .
'<table id="scrollpane'.$i.'" class="widefat">' .
    '<thead>' .
        '<tr valign="top">' .
            '<th class="label">'.__('ScrollPane').'&nbsp;'.($i+1).'</th>' .
            '<th colspan="4"><input type="button" value="Delete" class="button" id="delete'.$i.'"/></th>' .
        '</tr>' .
    '</thead>' .
    '<tbody>' .
        '<tr valign="top">' .
            '<td>' .
                '<label for="element'.$i.'">'.__('Element').'&#58;&nbsp;</label>' .
                '<input type="text" size="16" name="_wpjsp['.$i.'][element]" id="element'.$i.'" ' .
                        ( ( isset($x) && isset($x['element']) ) ? 'value="'.$x['element'].'" ' : '' ) .
                        ( ( isset($x) && isset($x['fullpage']) ) ? 'disabled="disabled" ' : '' ) . '/>' .
            '</td>' .
            '<td>' .
                '<label for="selector'.$i.'">'.__('Selector').'&#58;&nbsp;</label>' .
                '<input type="text" size="16" name="_wpjsp['.$i.'][selector]" id="selector'.$i.'" ' .
                        ( ( isset($x) && isset($x['selector']) ) ? 'value="'.$x['selector'].'" ' : '' ) .
                        ( ( isset($x) && isset($x['fullpage']) ) ? 'disabled="disabled" ' : '' ) . '/>' .
            '</td>' .
            '<td>' .
                '<label for="selectortype'.$i.'">'.__('Selector Type').'&#58;&nbsp;</label>' .
                '<select name="_wpjsp['.$i.'][selectortype]" id="selectortype'.$i.'" ' .
                        ( ( isset($x) && isset($x['fullpage']) ) ? 'disabled="disabled" ' : '' ).'>' .
                    '<option value="class" '.( ( isset($x) && ($x['selectortype']=='class' || !isset($x['selectortype']) ) ) ? 'selected="selected" ' : '' ).'>'.__('Class').'</option>' .
                    '<option value="id" '.( ( isset($x) && $x['selectortype']=='id' ) ? 'selected="selected" ' : '' ).'>'.__('ID').'</option>' .
                '</select>' .
            '</td>' .
            '<td>' .
                '<label for="increment'.$i.'">'.__('Increment').'&#58;</label>' .
                '<input type="checkbox" name="_wpjsp['.$i.'][increment]" id="increment'.$i.'" ' .
                        ( ( isset($x) && isset($x['increment']) ) ? 'checked="checked" ' : '' ) .
                        ( ( isset($x) && isset($x['fullpage']) ) ? 'disabled="disabled" ' : '' ).'/>' .
                '<span>&nbsp;'.__('is at the').'&nbsp;</span>' .
                '<select name="_wpjsp['.$i.'][incrementlocation]" id="incrementlocation'.$i.'"'.( ( (isset($x) && !array_key_exists('increment',$x) || $x==null) ) ? ' disabled="disabled"' : '' ).'>' .
                    '<option value="end" '.( ( isset($x) && ($x['incrementlocation']=='end' || !isset($x['incrementlocation']) ) ) ? 'selected="selected" ' : '' ).'>'.__('End').'</option>' .
                    '<option value="mid" '.( ( isset($x) && $x['incrementlocation']=='mid' ) ? 'selected="selected" ' : '' ).'>'.__('Middle').'</option>' .
                    '<option value="beg" '.( ( isset($x) && $x['incrementlocation']=='beg' ) ? 'selected="selected" ' : '' ).'>'.__('Beginning').'</option>' .
                '</select>' .
            '</td>' .
            '<td>' .
                '<label for="fullpage'.$i.'">'.__('Full Page').'&#58;&nbsp;</label>' .
                '<input type="checkbox" name="_wpjsp['.$i.'][fullpage]" id="fullpage'.$i.'" ' .
                        ( ( isset($x) && isset($x['fullpage']) ) ? 'checked="checked" ' : '' ).'/>&nbsp;&nbsp;&nbsp;&nbsp;' .
                '<label for="arrows'.$i.'">'.__('Show Arrows').'&#58;&nbsp;</label>' .
                '<input type="checkbox" name="_wpjsp['.$i.'][arrows]" id="arrows'.$i.'" ' .
                        ( ( !isset($x) || ( isset($x) && isset($x['arrows']) ) ) ? 'checked="checked" ' : '' ).'/>' .
            '</td>' .
        '</tr>' .
        '<tr valign="top">' .
            '<td>' .
                '<label for="capcolor'.$i.'">'.__('Scrollbar Cap').'&#58;&nbsp;&#35;</label>' .
                '<input type="text" size="3" maxlength="6" class="color-picker" name="_wpjsp['.$i.'][capcolor]" id="capcolor'.$i.'" ' .
                        ( ( isset($x) && isset($x['capcolor']) ) ? 'value="'.$x['capcolor'].'" ' : '' ) .
                        ( ( isset($x) && isset($x['theme']) && $x['theme']!='customcolors' ) ? 'disabled="disabled" ' : '' ).'/>' .
            '</td>' .
            '<td>' .
                '<label for="arrowcolor'.$i.'">'.__('Scrollbar Arrows').'&#58;&nbsp;&#35;</label>' .
                '<input type="text" size="3" maxlength="6" class="color-picker" name="_wpjsp['.$i.'][arrowcolor]" id="arrowcolor'.$i.'" ' .
                        ( ( isset($x) && isset($x['arrowcolor']) ) ? 'value="'.$x['arrowcolor'].'" ' : '' ) .
                        ( ( isset($x) && isset($x['theme']) && ( $x['theme']!='customcolors' || ( $x['theme']=='customcolors' && !isset($x['arrows']) ) ) ) ? 'disabled="disabled" ' : '' ).'/>' .
            '</td>' .
            '<td>' .
                '<label for="trackcolor'.$i.'">'.__('Scrollbar Track').'&#58;&nbsp;&#35;</label>' .
                '<input type="text" size="3" maxlength="6" class="color-picker" name="_wpjsp['.$i.'][trackcolor]" id="trackcolor'.$i.'" ' .
                        ( ( isset($x) && isset($x['trackcolor']) ) ? 'value="'.$x['trackcolor'].'" ' : '' ) .
                        ( ( isset($x) && isset($x['theme']) && $x['theme']!='customcolors' ) ? 'disabled="disabled" ' : '' ).'/>' .
            '</td>' .
            '<td>' .
                '<label for="dragcolor'.$i.'">'.__('Scrollbar Drag').'&#58;&nbsp;&#35;</label>' .
                '<input type="text" size="3" maxlength="6" class="color-picker" name="_wpjsp['.$i.'][dragcolor]" id="dragcolor'.$i.'" ' .
                        ( ( isset($x) && isset($x['dragcolor']) ) ? 'value="'.$x['dragcolor'].'" ' : '' ) .
                        ( ( isset($x) && isset($x['theme']) && $x['theme']!='customcolors' ) ? 'disabled="disabled" ' : '' ).'/>' .
            '<td>' .
                '<label for="arrowsvert'.$i.'">'.__('Vertical Arrow Position').'&#58;&nbsp;</label>' .
                '<select name="_wpjsp['.$i.'][arrowsvert]" id="arrowsvert'.$i.'" '.
                    ( ( isset($x) && !isset($x['arrows']) ) ? 'disabled="disabled" ' : '' ).'>' .
                    '<option value="split"'.( ( isset($x) && ( $x['arrowsvert']=='split' || !isset($x['arrowsvert']) ) ) ? ' selected="selected"' : '' ).'>Split</option>' .
                    '<option value="before"'.( ( isset($x) && $x['arrowsvert']=='before' ) ? ' selected="selected"' : '' ).'>Before</option>' .
                    '<option value="after"'.( ( isset($x) && $x['arrowsvert']=='after' ) ? ' selected="selected"' : '' ).'>After</option>' .
                    '<option value="os"'.( ( isset($x) && $x['arrowsvert']=='os' ) ? ' selected="selected"' : '' ).'>OS</option>' .
                    '<option value="none"'.( ( isset($x) && $x['arrowsvert']=='none' ) ? ' selected="selected"' : '' ).'>None</option>' .
                '</select>' .
            '</td>' .
        '</tr>' .
        '<tr valign="top">' .
            '<td>' .
                '<label for="caphover'.$i.'">'.__('Scrollbar Cap:hover').'&#58;&nbsp;&#35;</label>' .
                '<input type="text" size="3" maxlength="6" class="color-picker" name="_wpjsp['.$i.'][caphover]" id="caphover'.$i.'" ' .
                        ( ( isset($x) && isset($x['caphover']) ) ? 'value="'.$x['caphover'].'" ' : '' ) .
                        ( ( !isset($x) || ( isset($x) && isset($x['theme']) && ( $x['theme']!='customcolors' || ( $x['theme']=='customcolors' && !isset($x['capcolor']) ) ) ) ) ? 'disabled="disabled" ' : '' ).'/>' .
            '</td>' .
            '<td>' .
                '<label for="arrowhover'.$i.'">'.__('Scrollbar Arrows:hover').'&#58;&nbsp;&#35;</label>' .
                '<input type="text" size="3" maxlength="6" class="color-picker" name="_wpjsp['.$i.'][arrowhover]" id="arrowhover'.$i.'" ' .
                        ( ( isset($x) && isset($x['arrowhover']) ) ? 'value="'.$x['arrowhover'].'" ' : '' ) . //need more checking for arrows
                        ( ( !isset($x) || ( isset($x) && isset($x['theme']) && ( $x['theme']!='customcolors' || ( $x['theme']=='customcolors' && !isset($x['arrows']) ) ) ) ) ? 'disabled="disabled" ' : '' ).'/>' .
            '</td>' .
            '<td>' .
                '<label for="trackhover'.$i.'">'.__('Scrollbar Track:hover').'&#58;&nbsp;&#35;</label>' .
                '<input type="text" size="3" maxlength="6" class="color-picker" name="_wpjsp['.$i.'][trackhover]" id="trackhover'.$i.'" ' .
                        ( ( isset($x) && isset($x['trackhover']) ) ? 'value="'.$x['trackhover'].'" ' : '' ) .
                        ( ( !isset($x) || ( isset($x) && isset($x['theme']) && ( $x['theme']!='customcolors' || ( $x['theme']=='customcolors' && !isset($x['trackcolor']) ) ) ) ) ? 'disabled="disabled" ' : '' ).'/>' .
            '</td>' .
            '<td>' .
                '<label for="draghover'.$i.'">'.__('Scrollbar Drag:hover').'&#58;&nbsp;&#35;</label>' .
                '<input type="text" size="3" maxlength="6" class="color-picker" name="_wpjsp['.$i.'][draghover]" id="draghover'.$i.'" ' .
                        ( ( isset($x) && isset($x['draghover']) ) ? 'value="'.$x['draghover'].'" ' : '' ) .
                        ( ( !isset($x) || ( isset($x) && isset($x['theme']) && ( $x['theme']!='customcolors' || ( $x['theme']=='customcolors' && !isset($x['dragcolor']) ) ) ) ) ? 'disabled="disabled" ' : '' ).'/>' .
            '</td>' .
            '<td>' .
                '<label for="arrowshoriz'.$i.'">'.__('Horizontal Arrow Position').'&#58;&nbsp;</label>' .
                '<select name="_wpjsp['.$i.'][arrowshoriz]" id="arrowshoriz'.$i.'" '.
                    ( ( isset($x) && !isset($x['arrows']) ) ? 'disabled="disabled" ' : '' ).'>' .
                    '<option value="split"'.( ( isset($x) && ( $x['arrowshoriz']=='split' || !isset($x['arrowshoriz']) ) ) ? ' selected="selected"' : '' ).'>Split</option>' .
                    '<option value="before"'.( ( isset($x) && $x['arrowshoriz']=='before' ) ? ' selected="selected"' : '' ).'>Before</option>' .
                    '<option value="after"'.( ( isset($x) && $x['arrowshoriz']=='after' ) ? ' selected="selected"' : '' ).'>After</option>' .
                    '<option value="os"'.( ( isset($x) && $x['arrowshoriz']=='os' ) ? ' selected="selected"' : '' ).'>OS</option>' .
                    '<option value="none"'.( ( isset($x) && $x['arrowshoriz']=='none' ) ? ' selected="selected"' : '' ).'>None</option>' .
                '</select>' .
            '</td>' .
        '</tr>' .
        '<tr valign="top">' .
            '<td>' .
                '<label>'.__('Cap Length').'&#58;&nbsp;</label>' .
                '<label for="caphoriz'.$i.'">'.__('H').'</label>' .
                '<input type="text" size="1" maxlength="3" class="numbers" name="_wpjsp['.$i.'][caphoriz]" id="caphoriz'.$i.'" ' .
                        ( ( isset($x) && isset($x['caphoriz']) ) ? 'value="'.$x['caphoriz'].'" ' : '' ) .
                        ( ( !isset($x) || ( isset($x) && isset($x['theme']) && ( $x['theme']!='customcolors' || ( $x['theme']=='customcolors' && !isset($x['capcolor']) ) ) ) ) ? 'disabled="disabled" ' : '' ).'/>' .
                '<label for="capvert'.$i.'">'.__('V').'</label>' .
                '<input type="text" size="1" maxlength="3" class="numbers" name="_wpjsp['.$i.'][capvert]" id="capvert'.$i.'" ' .
                        ( ( isset($x) && isset($x['capvert']) ) ? 'value="'.$x['capvert'].'" ' : '' ) .
                        ( ( !isset($x) || ( isset($x) && isset($x['theme']) && ( $x['theme']!='customcolors' || ( $x['theme']=='customcolors' && !isset($x['capcolor']) ) ) ) ) ? 'disabled="disabled" ' : '' ).'/>' .
            '</td>' .
            '<td>' .
                '<label>'.__('Bar Width').'&#58;&nbsp;</label>' .
                '<label for="barhoriz'.$i.'">'.__('H').'</label>' .
                '<input type="text" size="1" maxlength="2" class="numbers" name="_wpjsp['.$i.'][barhoriz]" id="barhoriz'.$i.'" ' .
                        ( ( isset($x) && isset($x['barhoriz']) ) ? 'value="'.$x['barhoriz'].'" ' : '' ) .
                        ( ( isset($x) && isset($x['theme']) && $x['theme']!='customcolors' ) ? 'disabled="disabled" ' : '' ).'/>' .
                '<label for="barvert'.$i.'">'.__('V').'</label>' .
                '<input type="text" size="1" maxlength="2" class="numbers" name="_wpjsp['.$i.'][barvert]" id="barvert'.$i.'" ' .
                        ( ( isset($x) && isset($x['barvert']) ) ? 'value="'.$x['barvert'].'" ' : '' ) .
                        ( ( isset($x) && isset($x['theme']) && $x['theme']!='customcolors' ) ? 'disabled="disabled" ' : '' ).'/>' .
            '</td>' .
            '<td>' .
                '<label>'.__('Gutter Length').'&#58;&nbsp;</label>' .
                '<label for="gutterhoriz'.$i.'">'.__('H').'</label>' .
                '<input type="text" size="1" maxlength="3" class="numbers" name="_wpjsp['.$i.'][gutterhoriz]" id="gutterhoriz'.$i.'" ' .
                        ( ( isset($x) && isset($x['gutterhoriz']) ) ? 'value="'.$x['gutterhoriz'].'" ' : '' ).'/>' .
                '<label for="guttervert'.$i.'">'.__('V').'</label>' .
                '<input type="text" size="1" maxlength="3" class="numbers" name="_wpjsp['.$i.'][guttervert]" id="guttervert'.$i.'" ' .
                        ( ( isset($x) && isset($x['guttervert']) ) ? 'value="'.$x['guttervert'].'" ' : '' ).'/>' .
            '</td>' .
            '<td>' .
                '<label for="autoinit'.$i.'">'.__('Automatically Reinitialize').'&#58;&nbsp;</label>' .
                '<input type="checkbox" name="_wpjsp['.$i.'][autoinit]" id="autoinit'.$i.'" ' .
                        ( ( isset($x) && isset($x['autoinit']) ) ? 'checked="checked" ' : '' ).'/>' .
            '</td>' .
            '<td>' .
                '<label for="left'.$i.'">'.__('Move scrollbar to the left').'&#58;&nbsp;</label>' .
                '<input type="checkbox" name="_wpjsp['.$i.'][left]" id="left'.$i.'" ' .
                        ( ( isset($x) && isset($x['left']) ) ? 'checked="checked" ' : '' ).'/>' .
            '</td>' .
        '</tr>' .
        '<tr valign="top">' .
            '<td colspan="5">' .
                '<label>'.__('Theme').'&#58;&nbsp;</label>';
            
            $dir = realpath(plugin_dir_path(__FILE__).'themes');
            if( scandir($dir) != false )
                foreach( scandir($dir) as $item ) {
                    if( is_dir(realpath($dir.'/'.$item)) && !($item=='.'||$item=='..') ) {
                        $html .= '<label for="'.strtolower($item).$i.'">'.__($item).'</label>';
                        $html .= '<input type="radio" value="'.$item.'" name="_wpjsp['.$i.'][theme]" id="'.strtolower($item).$i.'" ' .
                                ( (isset($x) && $x['theme']==$item) ? 'checked="checked" ' : '' ).'/>&nbsp;&nbsp;&nbsp;';
                    }
                }
            
$html .=        '<label for="colors'.$i.'">'.__('Custom Colors').'&nbsp;</label>' .
                '<input type="radio" value="customcolors" name="_wpjsp['.$i.'][theme]" id="colors'.$i.'" ' .
                        ( ( !isset($x) || ( isset($x) && $x['theme']=='customcolors' ) ) ? 'checked="checked" ' : '' ).'/>' .
            '</td>' .
        '</tr>' .
    '</tbody>' .
'</table>';
            
            return $html;
        }
        
        function wpjsp_get_themes() {
            $dir = realpath( plugin_dir_path(__FILE__).'themes' );
            $themes = '';
            if( scandir($dir) != false ) {
                foreach( scandir($dir) as $item )
                    if( is_dir( realpath($dir.'/'.$item) ) &&
                            !( $item=='.' || $item=='..' ) )
                        $themes .= $item . '|';
                $themes = rtrim($themes,'|');
            }
            if( isset($_POST['wpjspclient']) ) {
                echo $themes;
                die();
            }
            else
                return $themes;
        }
        
        function wpjsp_sort($a,$b) {
            $key = 'theme';
            return strnatcmp($a[$key], $b[$key]);
        }
    }
}
                
// If WP jScrollPane is defined, run it!
if( class_exists('wp_jscrollpane') ) $wpjsp = new wp_jscrollpane();
?>