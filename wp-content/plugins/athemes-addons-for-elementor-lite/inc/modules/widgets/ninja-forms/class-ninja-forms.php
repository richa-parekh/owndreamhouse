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
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Text_Shadow;
use aThemes_Addons\Traits\Upsell_Section_Trait;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor icon list widget.
 *
 * Elementor widget that displays a bullet list with any chosen icons and texts.
 *
 * @since 1.0.0
 */
class Ninja_Forms extends Widget_Base {
	use Upsell_Section_Trait;
	
	/**
	 * Get widget name.
	 *
	 * Retrieve icon list widget name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'athemes-addons-ninjaforms';
	}

	/**
	 * Get widget title.
	 *
	 * Retrieve icon list widget title.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return __( 'Ninja Forms', 'athemes-addons-for-elementor-lite' );
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
		return 'eicon-form-horizontal aafe-elementor-icon';
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
		return [ 'contact', 'form', 'contact form', 'ninja', 'ninja forms', 'ninjaforms', 'athemes', 'addons', 'athemes addons' ];
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
		return 'https://docs.athemes.com/article/ninja-forms/';
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

        if ( !class_exists( 'Ninja_Forms' ) ) {
            $this->start_controls_section(
                'missing_plugin_notice_section',
                [
                    'label' => __( 'Missing plugin: Ninja Forms', 'athemes-addons-for-elementor-lite' ),
                ]
            );

            $this->add_control(
                'missing_plugin_notice',
                [
                    'type'            => Controls_Manager::RAW_HTML,
                    'raw'             => __('Please install <a href="plugin-install.php?s=ninja+forms&tab=search&type=term" target="_blank">Ninja Forms</a> to use this widget.',
                        'athemes-addons-for-elementor-lite'),
                ]
            );

            $this->end_controls_section();
            return;
        }

		$this->start_controls_section(
			'section_forms',
			[
				'label' => __( 'Ninja Forms', 'athemes-addons-for-elementor-lite' ),
			]
		);

		//raw text
		$this->add_control(
			'contact_forms_notice',
			[
				'type' => Controls_Manager::RAW_HTML,
				'raw' => __( 'Please note that Ninja Forms are not display in the Elementor editor, only on the frontend.', 'athemes-addons-for-elementor-lite' ),
			]
		);

		$this->add_control(
			'contact_title_heading',
			[
				'label' => __( 'Title', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::HEADING,
			]
		);

		$this->add_control(
			'form_title',
			[
				'label' => __( 'Form title', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
				'placeholder' => '',
			]
		);  
		
		$this->add_control(
			'form_title_tag',
			[
				'label' => __('Form title tag', 'athemes-addons-for-elementor-lite'),
				'type' => Controls_Manager::SELECT,
				'default' => 'h2',
				'options' => [
					'h1'    => __('H1', 'athemes-addons-for-elementor-lite'),
					'h2'    => __('H2', 'athemes-addons-for-elementor-lite'),
					'h3'    => __('H3', 'athemes-addons-for-elementor-lite'),
					'h4'    => __('H4', 'athemes-addons-for-elementor-lite'),
					'h5'    => __('H5', 'athemes-addons-for-elementor-lite'),
					'h6'    => __('H6', 'athemes-addons-for-elementor-lite'),
					'span'  => __('Span', 'athemes-addons-for-elementor-lite'),
					'p'     => __('P', 'athemes-addons-for-elementor-lite'),
					'div'   => __('Div', 'athemes-addons-for-elementor-lite'),
				],
			]
		);  

		$this->add_control(
			'contact_forms_heading',
			[
				'label' => __( 'Form', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'contact_forms',
			[
				'label' => __('Select your form', 'athemes-addons-for-elementor-lite'),
				'type' => Controls_Manager::SELECT,
				'default' => 0,
				'options' => $this->forms(),
			]
		);  
	
		$this->add_responsive_control(
			'align',
			[
				'label' => esc_html__( 'Form alignment', 'athemes-addons-for-elementor-lite' ),
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
				'default' => '',
				'selectors_dictionary' => [
					'left'      => '-webkit-box-pack:start;-ms-flex-pack:start;justify-content:flex-start',
					'center'    => '-webkit-box-pack:center;-ms-flex-pack:center;justify-content:center',
					'right'     => '-webkit-box-pack:end;-ms-flex-pack:end;justify-content:flex-end',
				],
				'selectors' => [
					'{{WRAPPER}} .athemes-addons-contact-form' => '{{VALUE}}',
				],
			]
		);  
		
	
		$this->add_responsive_control(
			'content_align',
			[
				'label' => esc_html__( 'Content alignment', 'athemes-addons-for-elementor-lite' ),
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
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .athemes-addons-contact-form' => 'text-align: {{VALUE}}',
				],
			]
		);          

		$this->add_responsive_control(
			'size',
			[
				'label' => __( 'Form width', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 2000,
						'step' => 1,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
						'step' => 1,
					],                  
				],
				'devices' => [ 'desktop', 'tablet', 'mobile' ],
				'desktop_default' => [
					'size' => 370,
					'unit' => 'px',
				],
				'tablet_default' => [
					'size' => 370,
					'unit' => 'px',
				],
				'mobile_default' => [
					'size' => 370,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .athemes-addons-form-inner' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);      

		$this->add_control(
			'contact_button_heading',
			[
				'label' => __( 'Button', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'stretch_button',
			[
				'label' => __( 'Stretch button?', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on'  => __( 'Yes', 'athemes-addons-for-elementor-lite' ),
				'label_off' => __( 'No', 'athemes-addons-for-elementor-lite' ),
				'return_value' => 'yes',
				'default' => 'no',
				'selectors_dictionary' => [
					'yes'   => 'width:100%',
					'no'    => 'width:auto',
				],
				'selectors' => [
					'{{WRAPPER}} .athemes-addons-contact-form input[type=button]' => '{{VALUE}}',
					'{{WRAPPER}} .athemes-addons-contact-form input[type=submit]' => '{{VALUE}}',
				],                               
			]
		);  
		
		$this->add_responsive_control(
			'button_size',
			[
				'label' => __( 'Button max. width', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 2000,
						'step' => 5,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
						'step' => 1,
					],                  
				],
				'devices' => [ 'desktop', 'tablet', 'mobile' ],
				'selectors' => [
					'{{WRAPPER}} .athemes-addons-contact-form input[type=button]' => 'max-width: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .athemes-addons-contact-form input[type=submit]' => 'max-width: {{SIZE}}{{UNIT}};',
				],
				'condition' => array(
					'stretch_button' => 'yes',
				),
			]
		);          

		$this->add_responsive_control(
			'align_button',
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
				'default' => '',
				'selectors_dictionary' => [
					'left'      => 'margin-left:0;margin-right:auto;display:table;',
					'center'    => 'margin-left:auto;margin-right:auto;display:table;',
					'right'     => 'margin-left:auto;margin-right:0;display:table;',
				],
				'selectors' => [
					'{{WRAPPER}} .athemes-addons-contact-form input[type=button]' => '{{VALUE}}',
					'{{WRAPPER}} .athemes-addons-contact-form input[type=submit]' => '{{VALUE}}',
				],
			]
		);  
		

		$this->end_controls_section();

		//First part styles
		$this->start_controls_section(
			'section_form_container',
			[
				'label' => __( 'Container', 'athemes-addons-for-elementor-lite' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'container_background',
				'label' => __( 'Background', 'athemes-addons-for-elementor-lite' ),
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .athemes-addons-form-inner',
			]
		);

		$this->add_responsive_control(
			'container_padding',
			[
				'label' => __( 'Padding', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'devices' => [ 'desktop', 'tablet', 'mobile' ],
				'desktop_default' => [
					'top' => 15,
					'right' => 15,
					'bottom' => 15,
					'left' => 15,
					'unit' => 'px',
				],
				'tablet_default' => [
					'top' => 15,
					'right' => 15,
					'bottom' => 15,
					'left' => 15,
					'unit' => 'px',
				],
				'mobile_default' => [
					'top' => 15,
					'right' => 15,
					'bottom' => 15,
					'left' => 15,
					'unit' => 'px',
				],              
				'selectors' => [
					'{{WRAPPER}} .athemes-addons-form-inner' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);  
		
		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'container_border',
				'label' => __( 'Border', 'athemes-addons-for-elementor-lite' ),
				'selector' => '{{WRAPPER}} .athemes-addons-form-inner',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'container_shadow',
				'label' => __( 'Box Shadow', 'athemes-addons-for-elementor-lite' ),
				'selector' => '{{WRAPPER}} .athemes-addons-form-inner',
			]
		);

		$this->end_controls_section();

		//Title styles
		$this->start_controls_section(
			'section_form_title',
			[
				'label' => __( 'Title', 'athemes-addons-for-elementor-lite' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'form_title_color',
			[
				'label'     => __( 'Color', 'athemes-addons-for-elementor-lite' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .athemes-addons-contact-form-title' => 'color: {{VALUE}};',
				],
			]
		);          

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'      => 'form_title_typography',
				'selector'  => '{{WRAPPER}} .athemes-addons-contact-form-title',
			]
		);

		$this->end_controls_section();

		//Label styles
		$this->start_controls_section(
			'section_form_labels',
			[
				'label' => __( 'Labels', 'athemes-addons-for-elementor-lite' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'form_labels_color',
			[
				'label'     => __( 'Color', 'athemes-addons-for-elementor-lite' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .athemes-addons-form-inner label' => 'color: {{VALUE}};',
				],
			]
		);          

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'      => 'form_labels_typography',
				'selector'  => '{{WRAPPER}} .athemes-addons-form-inner label',
			]
		);

		$this->end_controls_section();      
		
		//Fields styles
		$this->start_controls_section(
			'section_form_fields',
			[
				'label' => __( 'Fields', 'athemes-addons-for-elementor-lite' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'field_background_color',
			[
				'label'     => __( 'Background color', 'athemes-addons-for-elementor-lite' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .athemes-addons-form-inner input:not([type="submit"]), {{WRAPPER}} .athemes-addons-form-inner textarea, {{WRAPPER}} .athemes-addons-form-inner select' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'field_text_color',
			[
				'label'     => __( 'Color', 'athemes-addons-for-elementor-lite' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .athemes-addons-form-inner input:not([type="submit"]), {{WRAPPER}} .athemes-addons-form-inner textarea, {{WRAPPER}} .athemes-addons-form-inner select' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'field_border_radius',
			[
				'label' => esc_html__( 'Border Radius', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} input:not([type="submit"])' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} textarea' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);      

		$this->add_control(
			'field_placeholder_color',
			[
				'label'     => __( 'Placeholder color', 'athemes-addons-for-elementor-lite' ),
				'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} input:not([type="submit"])::placeholder,
					{{WRAPPER}} textarea::placeholder' => 'color: {{VALUE}};',
                ],
			]
		);      

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'field_border',
				'label' => __( 'Border', 'athemes-addons-for-elementor-lite' ),
				'selector' => '{{WRAPPER}} .athemes-addons-form-inner input:not([type="submit"]), {{WRAPPER}} .athemes-addons-form-inner textarea, {{WRAPPER}} .athemes-addons-form-inner select',
			]
		);

		$this->add_responsive_control(
			'field_padding',
			[
				'label' => __( 'Padding', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'devices' => [ 'desktop', 'tablet', 'mobile' ],         
				'selectors' => [
					'{{WRAPPER}} .athemes-addons-form-inner input:not([type="submit"]), {{WRAPPER}} .athemes-addons-form-inner textarea, {{WRAPPER}} .athemes-addons-form-inner select' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);  
		
		$this->add_responsive_control(
			'field_spacing',
			[
				'label' => __( 'Spacing', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
						'step' => 1,
					],                  
				],
				'devices' => [ 'desktop', 'tablet', 'mobile' ],
				'selectors' => [
					'{{WRAPPER}} .athemes-addons-form-inner input:not([type="submit"]), {{WRAPPER}} .athemes-addons-form-inner textarea' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);  

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			[
				'label' => esc_html__( 'Button', 'athemes-addons-for-elementor-lite' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'typography',
				'selector' => '{{WRAPPER}} input[type=button], {{WRAPPER}} input[type=submit]',
			]
		);

		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'name' => 'text_shadow',
				'selector' => '{{WRAPPER}} input[type=button], {{WRAPPER}} input[type=submit]',
			]
		);

		$this->start_controls_tabs( 'tabs_button_style' );

		$this->start_controls_tab(
			'tab_button_normal',
			[
				'label' => esc_html__( 'Normal', 'athemes-addons-for-elementor-lite' ),
			]
		);

		$this->add_control(
			'button_text_color',
			[
				'label' => esc_html__( 'Text Color', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} input[type=button]' => 'fill: {{VALUE}}; color: {{VALUE}};',
					'{{WRAPPER}} input[type=submit]' => 'fill: {{VALUE}}; color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'background',
				'label' => esc_html__( 'Background', 'athemes-addons-for-elementor-lite' ),
				'types' => [ 'classic', 'gradient' ],
				'exclude' => [ 'image' ], // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
				'selector' => '{{WRAPPER}} input[type=button], {{WRAPPER}} input[type=submit]',
				'fields_options' => [
					'background' => [
						'default' => 'classic',
					],
					'color' => [],
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_button_hover',
			[
				'label' => esc_html__( 'Hover', 'athemes-addons-for-elementor-lite' ),
			]
		);

		$this->add_control(
			'hover_color',
			[
				'label' => esc_html__( 'Text Color', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} input[type=button]:hover, {{WRAPPER}} input[type=button]:focus, {{WRAPPER}} input[type=submit]:hover, {{WRAPPER}} input[type=submit]:focus,' => 'color: {{VALUE}};',
					'{{WRAPPER}} input[type=button]:hover svg, {{WRAPPER}} input[type=button]:focus svg, {{WRAPPER}} input[type=submit]:hover svg, {{WRAPPER}} input[type=submit]:focus svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'button_background_hover',
				'label' => esc_html__( 'Background', 'athemes-addons-for-elementor-lite' ),
				'types' => [ 'classic', 'gradient' ],
				'exclude' => [ 'image' ], // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
				'selector' => '{{WRAPPER}} input[type=button]:hover, {{WRAPPER}} input[type=button]:focus, {{WRAPPER}} input[type=submit]:hover, {{WRAPPER}} input[type=submit]:focus',
				'fields_options' => [
					'background' => [
						'default' => 'classic',
					],
				],
			]
		);

		$this->add_control(
			'button_hover_border_color',
			[
				'label' => esc_html__( 'Border Color', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::COLOR,
				'condition' => [
					'border_border!' => '',
				],
				'selectors' => [
					'{{WRAPPER}} input[type=button]:hover, {{WRAPPER}} input[type=button]:focus, {{WRAPPER}} input[type=submit]:hover, {{WRAPPER}} input[type=submit]:focus' => 'border-color: {{VALUE}};',
				],
			]
		);


		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'border',
				'selector' => '{{WRAPPER}} input[type=button], {{WRAPPER}} input[type=submit]',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'border_radius',
			[
				'label' => esc_html__( 'Border Radius', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} input[type=button], {{WRAPPER}} input[type=submit]' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'button_box_shadow',
				'selector' => '{{WRAPPER}} input[type=button], {{WRAPPER}} input[type=submit]',
			]
		);

		$this->add_responsive_control(
			'text_padding',
			[
				'label' => esc_html__( 'Padding', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} input[type=button], {{WRAPPER}} input[type=submit]' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'separator' => 'before',
			]
		);

		$this->end_controls_section();      

		//Register upsell section
		$this->register_upsell_section();
	}

	public function forms() {

        $options = array();

        if ( class_exists( 'Ninja_Forms' ) ) {
            $contact_forms = Ninja_Forms()->form()->get_forms();

            if (!empty($contact_forms) && !is_wp_error($contact_forms)) {

                $options[0] = esc_html__( 'Select a form', 'athemes-addons-for-elementor-lite');

                foreach ($contact_forms as $form) {
                    $options[$form->get_id()] = $form->get_setting('title');
                }
            }
        } else {
            $options[0] = esc_html__('Create a Form First', 'athemes-addons-for-elementor-lite');
        }

        return $options;
	}

	/**
	 * Render icon list widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render() {

		if ( !class_exists( 'Ninja_Forms' ) ) {
            return;
        }

		$settings = $this->get_settings_for_display();
		?>

		<div class="athemes-addons-contact-form athemes-addons-ninjaforms">
			<div class="athemes-addons-form-inner">
				<?php if ( $settings['form_title'] ) : ?>
					<?php $settings['form_title_tag'] = athemes_addons_validate_html_tag( $settings['form_title_tag'] ); ?>
					<<?php echo tag_escape( $settings['form_title_tag'] ); ?> class="athemes-addons-contact-form-title">
						<?php echo esc_html( $settings['form_title'] ); ?>
					</<?php echo tag_escape( $settings['form_title_tag'] ); ?>>
				<?php endif; ?>
				<?php echo do_shortcode( '[ninja_form id="' . absint( $settings['contact_forms'] ) . '" ]' ); ?>
			</div>
		</div>
		<style>
			.athemes-addons-contact-form {
				display: -webkit-box;
				display: -ms-flexbox;
				display: flex;
			}
			.athemes-addons-contact-form label,
			.athemes-addons-contact-form input:not([type="submit"]) {
				width: 100%;
			}
			.athemes-addons-form-inner {
				width: 100%;
				max-width: 370px;
			}
			.athemes-addons-contact-form button[type="submit"],
			.athemes-addons-contact-form input[type="submit"] {
				height: auto !important;
			}
		</style>

		<?php
	}

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
Plugin::instance()->widgets_manager->register( new Ninja_Forms() );