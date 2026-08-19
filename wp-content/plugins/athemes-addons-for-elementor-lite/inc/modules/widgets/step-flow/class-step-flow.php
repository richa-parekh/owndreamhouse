<?php
namespace aThemes_Addons\Widgets;

use Elementor\Repeater;
use Elementor\Plugin;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Utils;
use Elementor\Widget_Base;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Text_Shadow;
use aThemes_Addons\Traits\Upsell_Section_Trait;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Step Flow block
 *
 * @since 1.0.0
 */
class Step_Flow extends Widget_Base {
	use Upsell_Section_Trait;
	
	/**
	 * Get widget name.
	 *
	 * Retrieve widget name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'athemes-addons-step-flow';
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
		return esc_html__( 'Step Flow', 'athemes-addons-for-elementor-lite' );
	}

	/**
	 * Get widget icon.
	 *
	 * Retrieve icon list widget icon.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-form-vertical aafe-elementor-icon';
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
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return [ 'step', 'flow', 'step flow', 'athemes', 'addons', 'athemes addons' ];
	}

	/**
	 * Get widget categories.
	 *
	 * Retrieve the list of categories the icon list widget belongs to.
	 *
	 * Used to determine where to display the widget in the editor.
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
		return 'https://docs.athemes.com/article/step-flow/';
	}

	/**
	 * Register icon list widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function register_controls() {
		// Content Tab
		$this->start_controls_section(
			'section_steps',
			[
				'label' => esc_html__( 'Steps', 'athemes-addons-for-elementor-lite' ),
			]
		);

		$repeater = new Repeater();

		$repeater->start_controls_tabs(
			'step_tabs',
			[
				'label' => esc_html__( 'Step Settings', 'athemes-addons-for-elementor-lite' ),
			]
		);

		// Start Content Tab
		$repeater->start_controls_tab(
			'tab_content',
			[
				'label' => esc_html__( 'Content', 'athemes-addons-for-elementor-lite' ),
			]
		);

		$repeater->add_control(
			'step_title',
			[
				'label' => esc_html__( 'Step Title', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Step Title', 'athemes-addons-for-elementor-lite' ),
				'label_block' => true,
			]
		);

		$repeater->add_control(
			'description',
			[
				'label' => esc_html__( 'Description', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::TEXTAREA,
				'default' => esc_html__( 'Step description goes here. Add details about this step.', 'athemes-addons-for-elementor-lite' ),
				'label_block' => true,
			]
		);

		$repeater->add_control(
			'icon_type',
			[
				'label' => esc_html__( 'Icon Type', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'icon' => [
						'title' => esc_html__( 'Icon', 'athemes-addons-for-elementor-lite' ),
						'icon' => 'eicon-favorite',
					],
					'image' => [
						'title' => esc_html__( 'Image', 'athemes-addons-for-elementor-lite' ),
						'icon' => 'eicon-image',
					],
				],
				'default' => 'icon',
				'toggle' => false,
			]
		);

		$repeater->add_control(
			'selected_icon',
			[
				'label' => esc_html__( 'Icon', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::ICONS,
				'default' => [
					'value' => 'fas fa-check',
					'library' => 'fa-solid',
				],
				'condition' => [
					'icon_type' => 'icon',
				],
			]
		);

		$repeater->add_control(
			'image',
			[
				'label' => esc_html__( 'Image', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::MEDIA,
				'default' => [
					'url' => Utils::get_placeholder_image_src(),
				],
				'condition' => [
					'icon_type' => 'image',
				],
			]
		);

		$repeater->add_control(
			'step_url',
			[
				'label' => esc_html__( 'Link', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::URL,
				'placeholder' => esc_html__( 'https://your-link.com', 'athemes-addons-for-elementor-lite' ),
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$repeater->end_controls_tab();

		// Start Style Tab
		$repeater->start_controls_tab(
			'tab_style',
			[
				'label' => esc_html__( 'Style', 'athemes-addons-for-elementor-lite' ),
			]
		);

		// Icon style options
		$repeater->add_control(
			'custom_icon_style',
			[
				'label' => esc_html__( 'Custom Icon Style', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'athemes-addons-for-elementor-lite' ),
				'label_off' => esc_html__( 'No', 'athemes-addons-for-elementor-lite' ),
				'default' => '',
			]
		);

		$repeater->add_control(
			'icon_color',
			[
				'label' => esc_html__( 'Icon Color', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}} .step-flow-icon i' => 'color: {{VALUE}};',
					'{{WRAPPER}} {{CURRENT_ITEM}} .step-flow-icon svg' => 'fill: {{VALUE}};',
				],
				'condition' => [
					'custom_icon_style' => 'yes',
					'icon_type' => 'icon',
				],
			]
		);

		$repeater->add_control(
			'icon_bg_color',
			[
				'label' => esc_html__( 'Icon Background', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}} .step-flow-icon-wrapper' => 'background-color: {{VALUE}};',
				],
				'condition' => [
					'custom_icon_style' => 'yes',
				],
			]
		);

		// Card style options
		$repeater->add_control(
			'custom_card_style',
			[
				'label' => esc_html__( 'Custom Card Style', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'athemes-addons-for-elementor-lite' ),
				'label_off' => esc_html__( 'No', 'athemes-addons-for-elementor-lite' ),
				'default' => '',
				'separator' => 'before',
			]
		);

		$repeater->add_control(
			'card_bg_color',
			[
				'label' => esc_html__( 'Background Color', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}} .step-flow-item-inner' => 'background-color: {{VALUE}};',
				],
				'condition' => [
					'custom_card_style' => 'yes',
				],
			]
		);

		$repeater->add_control(
			'card_border_color',
			[
				'label' => esc_html__( 'Border Color', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}} .step-flow-item-inner' => 'border-color: {{VALUE}};',
				],
				'condition' => [
					'custom_card_style' => 'yes',
				],
			]
		);

		$repeater->add_control(
			'title_color',
			[
				'label' => esc_html__( 'Title Color', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}} .step-flow-title' => 'color: {{VALUE}};',
				],
				'condition' => [
					'custom_card_style' => 'yes',
				],
			]
		);

		$repeater->add_control(
			'description_color',
			[
				'label' => esc_html__( 'Description Color', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}} .step-flow-description' => 'color: {{VALUE}};',
				],
				'condition' => [
					'custom_card_style' => 'yes',
				],
			]
		);

		$repeater->end_controls_tab();

		$repeater->end_controls_tabs();

		$this->add_control(
			'steps',
			[
				'label' => esc_html__( 'Steps', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => [
					[
						'step_title' => esc_html__( 'Discovery Phase', 'athemes-addons-for-elementor-lite' ),
						'description' => esc_html__( 'We analyze your requirements and create a detailed project roadmap.', 'athemes-addons-for-elementor-lite' ),
						'icon_type' => 'icon',
						'selected_icon' => [
							'value' => 'fas fa-pencil-alt',
							'library' => 'fa-solid',
						],
					],
					[
						'step_title' => esc_html__( 'Design & Development', 'athemes-addons-for-elementor-lite' ),
						'description' => esc_html__( 'Our team brings your vision to life with responsive, modern design.', 'athemes-addons-for-elementor-lite' ),
						'icon_type' => 'icon',
						'selected_icon' => [
							'value' => 'fas fa-desktop',
							'library' => 'fa-solid',
						],
					],
					[
						'step_title' => esc_html__( 'Launch & Support', 'athemes-addons-for-elementor-lite' ),
						'description' => esc_html__( 'Your website goes live with ongoing maintenance and optimization.', 'athemes-addons-for-elementor-lite' ),
						'icon_type' => 'icon',
						'selected_icon' => [
							'value' => 'fas fa-rocket',
							'library' => 'fa-solid',
						],
					],
				],
				'title_field' => '{{{ step_title }}}',
				'max_items' => 6,
			]
		);

		$this->end_controls_section();

		// Layout Settings Section
		$this->start_controls_section(
			'section_layout',
			[
				'label' => esc_html__( 'Settings', 'athemes-addons-for-elementor-lite' ),
			]
		);

		$this->add_control(
			'layout',
			[
				'label' => esc_html__( 'Layout', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'horizontal',
				'options' => [
					'horizontal'            => esc_html__( 'Horizontal', 'athemes-addons-for-elementor-lite' ),
					'vertical'              => esc_html__( 'Vertical', 'athemes-addons-for-elementor-lite' ),
				],
				'prefix_class' => 'step-flow-layout-',
			]
		);

		$this->add_responsive_control(
			'step_gap',
			[
				'label' => esc_html__( 'Gap Between Steps', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
						'step' => 1,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 30,
				],
				'selectors' => [
					'{{WRAPPER}}.step-flow-layout-horizontal .step-flow-connector' => 'margin-left: {{SIZE}}{{UNIT}};margin-right: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}}.step-flow-layout-vertical .step-flow-connector' => 'margin-top: {{SIZE}}{{UNIT}};margin-bottom: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}}.step-flow-layout-zigzag .step-flow-connector' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
				'separator' => 'after',
			]
		);

		$this->add_responsive_control(
			'alignment',
			[
				'label' => esc_html__( 'Alignment', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => esc_html__( 'Left', 'athemes-addons-for-elementor-lite' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'athemes-addons-for-elementor-lite' ),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => esc_html__( 'Right', 'athemes-addons-for-elementor-lite' ),
						'icon' => 'eicon-text-align-right',
					],
				],
				'default' => 'center',
				'selectors' => [
					'{{WRAPPER}} .step-flow-item' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'title_tag',
			[
				'label' => esc_html__( 'Step Title HTML Tag', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'h4',
				'options' => [
					'h1' => 'H1',
					'h2' => 'H2', 
					'h3' => 'H3',
					'h4' => 'H4',
					'h5' => 'H5',
					'h6' => 'H6',
					'div' => 'div',
					'span' => 'span',
					'p' => 'p',
				],
			]
		);

		$this->add_control(
			'connector_style',
			[
				'label' => esc_html__( 'Connector Style', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'line',
				'options' => [
					'line' => esc_html__( 'Straight Line', 'athemes-addons-for-elementor-lite' ),
					'zigzag' => esc_html__( 'Zigzag Line', 'athemes-addons-for-elementor-lite' ),
					'wiggly' => esc_html__( 'Wiggly Line', 'athemes-addons-for-elementor-lite' ),
					'arrow' => esc_html__( 'Arrow Line', 'athemes-addons-for-elementor-lite' ),
					'none' => esc_html__( 'None', 'athemes-addons-for-elementor-lite' ),
				],
			]
		);

		$this->add_control(
			'show_numbers',
			[
				'label' => esc_html__( 'Show Step Numbers', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'label_on' => esc_html__( 'Show', 'athemes-addons-for-elementor-lite' ),
				'label_off' => esc_html__( 'Hide', 'athemes-addons-for-elementor-lite' ),
				'separator' => 'before',
			]
		);

		$this->add_control(
			'numbers_position',
			[
				'label' => esc_html__( 'Numbers Position', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => esc_html__( 'Left', 'athemes-addons-for-elementor-lite' ),
						'icon' => 'eicon-h-align-left',
					],
					'right' => [
						'title' => esc_html__( 'Right', 'athemes-addons-for-elementor-lite' ),
						'icon' => 'eicon-h-align-right',
					],
				],
				'default' => 'left',
				'condition' => [
					'show_numbers' => 'yes',
				],
				'selectors_dictionary' => [
					'right' => 'left:auto;right:-5px;',
				],
				'selectors' => [
					'{{WRAPPER}} .step-flow-number' => '{{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		// BOX STYLES
		$this->start_controls_section(
			'section_card_style',
			[
				'label' => esc_html__( 'Card', 'athemes-addons-for-elementor-lite' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'box_padding',
			[
				'label' => esc_html__( 'Padding', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .step-flow-item-inner' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->start_controls_tabs( 'box_style_tabs' );

		$this->start_controls_tab(
			'box_style_normal',
			[
				'label' => esc_html__( 'Normal', 'athemes-addons-for-elementor-lite' ),
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'box_background',
				'label' => esc_html__( 'Background', 'athemes-addons-for-elementor-lite' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .step-flow-item-inner',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'box_border',
				'label' => esc_html__( 'Border', 'athemes-addons-for-elementor-lite' ),
				'selector' => '{{WRAPPER}} .step-flow-item-inner',
			]
		);

		$this->add_control(
			'box_border_radius',
			[
				'label' => esc_html__( 'Border Radius', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .step-flow-item-inner' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'box_shadow',
				'label' => esc_html__( 'Box Shadow', 'athemes-addons-for-elementor-lite' ),
				'selector' => '{{WRAPPER}} .step-flow-item-inner',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'box_style_hover',
			[
				'label' => esc_html__( 'Hover', 'athemes-addons-for-elementor-lite' ),
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'box_background_hover',
				'label' => esc_html__( 'Background', 'athemes-addons-for-elementor-lite' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .step-flow-item:hover .step-flow-item-inner, {{WRAPPER}} .step-flow-item-active .step-flow-item-inner',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'box_border_hover',
				'label' => esc_html__( 'Border', 'athemes-addons-for-elementor-lite' ),
				'selector' => '{{WRAPPER}} .step-flow-item:hover .step-flow-item-inner, {{WRAPPER}} .step-flow-item-active .step-flow-item-inner',
			]
		);

		$this->add_control(
			'box_border_radius_hover',
			[
				'label' => esc_html__( 'Border Radius', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .step-flow-item:hover .step-flow-item-inner, {{WRAPPER}} .step-flow-item-active .step-flow-item-inner' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'box_shadow_hover',
				'label' => esc_html__( 'Box Shadow', 'athemes-addons-for-elementor-lite' ),
				'selector' => '{{WRAPPER}} .step-flow-item:hover .step-flow-item-inner, {{WRAPPER}} .step-flow-item-active .step-flow-item-inner',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		// CONNECTOR STYLE
		$this->start_controls_section(
			'section_connector_style',
			[
				'label' => esc_html__( 'Connector', 'athemes-addons-for-elementor-lite' ),
				'tab' => Controls_Manager::TAB_STYLE,
				'condition' => [
					'connector_style!' => 'none',
				],
			]
		);

		$this->add_control(
			'connector_color',
			[
				'label' => esc_html__( 'Color', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .step-flow-connector-line .step-flow-connector' => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .step-flow-connector' => 'color: {{VALUE}};',
					'{{WRAPPER}} .step-flow-connector-line .step-flow-connector:before, {{WRAPPER}} .step-flow-connector-line .step-flow-connector:after' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'connector_thickness',
			[
				'label' => esc_html__( 'Thickness', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 1,
						'max' => 10,
						'step' => 1,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 2,
				],
				'selectors' => [
					'{{WRAPPER}} .step-flow-connector' => 'height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}}.step-flow-layout-vertical .step-flow-connector' => 'width: {{SIZE}}{{UNIT}}; height: 30px;',
					'{{WRAPPER}} .step-flow-connector:before, {{WRAPPER}} .step-flow-connector:after' => 'height: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'connector_style' => 'line',
				],
			]
		);

		$this->add_control(
			'connector_max_width',
			[
				'label' => esc_html__( 'Max Width', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 200,
						'step' => 1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .step-flow-connector' => 'max-width: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'connector_style!' => 'none',
				],
			]
		);

		$this->end_controls_section();

		// ICON/IMAGE STYLE
		$this->start_controls_section(
			'section_icon_style',
			[
				'label' => esc_html__( 'Icon/Image', 'athemes-addons-for-elementor-lite' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'icon_size',
			[
				'label' => esc_html__( 'Size', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 10,
						'max' => 100,
						'step' => 1,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 30,
				],
				'selectors' => [
					'{{WRAPPER}} .step-flow-icon i' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .step-flow-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'image_size',
			[
				'label' => esc_html__( 'Image Size', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 10,
						'max' => 300,
						'step' => 1,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 80,
				],
				'selectors' => [
					'{{WRAPPER}} .step-flow-image img' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'icon_padding',
			[
				'label' => esc_html__( 'Padding', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .step-flow-icon-wrapper, {{WRAPPER}} .step-flow-image-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->start_controls_tabs( 'icon_style_tabs' );

		$this->start_controls_tab(
			'icon_style_normal',
			[
				'label' => esc_html__( 'Normal', 'athemes-addons-for-elementor-lite' ),
			]
		);

		$this->add_control(
			'icon_color',
			[
				'label' => esc_html__( 'Icon Color', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .step-flow-icon i' => 'color: {{VALUE}};',
					'{{WRAPPER}} .step-flow-icon svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'icon_background_color',
			[
				'label' => esc_html__( 'Background Color', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .step-flow-icon-wrapper, {{WRAPPER}} .step-flow-image-wrapper' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'icon_border',
				'label' => esc_html__( 'Border', 'athemes-addons-for-elementor-lite' ),
				'selector' => '{{WRAPPER}} .step-flow-icon-wrapper, {{WRAPPER}} .step-flow-image-wrapper',
			]
		);

		$this->add_control(
			'icon_border_radius',
			[
				'label' => esc_html__( 'Border Radius', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .step-flow-icon-wrapper, {{WRAPPER}} .step-flow-image-wrapper' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'icon_style_hover',
			[
				'label' => esc_html__( 'Hover', 'athemes-addons-for-elementor-lite' ),
			]
		);

		$this->add_control(
			'icon_color_hover',
			[
				'label' => esc_html__( 'Icon Color', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .step-flow-item:hover .step-flow-icon i' => 'color: {{VALUE}};',
					'{{WRAPPER}} .step-flow-item:hover .step-flow-icon svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'icon_background_color_hover',
			[
				'label' => esc_html__( 'Background Color', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .step-flow-item:hover .step-flow-icon-wrapper, {{WRAPPER}} .step-flow-item:hover .step-flow-image-wrapper' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'icon_border_hover',
				'label' => esc_html__( 'Border', 'athemes-addons-for-elementor-lite' ),
				'selector' => '{{WRAPPER}} .step-flow-item:hover .step-flow-icon-wrapper, {{WRAPPER}} .step-flow-item:hover .step-flow-image-wrapper',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

		// TITLE STYLE
		$this->start_controls_section(
			'section_title_style',
			[
				'label' => esc_html__( 'Title', 'athemes-addons-for-elementor-lite' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'title_color',
			[
				'label' => esc_html__( 'Color', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .step-flow-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'title_color_hover',
			[
				'label' => esc_html__( 'Hover Color', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .step-flow-item:hover .step-flow-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'title_typography',
				'selector' => '{{WRAPPER}} .step-flow-title',
			]
		);

		$this->add_responsive_control(
			'title_spacing',
			[
				'label' => esc_html__( 'Spacing', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 50,
						'step' => 1,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 15,
				],
				'selectors' => [
					'{{WRAPPER}} .step-flow-title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// DESCRIPTION STYLE
		$this->start_controls_section(
			'section_description_style',
			[
				'label' => esc_html__( 'Description', 'athemes-addons-for-elementor-lite' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'description_color',
			[
				'label' => esc_html__( 'Color', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .step-flow-description' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'description_color_hover',
			[
				'label' => esc_html__( 'Hover Color', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .step-flow-item:hover .step-flow-description' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'description_typography',
				'selector' => '{{WRAPPER}} .step-flow-description',
			]
		);

		$this->end_controls_section();

		// STEP NUMBER STYLE
		$this->start_controls_section(
			'section_number_style',
			[
				'label' => esc_html__( 'Step Number', 'athemes-addons-for-elementor-lite' ),
				'tab' => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_numbers' => 'yes',
				],
			]
		);

		$this->add_responsive_control(
			'number_position_top',
			[
				'label' => esc_html__( 'Top Position', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => -50,
						'max' => 50,
						'step' => 1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .step-flow-number' => 'top: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'number_position_left',
			[
				'label' => esc_html__( 'Left Position', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => -50,
						'max' => 50,
						'step' => 1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .step-flow-number' => 'left: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'numbers_position' => 'left',
				],
			]
		);

		$this->add_responsive_control(
			'number_position_right',
			[
				'label' => esc_html__( 'Right Position', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => -50,
						'max' => 50,
						'step' => 1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .step-flow-number' => 'right: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'numbers_position' => 'right',
				],
			]
		);

		$this->add_responsive_control(
			'number_size',
			[
				'label' => esc_html__( 'Size', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 10,
						'max' => 100,
						'step' => 1,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .step-flow-number' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'number_typography',
				'selector' => '{{WRAPPER}} .step-flow-number',
			]
		);

		$this->start_controls_tabs( 'number_style_tabs' );

		$this->start_controls_tab(
			'number_style_normal',
			[
				'label' => esc_html__( 'Normal', 'athemes-addons-for-elementor-lite' ),
			]
		);

		$this->add_control(
			'number_color',
			[
				'label' => esc_html__( 'Color', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .step-flow-number' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'number_background_color',
			[
				'label' => esc_html__( 'Background Color', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .step-flow-number' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'number_border',
				'label' => esc_html__( 'Border', 'athemes-addons-for-elementor-lite' ),
				'selector' => '{{WRAPPER}} .step-flow-number',
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'number_style_hover',
			[
				'label' => esc_html__( 'Hover', 'athemes-addons-for-elementor-lite' ),
			]
		);

		$this->add_control(
			'number_color_hover',
			[
				'label' => esc_html__( 'Color', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .step-flow-item:hover .step-flow-number' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'number_background_color_hover',
			[
				'label' => esc_html__( 'Background Color', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .step-flow-item:hover .step-flow-number' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'number_border_hover',
				'label' => esc_html__( 'Border', 'athemes-addons-for-elementor-lite' ),
				'selector' => '{{WRAPPER}} .step-flow-item:hover .step-flow-number',
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'number_border_radius',
			[
				'label' => esc_html__( 'Border Radius', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .step-flow-number' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'separator' => 'before',
			]
		);

		$this->end_controls_section();

		// Register upsell section
		$this->register_upsell_section();
	}

	/**
	 * Render the widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		$this->add_render_attribute( 'wrapper', 'class', [
			'step-flow-wrapper',
			'step-flow-connector-' . esc_attr( $settings['connector_style'] ),
		] );

		if ( 'yes' === $settings['show_numbers'] ) {
			$this->add_render_attribute( 'wrapper', 'class', 'step-flow-show-numbers' );
		}
		?>
		<div <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
			<div class="step-flow-container">
				<?php
				$count = 1;
				$total_steps = count( $settings['steps'] );
				
				foreach ( $settings['steps'] as $index => $item ) :
					$step_key = $this->get_repeater_setting_key( 'step_item', 'steps', $index );
					$this->add_render_attribute( $step_key, 'class', [
						'step-flow-item',
						'step-flow-item-' . $count,
						'elementor-repeater-item-' . $item['_id'],
					] );

					// Handle step link
					$has_link = ! empty( $item['step_url']['url'] );
					if ( $has_link ) {
						$this->add_link_attributes( 'link_' . $index, $item['step_url'] );
					}
					?>
					<div <?php $this->print_render_attribute_string( $step_key ); ?>>
						<div class="step-flow-item-inner">

							<div class="step-flow-icon-wrapper">
							<?php if ( 'yes' === $settings['show_numbers'] ) : ?>
								<div class="step-flow-number"><?php echo esc_html( $count ); ?></div>
							<?php endif; ?>
								<?php if ( 'icon' === $item['icon_type'] && ! empty( $item['selected_icon']['value'] ) ) : ?>
									<div class="step-flow-icon">
										<?php \Elementor\Icons_Manager::render_icon( $item['selected_icon'], [ 'aria-hidden' => 'true' ] ); ?>
									</div>
								<?php elseif ( 'image' === $item['icon_type'] && ! empty( $item['image']['url'] ) ) : ?>
									<div class="step-flow-image">
										<img src="<?php echo esc_url( $item['image']['url'] ); ?>" alt="<?php echo esc_attr( $item['step_title'] ); ?>">
									</div>
								<?php endif; ?>
							</div>

							<div class="step-flow-content">
								<?php if ( ! empty( $item['step_title'] ) ) : 
									if ( $has_link ) : ?>
										<a <?php $this->print_render_attribute_string( 'link_' . $index ); ?>>
									<?php endif; ?>
										<?php $settings['title_tag'] = athemes_addons_validate_html_tag( $settings['title_tag'] ); ?>
										<<?php echo tag_escape( $settings['title_tag'] ); ?> class="step-flow-title"><?php echo esc_html( $item['step_title'] ); ?></<?php echo tag_escape( $settings['title_tag'] ); ?>>
									<?php if ( $has_link ) : ?>
										</a>
									<?php endif; ?>
								<?php endif; ?>

								<?php if ( ! empty( $item['description'] ) ) : ?>
									<div class="step-flow-description"><?php echo wp_kses_post( $item['description'] ); ?></div>
								<?php endif; ?>
							</div>
						</div>
					</div>
					
					<?php if ( $count < $total_steps && 'none' !== $settings['connector_style'] ) : ?>
						<div class="step-flow-connector">
							<?php if ( 'line' !== $settings['connector_style'] ) : ?>
								<?php echo $this->get_connector_svg( $settings ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php endif; ?>
						</div>
					<?php endif; ?>
					
					<?php
					$count++;
				endforeach;
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render the widget output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function content_template() {}

	/**
	 * Get connector SVG
	 */
	public function get_connector_svg( $settings ) {
		$svg = '';
		
		switch ( $settings['connector_style'] ) {
			case 'zigzag':
				$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 10" preserveAspectRatio="none">
					<path d="M0,5 L12.5,0 L25,10 L37.5,0 L50,10 L62.5,0 L75,10 L87.5,0 L100,5" 
						stroke="currentColor" 
						fill="none" 
						stroke-width="1" 
						stroke-linecap="round" 
						stroke-linejoin="round"/>
				</svg>';
				break;
			case 'wiggly':
				$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 20" preserveAspectRatio="none">
					<path d="M0,10 C5,0 10,20 15,10 C20,0 25,20 30,10 C35,0 40,20 45,10 C50,0 55,20 60,10 C65,0 70,20 75,10 C80,0 85,20 90,10 C95,0 100,20 100,10 C105,0 110,20 115,10" 
						stroke="currentColor" 
						fill="none" 
						stroke-width="1" 
						stroke-linecap="round" 
						stroke-linejoin="round"/>
				</svg>';
				break;
			case 'arrow':
				$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 20" preserveAspectRatio="none">
					<path d="M0,10 L100,10 M90,5 L100,10 L90,15" 
						stroke="currentColor" 
						fill="none" 
						stroke-width="1" 
						stroke-linecap="round" 
						stroke-linejoin="round"/>
				</svg>';
				break;
			case 'none':
				$svg = '';
				break;
		}

		return $svg;
	}
}

Plugin::instance()->widgets_manager->register( new Step_Flow() );