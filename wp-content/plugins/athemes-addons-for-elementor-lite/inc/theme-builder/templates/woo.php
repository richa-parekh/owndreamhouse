<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

get_header( 'shop' );

if ( have_posts() ) {
	the_post();
}

do_action( 'athemes_addons_do_content' );

get_footer( 'shop' );