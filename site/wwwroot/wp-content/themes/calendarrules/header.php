<?php
/*
 WARNING: This file is part of the core Genesis framework. DO NOT edit
 this file under any circumstances. Please do all modifications
 in the form of a child theme.
 */

/**
 * Handles the header structure.
 *
 * This file is a core Genesis file and should not be edited.
 *
 * @category Genesis
 * @package  Templates
 * @author   StudioPress
 * @license  http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link     http://www.studiopress.com/themes/genesis
 */

do_action( 'genesis_doctype' );
do_action( 'genesis_title' );
do_action( 'genesis_meta' );

wp_head(); /** we need this for plugins **/
?>
</head>
<!--
<link href="jquery/css/normalize.css?v=11410177095" rel="stylesheet" type="text/css">
  <link href="jquery/css/webflow.css?v=11410177095" rel="stylesheet" type="text/css">
  <link href="jquery/css/cr-main-4.webflow.css?v=11410177095" rel="stylesheet" type="text/css">-->
<body <?php body_class(); ?>>
<?php

if($_SESSION['access_token'] == '')
 {
   if($_SESSION['CheckAccess']!="NoGmail")
		{
			session_destroy();
		}
 }
do_action( 'genesis_before' );
?>
<div id="wrap">
<?php
do_action( 'genesis_before_header' );
do_action( 'genesis_header' );
do_action( 'genesis_after_header' );

echo '<div id="inner">';
genesis_structural_wrap( 'inner' );
