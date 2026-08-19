<?php
namespace aThemes_Addons\Widgets;

use Elementor\Plugin;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Utils;
use Elementor\Widget_Base;
use Elementor\Icons_Manager;
use aThemes_Addons\Traits\Upsell_Section_Trait;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Table of Contents widget.
 *
 * @since 1.0.0
 */
class Table_Of_Contents extends Widget_Base {
	use Upsell_Section_Trait;
	
	/**
	 * Get widget name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'athemes-addons-table-of-contents';
	}

	/**
	 * Get widget title.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return esc_html__( 'Table of Contents', 'athemes-addons-for-elementor-lite' );
	}

	/**
	 * Get widget icon.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-table-of-contents aafe-elementor-icon';
	}

	/**
	 * Enqueue styles.
	 */
	public function get_style_depends() {
		return [ $this->get_name() . '-styles' ];
	}

	/**
	 * Enqueue scripts.
	 */
	public function get_script_depends() {
		return [ $this->get_name() . '-scripts' ];
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
		return [ 'table', 'contents', 'toc', 'navigation', 'headings', 'athemes', 'addons', 'athemes addons' ];
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
		return 'https://docs.athemes.com/article/table-of-contents/';
	}

	/**
	 * Register table of contents widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
		protected function register_controls() {
		
		// Content Tab - Single Section for all content controls
		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'Content', 'athemes-addons-for-elementor-lite' ),
			]
		);

		$this->add_control(
			'title',
			[
				'label' => esc_html__( 'Title', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( 'Table of Contents', 'athemes-addons-for-elementor-lite' ),
				'label_block' => true,
			]
		);

		$this->add_control(
			'title_tag',
			[
				'label' => esc_html__( 'Title HTML Tag', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::SELECT,
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
				'default' => 'h3',
				'separator' => 'after',
			]
		);

		$this->start_controls_tabs(
			'include_exclude_tabs'
		);

		// Include Tab
		$this->start_controls_tab(
			'include_tab',
			[
				'label' => esc_html__( 'Include', 'athemes-addons-for-elementor-lite' ),
			]
		);

		$this->add_control(
			'heading_types',
			[
				'label' => esc_html__( 'Include Headings', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::SELECT2,
				'multiple' => true,
				'options' => [
					'h1' => 'H1',
					'h2' => 'H2',
					'h3' => 'H3',
					'h4' => 'H4',
					'h5' => 'H5',
					'h6' => 'H6',
				],
				'default' => [ 'h1', 'h2', 'h3', 'h4' ],
			]
		);

		$this->add_control(
			'container_selector',
			[
				'label' => esc_html__( 'Container Selector', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::TEXT,
				'description' => esc_html__( 'Optional CSS selector (class or ID) to scan for headings. Leave empty to use current post/page content.', 'athemes-addons-for-elementor-lite' ),
				'placeholder' => esc_html__( '.content, #main-content', 'athemes-addons-for-elementor-lite' ),
				'label_block' => true,
			]
		);

		$this->end_controls_tab();

		// Exclude Tab
		$this->start_controls_tab(
			'exclude_tab',
			[
				'label' => esc_html__( 'Exclude', 'athemes-addons-for-elementor-lite' ),
			]
		);

		$this->add_control(
			'exclude_classes',
			[
				'label' => esc_html__( 'Exclude CSS Classes', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::TEXT,
				'description' => esc_html__( 'Enter CSS classes to exclude (comma separated)', 'athemes-addons-for-elementor-lite' ),
				'placeholder' => esc_html__( 'no-toc, exclude-heading', 'athemes-addons-for-elementor-lite' ),
				'label_block' => true,
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'list_style',
			[
				'label' => esc_html__( 'List Style', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'numbers' => esc_html__( 'Numbers', 'athemes-addons-for-elementor-lite' ),
					'custom' => esc_html__( 'Custom Icon', 'athemes-addons-for-elementor-lite' ),
				],
				'default' => 'numbers',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'custom_icon',
			[
				'label' => esc_html__( 'List Icon', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::ICONS,
				'default' => [
					'value' => 'fas fa-circle',
					'library' => 'fa-solid',
				],
				'condition' => [
					'list_style' => 'custom',
				],
			]
		);

		$this->add_control(
			'enable_collapsible',
			[
				'label' => esc_html__( 'Make Collapsible', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'no',
				'return_value' => 'yes',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'collapse_icon',
			[
				'label' => esc_html__( 'Collapse Icon', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::ICONS,
				'default' => [
					'value' => 'fas fa-chevron-up',
					'library' => 'fa-solid',
				],
				'condition' => [
					'enable_collapsible' => 'yes',
				],
			]
		);

		$this->add_control(
			'expand_icon',
			[
				'label' => esc_html__( 'Expand Icon', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::ICONS,
				'default' => [
					'value' => 'fas fa-chevron-down',
					'library' => 'fa-solid',
				],
				'condition' => [
					'enable_collapsible' => 'yes',
				],
			]
		);

		$this->add_control(
			'collapse_breakpoint',
			[
				'label' => esc_html__( 'Collapse Below', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'tablet' => esc_html__( 'Tablet', 'athemes-addons-for-elementor-lite' ),
					'desktop' => esc_html__( 'Desktop', 'athemes-addons-for-elementor-lite' ),
				],
				'default' => 'tablet',
				'condition' => [
					'enable_collapsible' => 'yes',
				],
			]
		);

		$this->add_control(
			'initially_collapsed',
			[
				'label' => esc_html__( 'Initially Collapsed', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'no',
				'return_value' => 'yes',
				'condition' => [
					'enable_collapsible' => 'yes',
				],
			]
		);

		$this->end_controls_section();

		// Style Tab - Wrapper Styles Section
		$this->start_controls_section(
			'section_wrapper_style',
			[
				'label' => esc_html__( 'Wrapper', 'athemes-addons-for-elementor-lite' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'wrapper_background_color',
			[
				'label' => esc_html__( 'Background Color', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .athemes-addons-table-of-contents' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'wrapper_padding',
			[
				'label' => esc_html__( 'Padding', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .athemes-addons-table-of-contents' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'wrapper_margin',
			[
				'label' => esc_html__( 'Margin', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .athemes-addons-table-of-contents' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'wrapper_border',
				'label' => esc_html__( 'Border', 'athemes-addons-for-elementor-lite' ),
				'selector' => '{{WRAPPER}} .athemes-addons-table-of-contents',
			]
		);

		$this->add_responsive_control(
			'wrapper_border_radius',
			[
				'label' => esc_html__( 'Border Radius', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .athemes-addons-table-of-contents' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'wrapper_box_shadow',
				'label' => esc_html__( 'Box Shadow', 'athemes-addons-for-elementor-lite' ),
				'selector' => '{{WRAPPER}} .athemes-addons-table-of-contents',
			]
		);

		$this->add_responsive_control(
			'wrapper_max_width',
			[
				'label' => esc_html__( 'Max Width', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [
						'min' => 100,
						'max' => 1200,
					],
					'%' => [
						'min' => 10,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .athemes-addons-table-of-contents' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);      

		$this->add_responsive_control(
			'wrapper_text_align',
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
				'default' => 'left',
				'selectors' => [
					'{{WRAPPER}} .athemes-addons-table-of-contents' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		// Header Styles Section
		$this->start_controls_section(
			'section_header_style',
			[
				'label' => esc_html__( 'Header', 'athemes-addons-for-elementor-lite' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'title_color',
			[
				'label' => esc_html__( 'Title Color', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .toc-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'title_typography',
				'label' => esc_html__( 'Title Typography', 'athemes-addons-for-elementor-lite' ),
				'selector' => '{{WRAPPER}} .toc-title',
			]
		);

		$this->add_responsive_control(
			'title_spacing',
			[
				'label' => esc_html__( 'Title Spacing', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .toc-title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'header_background_color',
			[
				'label' => esc_html__( 'Header Background Color', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .toc-header' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'header_padding',
			[
				'label' => esc_html__( 'Header Padding', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors' => [
					'{{WRAPPER}} .toc-header' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'header_border',
				'label' => esc_html__( 'Header Border', 'athemes-addons-for-elementor-lite' ),
				'selector' => '{{WRAPPER}} .toc-header',
			]
		);
		
		$this->end_controls_section();

		// List Styles Section
		$this->start_controls_section(
			'section_list_style',
			[
				'label' => esc_html__( 'List', 'athemes-addons-for-elementor-lite' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		// General List Styles
		$this->add_control(
			'list_general_heading',
			[
				'label' => esc_html__( 'General List Styles', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'list_item_spacing',
			[
				'label' => esc_html__( 'List Item Spacing', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .toc-list li' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'list_item_color',
			[
				'label' => esc_html__( 'List Item Color', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .toc-list a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'list_item_typography',
				'label' => esc_html__( 'List Item Typography', 'athemes-addons-for-elementor-lite' ),
				'selector' => '{{WRAPPER}} .toc-list a',
			]
		);

		$this->add_control(
			'list_item_hover_color',
			[
				'label' => esc_html__( 'List Item Hover Color', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .toc-list a:hover' => 'color: {{VALUE}};',
				],
			]
		);

		// Number/Icon Styles
		$this->add_control(
			'number_icon_heading',
			[
				'label' => esc_html__( 'Number/Icon Styles', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'number_icon_color',
			[
				'label' => esc_html__( 'Number/Icon Color', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .toc-list .toc-marker' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'number_icon_size',
			[
				'label' => esc_html__( 'Number/Icon Size', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 8,
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .toc-list .toc-marker' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'number_icon_spacing',
			[
				'label' => esc_html__( 'Number/Icon Spacing', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .toc-list .toc-marker' => 'margin-right: {{SIZE}}{{UNIT}};',
				],
			]
		);

		// Nested List Styles
		$this->add_control(
			'nested_list_heading',
			[
				'label' => esc_html__( 'Nested List Styles', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'indent_size',
			[
				'label' => esc_html__( 'Indent Size', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .toc-list ul' => 'padding-left: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'nested_item_color',
			[
				'label' => esc_html__( 'Nested Item Color', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .toc-list ul a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		// Collapsible Styles Section
		$this->start_controls_section(
			'section_collapsible_style',
			[
				'label' => esc_html__( 'Collapsible', 'athemes-addons-for-elementor-lite' ),
				'tab' => Controls_Manager::TAB_STYLE,
				'condition' => [
					'enable_collapsible' => 'yes',
				],
			]
		);

		$this->add_control(
			'toggle_button_background',
			[
				'label' => esc_html__( 'Toggle Button Background', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .toc-toggle-button' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'toggle_button_color',
			[
				'label' => esc_html__( 'Toggle Button Color', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .toc-toggle-button' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'toggle_button_hover_background',
			[
				'label' => esc_html__( 'Toggle Button Hover Background', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .toc-toggle-button:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'toggle_button_hover_color',
			[
				'label' => esc_html__( 'Toggle Button Hover Color', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .toc-toggle-button:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		//Register upsell section
		$this->register_upsell_section();
	}

	/**
	 * Render table of contents widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		
		$this->add_render_attribute( 'wrapper', 'class', 'athemes-addons-table-of-contents' );
		
		// Add collapsible classes
		if ( 'yes' === $settings['enable_collapsible'] ) {
			$this->add_render_attribute( 'wrapper', 'class', 'toc-collapsible' );
			$this->add_render_attribute( 'wrapper', 'class', 'toc-collapsible-' . esc_attr( $settings['collapse_breakpoint'] ) );
			
			// Add initially collapsed class
			if ( 'yes' === $settings['initially_collapsed'] ) {
				$this->add_render_attribute( 'wrapper', 'class', 'toc-collapsed' );
			}
		}
		
		// Add list style class
		$this->add_render_attribute( 'wrapper', 'class', 'toc-style-' . esc_attr( $settings['list_style'] ) );
		
		// Prepare data attributes for JavaScript
		$data_attrs = [
			'heading-types' => ! empty( $settings['heading_types'] ) ? implode( ',', $settings['heading_types'] ) : 'h1,h2,h3,h4',
			'container-selector' => str_replace( array( '<', '>' ), '', $settings['container_selector'] ),
			'exclude-classes' => $settings['exclude_classes'],
			'max-nesting-level' => $settings['max_nesting_level'],
		];
		
		foreach ( $data_attrs as $key => $value ) {
			if ( ! empty( $value ) ) {
				$this->add_render_attribute( 'wrapper', 'data-' . $key, esc_attr( $value ) );
			}
		}

		?>

		<div <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
			<?php if ( 'custom' === $settings['list_style'] && ! empty( $settings['custom_icon'] ) ) : ?>
				<span class="toc-custom-icon-template" style="display: none;" aria-hidden="true">
					<?php Icons_Manager::render_icon( $settings['custom_icon'], [ 'aria-hidden' => 'true' ] ); ?>
				</span>
			<?php endif; ?>
			
			<div class="toc-header">
				<?php if ( ! empty( $settings['title'] ) ) : ?>
					<?php $settings['title_tag'] = athemes_addons_validate_html_tag( $settings['title_tag'] ); ?>
					<<?php echo tag_escape( $settings['title_tag'] ); ?> class="toc-title">
						<?php echo esc_html( $settings['title'] ); ?>
					</<?php echo tag_escape( $settings['title_tag'] ); ?>>
				<?php endif; ?>
				
				<?php if ( 'yes' === $settings['enable_collapsible'] ) : ?>
					<span class="toc-toggle-button" aria-expanded="<?php echo 'yes' === $settings['initially_collapsed'] ? 'false' : 'true'; ?>" aria-label="<?php echo esc_attr__( 'Toggle table of contents', 'athemes-addons-for-elementor-lite' ); ?>">
						<span class="toc-toggle-icon-collapse">
							<?php Icons_Manager::render_icon( $settings['collapse_icon'], [ 'aria-hidden' => 'true' ] ); ?>
						</span>
						<span class="toc-toggle-icon-expand">
							<?php Icons_Manager::render_icon( $settings['expand_icon'], [ 'aria-hidden' => 'true' ] ); ?>
						</span>
					</span>
				<?php endif; ?>						
			</div>
			
			<div class="toc-content">
				<nav class="toc-list" aria-label="<?php echo esc_attr__( 'Table of contents', 'athemes-addons-for-elementor-lite' ); ?>">
					<!-- Table of contents will be dynamically generated by JavaScript -->
				</nav>
			</div>
		</div>

		<?php
	}

	/**
	 * Render table of contents widget output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function content_template() {}
}

Plugin::instance()->widgets_manager->register( new Table_Of_Contents() );
