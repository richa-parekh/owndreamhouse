<?php
namespace aThemes_Addons\Widgets;

use Elementor\Plugin;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Utils;
use Elementor\Widget_Base;
use Elementor\Group_Control_Image_Size;
use Elementor\Icons_Manager;
use Elementor\Control_Media;
use Elementor\Repeater;
use Elementor\Group_Control_Css_Filter;
use aThemes_Addons\Traits\Upsell_Section_Trait;


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor team member widget.
 *
 * @since 1.0.0
 */
class Team_Member extends Widget_Base {
	use Upsell_Section_Trait;
	
	/**
	 * Get widget name.
	 *
	 * Retrieve tabs widget name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'athemes-addons-team-member';
	}

	/**
	 * Get widget title.
	 *
	 * Retrieve widget title.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return __( 'Team member', 'athemes-addons-for-elementor-lite' );
	}

	/**
	 * Get widget categories.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return [ 'athemes-addons-elements' ];
	}

	/**
	 * Get help URL.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Help URL.
	 */
	public function get_custom_help_url() {
		return 'https://docs.athemes.com/article/team-member/';
	}

	/**
	 * Get widget icon.
	 *
	 * Retrieve tabs widget icon.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-person aafe-elementor-icon';
	}

	/**
	 * Enqueue styles.
	 */
	public function get_style_depends() {
		return [ $this->get_name() . '-styles' ];
	}   

