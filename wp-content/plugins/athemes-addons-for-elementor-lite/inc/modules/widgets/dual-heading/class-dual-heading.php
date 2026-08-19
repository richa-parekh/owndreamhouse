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
class Dual_Heading extends Widget_Base {
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
		return 'athemes-addons-dual-heading';
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
		return __( 'Dual heading', 'athemes-addons-for-elementor-lite' );
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
		return 'eicon-heading aafe-elementor-icon';
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
		return [ 'heading', 'title', 'dual', 'athemes', 'addons', 'athemes addons' ];
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
		return 'https://docs.athemes.com/article/dual-heading/';
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
		$this->start_controls_section(
			'section_settings',
			[
				'label' => __( 'Dual heading', 'athemes-addons-for-elementor-lite' ),
			]
		);

		$this->add_control(
			'first_text',
			[
				'label' => __( 'First part', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
				'placeholder' => '',
				'default' => __( 'Dual', 'athemes-addons-for-elementor-lite' ),      
			]
		);
		
		$this->add_control(
			'second_text',
			[
				'label' => __( 'Second part', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
				'placeholder' => '',
				'default' => __( 'Heading', 'athemes-addons-for-elementor-lite' ),       
			]
		);  
		
		$this->add_control(
			'title_tag',
			[
				'label' => __('HTML Tag', 'athemes-addons-for-elementor-lite'),
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
				'separator' => 'before',
			]
		);  

		$this->add_control(
			'block_display',
			[
				'label'         => __('Display on separate lines', 'athemes-addons-for-elementor-lite'),
				'type'          => Controls_Manager::SWITCHER,
				'label_on'      => __('Yes', 'athemes-addons-for-elementor-lite'),
				'label_off'     => __('No', 'athemes-addons-for-elementor-lite'),
				'return_value'  => 'true',
				'selectors_dictionary' => [
					'false'         => 'inline',
					'true'          => 'block',
				],
				'default'       => 'false',
				'selectors' => [
					'{{WRAPPER}} span' => 'display: {{VALUE}};',
				],              
			]
		);      
		
		$this->add_control(
			'link',
			[
				'label' => esc_html__( 'Link', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::URL,
				'dynamic' => [
					'active' => true,
				],
				'default' => [
					'url' => '',
				],
				'separator' => 'before',
			]
		);      

		$this->add_responsive_control(
			'align',
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
					'justify' => [
						'title' => esc_html__( 'Justified', 'athemes-addons-for-elementor-lite' ),
						'icon' => 'eicon-text-align-justify',
					],
				],
				'default' => '',
				'selectors' => [
					'{{WRAPPER}}' => 'text-align: {{VALUE}};',
				],
			]
		);      
		

		$this->end_controls_section();

		$this->start_controls_section(
			'section_first_part_style',
			[
				'label' => __( 'First part', 'athemes-addons-for-elementor-lite' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'first_part_padding',
			[
				'label' => __( 'Padding', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .h-first-part' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],              
			]
		);

		$this->add_responsive_control(
			'first_part_margin',
			[
				'label' => __( 'Margin', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .h-first-part' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],          
				'separator' => 'after', 
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'first_part_border',
				'selector'  => '{{WRAPPER}} .h-first-part',
			]
		);

		$this->add_control(
			'first_part_border_radius',
			[
				'label'         => __( 'Border radius', 'athemes-addons-for-elementor-lite' ),
				'type'          => Controls_Manager::DIMENSIONS,
				'size_units'    => [ 'px', '%' ],
				'selectors'     => [
					'{{WRAPPER}} .h-first-part' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'separator' => 'after',
			]
		);      

		$this->add_group_control(
			Group_Control_Background::get_type(),
				array(
				'name'      => 'first_part_background',
				'types'     => array( 'classic', 'gradient' ),
				'selector'  => '{{WRAPPER}} .h-first-part',
			)
		);

		$this->add_control(
			'first_clip_text',
			[
				'label'         => __( 'Clip text', 'athemes-addons-for-elementor-lite' ),
				'type'          => Controls_Manager::SWITCHER,
				'label_on'      => __( 'Yes', 'athemes-addons-for-elementor-lite' ),
				'label_off'     => __( 'No', 'athemes-addons-for-elementor-lite' ),
				'return_value'  => 'true',
				'default'       => 'false',
				'selectors_dictionary' => [
					'false' => '',
					'true' => 'background-clip: text;-webkit-background-clip: text; -webkit-text-fill-color: transparent;',
				],
				'selectors' => [
					'{{WRAPPER}} .h-first-part' => '{{VALUE}}',
				],
			]
		);

		$this->add_control(
			'first_part_color',
			[
				'label'     => __( 'Color', 'athemes-addons-for-elementor-lite' ),
				'default'   => '#c687ff',
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .h-first-part' => 'color: {{VALUE}};',
				],
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'      => 'first_part_typography',
				'selector'  => '{{WRAPPER}} .h-first-part',
			]
		);
		
		//text shadow.
		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'label'     => __( 'Text Shadow', 'athemes-addons-for-elementor-lite' ),
				'name'      => 'first_part_text_shadow',
				'selector'  => '{{WRAPPER}} .h-first-part',
			]
		);

		$this->end_controls_section();


		$this->start_controls_section(
			'section_second_part_style',
			[
				'label' => __( 'Second part', 'athemes-addons-for-elementor-lite' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'second_part_padding',
			[
				'label' => __( 'Padding', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .h-second-part' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],              
			]
		);

		$this->add_responsive_control(
			'second_part_margin',
			[
				'label' => __( 'Margin', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .h-second-part' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],          
				'separator' => 'after', 
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'second_part_border',
				'selector'  => '{{WRAPPER}} .h-second-part',
			]
		);

		$this->add_control(
			'second_part_border_radius',
			[
				'label'         => __( 'Border radius', 'athemes-addons-for-elementor-lite' ),
				'type'          => Controls_Manager::DIMENSIONS,
				'size_units'    => [ 'px', '%' ],
				'selectors'     => [
					'{{WRAPPER}} .h-second-part' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'separator' => 'after',
			]
		);      

		$this->add_group_control(
			Group_Control_Background::get_type(),
				array(
				'name'      => 'second_part_background',
				'types'     => array( 'classic', 'gradient' ),
				'selector'  => '{{WRAPPER}} .h-second-part',
			)
		);

		$this->add_control(
			'second_clip_text',
			[
				'label'         => __( 'Clip text', 'athemes-addons-for-elementor-lite' ),
				'type'          => Controls_Manager::SWITCHER,
				'label_on'      => __( 'Yes', 'athemes-addons-for-elementor-lite' ),
				'label_off'     => __( 'No', 'athemes-addons-for-elementor-lite' ),
				'return_value'  => 'true',
				'default'       => 'false',
				'selectors_dictionary' => [
					'false' => '',
					'true' => 'background-clip: text;-webkit-background-clip: text; -webkit-text-fill-color: transparent;',
				],
				'selectors' => [
					'{{WRAPPER}} .h-second-part' => '{{VALUE}}',
				],
			]
		);

		$this->add_control(
			'second_part_color',
			[
				'label'     => __( 'Color', 'athemes-addons-for-elementor-lite' ),
				'default'   => '',
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .h-second-part' => 'color: {{VALUE}};',
				],
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'      => 'second_part_typography',
				'selector'  => '{{WRAPPER}} .h-second-part',
			]
		);
		
		//text shadow.
		$this->add_group_control(
			Group_Control_Text_Shadow::get_type(),
			[
				'label'     => __( 'Text Shadow', 'athemes-addons-for-elementor-lite' ),
				'name'      => 'second_part_text_shadow',
				'selector'  => '{{WRAPPER}} .h-second-part',
			]
		);

		$this->end_controls_section();

		//Register upsell section
		$this->register_upsell_section();
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
		$settings = $this->get_settings_for_display();

		$this->add_render_attribute( 'wrapper', 'class', 'athemes-addons-posts-list elementor-grid' );
		?>

		<div <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
			<?php $settings['title_tag'] = athemes_addons_validate_html_tag( $settings['title_tag'] ); ?>
			<<?php echo tag_escape( $settings['title_tag'] ); ?> class="athemes-dual-heading">
				<?php
				if ( ! empty( $settings['link']['url'] ) ) {
					$this->add_link_attributes( 'url', $settings['link'] );

					?>
					<a style="text-decoration:none;" <?php $this->print_render_attribute_string( 'url' ); ?>>
					<?php
				}
				?>

				<span class="h-first-part"><?php echo wp_kses_post( $settings['first_text'] ); ?></span>
				<span class="h-second-part"><?php echo wp_kses_post( $settings['second_text'] ); ?></span>
				
				<?php
				if ( ! empty( $settings['link']['url'] ) ) {
					echo '</a>';
				}
				?>
			</<?php echo tag_escape( $settings['title_tag'] ); ?>>
		</div>

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
Plugin::instance()->widgets_manager->register( new Dual_Heading() );