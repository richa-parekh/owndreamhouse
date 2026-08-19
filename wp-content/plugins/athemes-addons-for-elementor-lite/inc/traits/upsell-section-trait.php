<?php
namespace aThemes_Addons\Traits;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

trait Upsell_Section_Trait {

	/**
	 * Register button controls.
	 */
	protected function register_upsell_section( $args = [] ) {

		if ( defined( 'ATHEMES_AFE_PRO_VERSION' ) ) { // no need to show upsell if pro version is active
			return;
		}

		$default_args = [
			'title'         => esc_html__( 'Unlock More Features', 'athemes-addons-for-elementor-lite' ),
			'description'   => esc_html__( 'Take your site to the next level with aThemes Addons Pro.', 'athemes-addons-for-elementor-lite' ),
			'description2'  => esc_html__( 'You\'ll get access to:', 'athemes-addons-for-elementor-lite' ),
			'button_text'   => esc_html__( 'Upgrade to Pro', 'athemes-addons-for-elementor-lite' ),
			'button_url'    => athemes_addons_admin_upgrade_link( 'https://athemes.com/addons', array( 'utm_source' => 'widget_upsell', 'utm_medium' => 'button', 'utm_campaign' => 'Addons' ), 'widget-upsell-section' ),
		];
	
		$args = wp_parse_args( $args, $default_args );

		$this->start_controls_section(
			'section_athemes_addons_upsell',
			[
				'label' => esc_html( $args['title'] ),
			]
		);

		$this->add_control(
			'athemes_addons_upsell_button',
			[
				'type' => Controls_Manager::RAW_HTML,
				'raw' => 
				'<div class="aafe-editor-upsell-section">'
				. '<p>' . esc_html( $args['description'] ) . '</p>'
				. '<p>' . esc_html( $args['description2']). '</p>'
				. '<ul>' . 
					'<li><i class="eicon-check"></i>' . esc_html__( 'Many More Widgets &amp; Extensions', 'athemes-addons-for-elementor-lite' ) . '</li>' .
					'<li><i class="eicon-check"></i>' . esc_html__( 'Theme Builder System', 'athemes-addons-for-elementor-lite' ) . '</li>' .
					'<li><i class="eicon-check"></i>' . esc_html__( 'Premium email support', 'athemes-addons-for-elementor-lite' ) . '</li>' .
					'<li><i class="eicon-check"></i>' . esc_html__( 'Regular updates', 'athemes-addons-for-elementor-lite' ) . '</li>' .
					'<li><i class="eicon-check"></i><a href="' . esc_url( $args['button_url'] ) . '" target="_blank">' . esc_html__( '&hellip; and many more premium features', 'athemes-addons-for-elementor-lite' ) . '</a></li>'
				. '</ul>'
				. '<a href="' . esc_url( $args['button_url'] ) . '" class="elementor-button elementor-button-success" target="_blank">' . esc_html( $args['button_text'] ) . '</a>'
				. '</div>',
			]
		);

		$this->end_controls_section();
	}
}