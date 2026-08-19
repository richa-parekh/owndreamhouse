<?php
add_action( 'wp_enqueue_scripts', 'astra_enqueue_styles' );

function astra_enqueue_styles() {
	wp_enqueue_style( 
		'astra-style', 
		get_stylesheet_uri()
	);
}