	/**
	 * Get widget keywords.
	 *
	 * Retrieve the list of keywords the widget belongs to.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return [ 'team', 'member', 'employee', 'athemes', 'addons', 'athemes addons' ];
	}

	/**
	 * Register team member widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 3.1.0
	 * @access protected
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'section_image',
			[
				'label' => esc_html__( 'Image', 'athemes-addons-for-elementor-lite' ),
			]
		);

		$this->add_control(
			'image',
			array(
				'label'   => esc_html__( 'Image', 'athemes-addons-for-elementor-lite' ),
				'type'    => Controls_Manager::MEDIA,
				'default' => array(
					'url' => Utils::get_placeholder_image_src(),
				),
			)
		);

		$this->add_control(
			'object_fit',
			array(
				'label'                => __( 'Image Fit', 'athemes-addons-for-elementor-lite' ),
				'type'                 => Controls_Manager::SELECT,
				'options'              => array(
					'cover'   => __( 'Cover', 'athemes-addons-for-elementor-lite' ),
					'contain' => __( 'Contain', 'athemes-addons-for-elementor-lite' ),
				),
				'default'              => 'cover',
				'selectors_dictionary' => array(
					'cover'   => 'object-fit:cover;width:100%;',
					'contain' => 'object-fit:contain;height:auto',
				),
				'selectors'            => array(
					'{{WRAPPER}} .team-member-image img' => '{{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'image_height',
			array(
				'label'      => __( 'Image Height', 'athemes-addons-for-elementor-lite' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'vh' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 1000,
					),
					'vh' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'default'    => array(
					'size' => 350,
					'unit' => 'px',
				),
				'selectors'  => array(
					'{{WRAPPER}} .team-member-image img' => 'height: {{SIZE}}{{UNIT}};',
				),
				'condition'  => array(
					'object_fit' => 'cover',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			array(
				'name'    => 'thumb',
				'default' => 'large',
			)
		);


		$this->start_controls_tabs( 'image_hover_style' );

		$this->start_controls_tab(
			'normal',
			array(
				'label' => __( 'Normal', 'athemes-addons-for-elementor-lite' ),
			)
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			array(
				'name'     => 'image_css_filters',
				'selector' => '{{WRAPPER}} .team-member-image img',
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'hover',
			array(
				'label' => __( 'Hover', 'athemes-addons-for-elementor-lite' ),
			)
		);

		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			array(
				'name'     => 'image_hover_css_filters',
				'selector' => '{{WRAPPER}} .team-member-image img:hover',
			)
		);

		$this->add_control(
			'image_hover_effect',
			array(
				'label'   => __( 'Image Hover Effect', 'athemes-addons-for-elementor-lite' ),
				'type'    => Controls_Manager::SELECT,
				'options' => array(
					'none'      => __( 'None', 'athemes-addons-for-elementor-lite' ),
					'zoomin'    => __( 'Zoom in', 'athemes-addons-for-elementor-lite' ),
					'opacity'   => __( 'Opacity', 'athemes-addons-for-elementor-lite' ),
					'rotate'    => __( 'Zoom & Rotate', 'athemes-addons-for-elementor-lite' ),
				),
				'default' => 'none',
			)
		);      

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'image_hover_transition_duration',
			array(
				'label'      => __( 'Transition Duration', 'athemes-addons-for-elementor-lite' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'ms' ),
				'range'      => array(
					'ms' => array(
						'min' => 0,
						'max' => 10000,
					),
				),
				'default'    => array(
					'size' => 300,
					'unit' => 'ms',
				),
				'selectors'  => array(
					'{{WRAPPER}} .team-member-image img' => 'transition-duration: {{SIZE}}{{UNIT}};',
				),
				'separator'  => 'before',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_team_member_content',
			[
				'label' => esc_html__( 'Content', 'athemes-addons-for-elementor-lite' ),
			]
		);

		$this->add_control(
			'name',
			array(
				'label'       => esc_html__( 'Name', 'athemes-addons-for-elementor-lite' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'John Doe', 'athemes-addons-for-elementor-lite' ),
				'placeholder' => esc_html__( 'John Doe', 'athemes-addons-for-elementor-lite' ),
				'label_block' => true,
				'dynamic'     => array(
					'active' => true,
				),
				'separator'   => 'before',
			)
		);

		$this->add_control(
			'title_tag',
			array(
				'label'     => __( 'Name HTML Tag', 'athemes-addons-for-elementor-lite' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => array(
					'h1'   => __( 'H1', 'athemes-addons-for-elementor-lite' ),
					'h2'   => __( 'H2', 'athemes-addons-for-elementor-lite' ),
					'h3'   => __( 'H3', 'athemes-addons-for-elementor-lite' ),
					'h4'   => __( 'H4', 'athemes-addons-for-elementor-lite' ),
					'h5'   => __( 'H5', 'athemes-addons-for-elementor-lite' ),
					'h6'   => __( 'H6', 'athemes-addons-for-elementor-lite' ),
					'div'  => __( 'div', 'athemes-addons-for-elementor-lite' ),
					'span' => __( 'span', 'athemes-addons-for-elementor-lite' ),
					'p'    => __( 'p', 'athemes-addons-for-elementor-lite' ),
				),
				'default'   => 'h3',
			)
		);

		$this->add_control(
			'short_bio',
			array(
				'label'       => esc_html__( 'Short bio', 'athemes-addons-for-elementor-lite' ),
				'type'        => Controls_Manager::WYSIWYG,
				'placeholder' => esc_html__( 'Short bio', 'athemes-addons-for-elementor-lite' ),
				'label_block' => true,
				'dynamic'     => array(
					'active' => true,
				),
				'separator'   => 'before',
			)
		);

		$this->add_control(
			'position',
			array(
				'label'       => esc_html__( 'Position', 'athemes-addons-for-elementor-lite' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'CEO', 'athemes-addons-for-elementor-lite' ),
				'label_block' => true,
				'dynamic'     => array(
					'active' => true,
				),
				'separator'   => 'before',
			)
		);

		$this->add_control(
			'experience',
			array(
				'label'       => esc_html__( 'Experience', 'athemes-addons-for-elementor-lite' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( '10 years experience', 'athemes-addons-for-elementor-lite' ),
				'label_block' => true,
				'dynamic'     => array(
					'active' => true,
				),
			)
		);      

		$this->add_responsive_control(
			'alignment',
			[
				'label' => __( 'Alignment', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => __( 'Left', 'athemes-addons-for-elementor-lite' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'athemes-addons-for-elementor-lite' ),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => __( 'Right', 'athemes-addons-for-elementor-lite' ),
						'icon' => 'eicon-text-align-right',
					],
				],
				'default' => 'left',
				'selectors' => [
					'{{WRAPPER}} .athemes-addons-team-member' => 'text-align: {{VALUE}};',
				],
				'prefix_class' => 'team-member-align-',
			]
		);      

		$this->end_controls_section();      

		$this->start_controls_section(
			'section_team_member_social',
			[
				'label' => esc_html__( 'Social', 'athemes-addons-for-elementor-lite' ),
			]
		);

		$this->add_control(
			'show_social',
			array(
				'label'        => esc_html__( 'Show Social Icons', 'athemes-addons-for-elementor-lite' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'athemes-addons-for-elementor-lite' ),
				'label_off'    => esc_html__( 'Hide', 'athemes-addons-for-elementor-lite' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		//$repeater->add_control choose icon
		$repeater = new Repeater();

		$repeater->add_control(
			'icon',
			array(
				'label'   => esc_html__( 'Icon', 'athemes-addons-for-elementor-lite' ),
				'type'    => Controls_Manager::ICONS,
				'default' => array(
					'value'   => 'fas fa-facebook-f',
					'library' => 'fa-solid',
				),
			)
		);

		$repeater->add_control(
			'link',
			array(
				'label'       => esc_html__( 'Link', 'athemes-addons-for-elementor-lite' ),
				'type'        => Controls_Manager::URL,
				'placeholder' => esc_html__( 'https://your-link.com', 'athemes-addons-for-elementor-lite' ),
				'default'     => array(
					'url' => '#',
				),
				'show_external' => true,
				'separator'     => 'before',
			)
		);

		$this->add_control(
			'social',
			array(
				'label'       => esc_html__( 'Social', 'athemes-addons-for-elementor-lite' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array(
					array(
						'icon' => array(
							'value'   => 'fab fa-facebook-f',
							'library' => 'fa-brands',
						),
						'link' => array(
							'url' => '#',
						),
					),
					array(
						'icon' => array(
							'value'   => 'fab fa-twitter',
							'library' => 'fa-brands',
						),
						'link' => array(
							'url' => '#',
						),
					),
					array(
						'icon' => array(
							'value'   => 'fab fa-linkedin-in',
							'library' => 'fa-brands',
						),
						'link' => array(
							'url' => '#',
						),
					),
				),
				'title_field' => '{{{ elementor.helpers.renderIcon( this, icon, {}, "i", "panel" ) || \'<i class="{{ icon }}" aria-hidden="true"></i>\' }}}',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_card_style',
			[
				'label' => esc_html__( 'Card', 'athemes-addons-for-elementor-lite' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'card_padding',
			array(
				'label'      => __( 'Padding', 'athemes-addons-for-elementor-lite' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .athemes-addons-team-member .team-member-inner' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'card_border_radius',
			array(
				'label'      => __( 'Border Radius', 'athemes-addons-for-elementor-lite' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .athemes-addons-team-member .team-member-inner' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'separator'  => 'before',
			)
		);

		$this->start_controls_tabs( 'card_style' );

		$this->start_controls_tab(
			'card_normal',
			array(
				'label' => __( 'Normal', 'athemes-addons-for-elementor-lite' ),
			)
		);

		$this->add_control(
			'card_background_color',
			array(
				'label'     => __( 'Background Color', 'athemes-addons-for-elementor-lite' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array(
					'{{WRAPPER}} .athemes-addons-team-member .team-member-inner' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'card_border',
				'selector' => '{{WRAPPER}} .athemes-addons-team-member .team-member-inner',
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'card_box_shadow',
				'selector' => '{{WRAPPER}} .athemes-addons-team-member .team-member-inner',
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'card_hover',
			array(
				'label' => __( 'Hover', 'athemes-addons-for-elementor-lite' ),
			)
		);

		$this->add_control(
			'card_hover_background_color',
			array(
				'label'     => __( 'Background Color', 'athemes-addons-for-elementor-lite' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array(
					'{{WRAPPER}} .athemes-addons-team-member .team-member-inner:hover' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'card_hover_border_color',
			array(
				'label'     => __( 'Border Color', 'athemes-addons-for-elementor-lite' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .athemes-addons-team-member .team-member-inner:hover' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'card_box_shadow_hover',
				'selector' => '{{WRAPPER}} .athemes-addons-team-member .team-member-inner:hover',
			)
		);      

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		$this->start_controls_section(
			'section_image_style',
			[
				'label' => esc_html__( 'Image', 'athemes-addons-for-elementor-lite' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'image_width',
			array(
				'label'      => __( 'Image Width', 'athemes-addons-for-elementor-lite' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( '%' ),
				'range'      => array(
					'%' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'default'    => array(
					'size' => 100,
					'unit' => '%',
				),
				'selectors'  => array(
					'{{WRAPPER}} .athemes-addons-team-member .team-member-image' => 'max-width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'image_border',
				'selector' => '{{WRAPPER}} .athemes-addons-team-member .team-member-image',
			)
		);

		$this->add_control(
			'image_border_radius',
			array(
				'label'      => __( 'Border Radius', 'athemes-addons-for-elementor-lite' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .athemes-addons-team-member .team-member-image' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'separator'  => 'after',
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'image_box_shadow',
				'selector' => '{{WRAPPER}} .athemes-addons-team-member .team-member-image',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_name_style',
			[
				'label' => esc_html__( 'Name', 'athemes-addons-for-elementor-lite' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'name_color',
			array(
				'label'     => __( 'Color', 'athemes-addons-for-elementor-lite' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .athemes-addons-team-member .team-member-name' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'name_typography',
				'selector' => '{{WRAPPER}} .athemes-addons-team-member .team-member-name',
			)
		);

		$this->add_responsive_control(
			'name_margin',
			array(
				'label'      => __( 'Margin', 'athemes-addons-for-elementor-lite' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .athemes-addons-team-member .team-member-name' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'separator'  => 'before',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_position_style',
			[
				'label' => esc_html__( 'Position', 'athemes-addons-for-elementor-lite' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'position_color',
			array(
				'label'     => __( 'Color', 'athemes-addons-for-elementor-lite' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#A6A9B0',
				'selectors' => array(
					'{{WRAPPER}} .athemes-addons-team-member .team-member-position' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'position_typography',
				'selector' => '{{WRAPPER}} .athemes-addons-team-member .team-member-position',
			)
		);      

		$this->add_responsive_control(
			'position_margin',
			array(
				'label'      => __( 'Margin', 'athemes-addons-for-elementor-lite' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .athemes-addons-team-member .team-member-position' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'separator'  => 'before',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_experience_style',
			[
				'label' => esc_html__( 'Experience', 'athemes-addons-for-elementor-lite' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'experience_color',
			array(
				'label'     => __( 'Color', 'athemes-addons-for-elementor-lite' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#A6A9B0',
				'selectors' => array(
					'{{WRAPPER}} .athemes-addons-team-member .team-member-experience' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'experience_typography',
				'selector' => '{{WRAPPER}} .athemes-addons-team-member .team-member-experience',
			)
		);

		$this->add_responsive_control(
			'experience_margin',
			array(
				'label'      => __( 'Margin', 'athemes-addons-for-elementor-lite' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .athemes-addons-team-member .team-member-experience' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'separator'  => 'before',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_short_bio_style',
			[
				'label' => esc_html__( 'Short Bio', 'athemes-addons-for-elementor-lite' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'short_bio_color',
			array(
				'label'     => __( 'Color', 'athemes-addons-for-elementor-lite' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#A6A9B0',
				'selectors' => array(
					'{{WRAPPER}} .athemes-addons-team-member .team-member-short-bio' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'short_bio_typography',
				'selector' => '{{WRAPPER}} .athemes-addons-team-member .team-member-short-bio',
			)
		);

		$this->add_responsive_control(
			'short_bio_margin',
			array(
				'label'      => __( 'Margin', 'athemes-addons-for-elementor-lite' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .athemes-addons-team-member .team-member-short-bio' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'separator'  => 'before',
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_social_style',
			[
				'label' => esc_html__( 'Social', 'athemes-addons-for-elementor-lite' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'social_icon_size',
			array(
				'label'      => __( 'Icon Size', 'athemes-addons-for-elementor-lite' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 10,
						'max' => 100,
					),
				),
				'default'    => array(
					'size' => 16,
					'unit' => 'px',
				),
				'selectors'  => array(
					'{{WRAPPER}} .athemes-addons-team-member .team-member-social a' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .athemes-addons-team-member' => '--social-icon-size: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'social_icon_container_size',
			array(
				'label'      => __( 'Icon Container Size', 'athemes-addons-for-elementor-lite' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 10,
						'max' => 150,
					),
				),
				'default'    => array(
					'size' => 40,
					'unit' => 'px',
				),
				'selectors'  => array(
					'{{WRAPPER}} .athemes-addons-team-member' => '--social-icon-container-size: {{SIZE}}{{UNIT}};',
				),
			)
		);

		// border radius.
		$this->add_responsive_control(
			'social_icon_border_radius',
			array(
				'label'      => __( 'Border Radius', 'athemes-addons-for-elementor-lite' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'em', 'px', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .athemes-addons-team-member .team-member-social a' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
				'separator'  => 'before',
			)
		);

		$this->add_responsive_control(
			'social_icon_spacing',
			array(
				'label'      => __( 'Icon Spacing', 'athemes-addons-for-elementor-lite' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 50,
					),
				),
				'default'    => array(
					'size' => 12,
					'unit' => 'px',
				),
				'selectors'  => array(
					'{{WRAPPER}} .athemes-addons-team-member .team-member-social' => 'gap: {{SIZE}}{{UNIT}};',
				),
				'separator'  => 'before',
			)
		);  
		
		$this->start_controls_tabs( 'social_icon_style' );

		$this->start_controls_tab(
			'social_icon_normal',
			array(
				'label' => __( 'Normal', 'athemes-addons-for-elementor-lite' ),
			)
		);

		$this->add_control(
			'social_icon_color',
			array(
				'label'     => esc_html__( 'Icon Color', 'athemes-addons-for-elementor-lite' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .athemes-addons-team-member .team-member-social a' => 'fill: {{VALUE}};color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'social_icon_background_color',
			array(
				'label'     => esc_html__( 'Background Color', 'athemes-addons-for-elementor-lite' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .athemes-addons-team-member .team-member-social a' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'social_icon_hover',
			array(
				'label' => __( 'Hover', 'athemes-addons-for-elementor-lite' ),
			)
		);

		$this->add_control(
			'social_icon_hover_color',
			array(
				'label'     => esc_html__( 'Icon Hover Color', 'athemes-addons-for-elementor-lite' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .athemes-addons-team-member .team-member-social a:hover' => 'fill: {{VALUE}};color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'social_icon_hover_background_color',
			array(
				'label'     => esc_html__( 'Background Hover Color', 'athemes-addons-for-elementor-lite' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .athemes-addons-team-member .team-member-social a:hover' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();      

		//Register upsell section
		$this->register_upsell_section();
	}

	/**
	 * Render tabs widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		$this->add_render_attribute( 'athemes-addons-team-member', 'class', 'athemes-addons-team-member' );
		?>
		<div <?php $this->print_render_attribute_string( 'athemes-addons-team-member' ); ?>>
			<div class="team-member-inner">
				<div class="team-member-image" data-effect="<?php echo esc_attr( $settings['image_hover_effect'] ); ?>">
					<?php Group_Control_Image_Size::print_attachment_image_html( $settings, 'thumb', 'image' ); ?>
				</div>
				<div class="team-member-content">
					<?php $settings['title_tag'] = athemes_addons_validate_html_tag( $settings['title_tag'] ); ?>
					<<?php echo tag_escape( $settings['title_tag'] ); ?> class="team-member-name"><?php echo esc_html( $settings['name'] ); ?></<?php echo tag_escape( $settings['title_tag'] ); ?>>
					<?php if ( ! empty( $settings['position'] ) ) : ?>
						<div class="team-member-position"><?php echo esc_html( $settings['position'] ); ?></div>
					<?php endif; ?>
					<?php if ( ! empty( $settings['experience'] ) ) : ?>
						<div class="team-member-experience"><?php echo esc_html( $settings['experience'] ); ?></div>
					<?php endif; ?>
					<?php if ( ! empty( $settings['short_bio'] ) ) : ?>
						<div class="team-member-short-bio"><?php echo wp_kses_post( $settings['short_bio'] ); ?></div>
					<?php endif; ?>
					<?php if ( 'yes' === $settings['show_social'] && ! empty( $settings['social'] ) ) : ?>
						<div class="team-member-social">
							<?php foreach ( $settings['social'] as $item ) : ?>
								<a href="<?php echo esc_url( $item['link']['url'] ); ?>" target="<?php echo esc_attr( $item['link']['is_external'] ? '_blank' : '_self' ); ?>">
									<?php Icons_Manager::render_icon( $item['icon'], [ 'aria-hidden' => 'true' ] ); ?>
								</a>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render tabs widget output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since 2.9.0
	 * @access protected
	 */
	/**
	 * Render icon list widget output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function content_template() { 
	}
}
Plugin::instance()->widgets_manager->register( new Team_Member() );