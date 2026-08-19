<?php
namespace aThemes_Addons\Widgets;

use aThemes_Addons_Posts_Helper as Posts_Helper;
use Elementor\Plugin;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;
use Elementor\Utils;
use Elementor\Widget_Base;
use Elementor\Group_Control_Image_Size;
use aThemes_Addons\Traits\Upsell_Section_Trait;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * News ticker widget.
 *
 *
 * @since 1.0.0
 */
class News_Ticker extends Widget_Base {
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
		return 'athemes-addons-news-ticker';
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
		return __( 'News Ticker', 'athemes-addons-for-elementor-lite' );
	}

	public function get_keywords() {
		return [ 'posts', 'blog', 'post', 'carousel', 'ticker', 'news ticker', 'news bar', 'news', 'articles', 'athemes', 'addons', 'athemes addons' ];
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
		return 'eicon-posts-ticker aafe-elementor-icon';
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
		return 'https://docs.athemes.com/article/news-ticker/';
	}

	/**
	 * Enqueue styles.
	 */
	public function get_style_depends() {
		return [ 'swiper', $this->get_name() . '-styles' ];
	}   

	/**
	 * Enqueue scripts.
	 */
	public function get_script_depends() {
		return [ 'swiper', $this->get_name() . '-scripts' ];
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
			'section_title',
			[
				'label' => __( 'Title', 'athemes-addons-for-elementor-lite' ),
			]
		);

		$this->add_control(
			'title',
			[
				'label'         => __( 'Title', 'athemes-addons-for-elementor-lite' ),
				'type'          => Controls_Manager::TEXT,
				'default'       => __( 'Latest News', 'athemes-addons-for-elementor-lite' ),
			]
		);

		$this->add_control(
			'main_title_tag',
			[
				'label'         => __( 'Title tag', 'athemes-addons-for-elementor-lite' ),
				'type'          => Controls_Manager::SELECT,
				'default'       => 'h4',
				'options'       => [
					'h1'    => __( 'H1', 'athemes-addons-for-elementor-lite' ),
					'h2'    => __( 'H2', 'athemes-addons-for-elementor-lite' ),
					'h3'    => __( 'H3', 'athemes-addons-for-elementor-lite' ),
					'h4'    => __( 'H4', 'athemes-addons-for-elementor-lite' ),
					'h5'    => __( 'H5', 'athemes-addons-for-elementor-lite' ),
					'h6'    => __( 'H6', 'athemes-addons-for-elementor-lite' ),
					'span'  => __( 'span', 'athemes-addons-for-elementor-lite' ),
					'p'     => __( 'P', 'athemes-addons-for-elementor-lite' ),
					'div'   => __( 'div', 'athemes-addons-for-elementor-lite' ),
				],
			]
		);

		$this->add_control(
			'title_decoration',
			[
				'label' => __( 'Title Decoration', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'none',
				'options' => [
					'none'          => __( 'None', 'athemes-addons-for-elementor-lite' ),
					'arrow-small'   => __( 'Arrow small', 'athemes-addons-for-elementor-lite' ),
					'arrow-large'   => __( 'Arrow large', 'athemes-addons-for-elementor-lite' ),
					'diagonal-left' => __( 'Diagonal left', 'athemes-addons-for-elementor-lite' ),
					'diagonal-right' => __( 'Diagonal right', 'athemes-addons-for-elementor-lite' ),
				],
			]
		);

		$this->add_control(
			'icon',
			[
				'label' => __( 'Icon', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::ICONS,
				'skin' => 'inline',
				'default' => [
					'value' => 'fas fa-star',
					'library' => 'fa-solid',
				],
			]
		);

		$this->add_control(
			'icon_position',
			[
				'label' => __( 'Icon Position', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::CHOOSE,
				'default' => 'before',
				'options' => [
					'before' => [
						'title' => __( 'Before', 'athemes-addons-for-elementor-lite' ),
						'icon' => 'eicon-h-align-left',
					],
					'after' => [
						'title' => __( 'After', 'athemes-addons-for-elementor-lite' ),
						'icon' => 'eicon-h-align-right',
					],
				],
				'condition' => [
					'icon[value]!' => '',
				],
				'selectors_dictionary' => [
					'before' => '0',
					'after' => '11',
				],
				'selectors' => [
					'{{WRAPPER}} .news-ticker-icon' => 'order: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'icon_spacing',
			[
				'label' => __( 'Icon Spacing', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'size' => 10,
				],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .news-ticker-title' => 'gap: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'icon[value]!' => '',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_query',
			[
				'label' => __( 'Query', 'athemes-addons-for-elementor-lite' ),
			]
		);

		$post_types = Posts_Helper::get_post_types();

		$this->add_control(
			'post_type_filter',
			array(
				'label'     => __( 'Post Type', 'athemes-addons-for-elementor-lite' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => $post_types,
				'default'   => 'post',
			)
		);

		foreach ( $post_types as $key => $type ) {

			$taxonomy = Posts_Helper::get_taxonomies( $key );

			if ( ! empty( $taxonomy ) ) {

				foreach ( $taxonomy as $index => $tax ) {

					$terms = get_terms( $index );

					$related_tax = array();

					if ( ! empty( $terms ) ) {

						foreach ( $terms as $i => $object ) {

							$related_tax[ $object->slug ] = $object->name;
						}

						$this->add_control(
							$index . '_' . $key . '_filter_rule',
							array(
								'label'       => $tax->label,
								'type'        => Controls_Manager::SELECT,
								'default'     => 'IN',
								'label_block' => true,
								'options'     => array(
									/* translators: %s: Taxonomy label */
									'IN'     => sprintf( __( 'Include %s', 'athemes-addons-for-elementor-lite' ), $tax->label ),
 									/* translators: %s: Taxonomy label */
									'NOT IN' => sprintf( __( 'Exclude %s', 'athemes-addons-for-elementor-lite' ), $tax->label ),
								),
								'condition'   => array(
									'post_type_filter' => $key,
								),
							)
						);

						$this->add_control(
							'tax_' . $index . '_' . $key . '_filter',
							array(
								/* translators: %s: Taxonomy label */
								'label'       => sprintf( __( 'Choose %s', 'athemes-addons-for-elementor-lite' ), $tax->label ),
								'type'        => Controls_Manager::SELECT2,
								'default'     => '',
								'multiple'    => true,
								'label_block' => true,
								'options'     => $related_tax,
								'condition'   => array(
									'post_type_filter' => $key,
								),
								'separator'   => 'after',
							)
						);

					}
				}
			}
		}

		$this->add_control(
			'posts_filter_rule',
			array(
				'label'       => __( 'Posts', 'athemes-addons-for-elementor-lite' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'post__not_in',
				'label_block' => true,
				'options'     => array(
					'post__in'     => __( 'Include posts', 'athemes-addons-for-elementor-lite' ),
					'post__not_in' => __( 'Exclude posts', 'athemes-addons-for-elementor-lite' ), // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in
				),
				'condition'   => array(
					'post_type_filter' => 'post',
				),
			)
		);

		$this->add_control(
			'blog_posts_filter',
			array(
				'label'       => __( 'Posts', 'athemes-addons-for-elementor-lite' ),
				'type'        => Controls_Manager::SELECT2,
				'label_block' => true,
				'multiple'    => true,
				'options'     => Posts_Helper::get_default_posts_list( 'post' ),
				'condition'   => array(
					'post_type_filter' => 'post',
				),
			)
		);

		$this->add_control(
			'number',
			[
				'label' => __( 'Number of posts', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 5,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'orderby',
			[
				'label' => __( 'Order by', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'date',
				'options' => [
					'date'      => __( 'Date', 'athemes-addons-for-elementor-lite' ),
					'title'     => __( 'Title', 'athemes-addons-for-elementor-lite' ),
					'rand'      => __( 'Random', 'athemes-addons-for-elementor-lite' ),
				],
			]
		);

		$this->add_control(
			'order',
			[
				'label' => __( 'Order', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'desc',
				'options' => [
					'asc'   => __( 'ASC', 'athemes-addons-for-elementor-lite' ),
					'desc'  => __( 'DESC', 'athemes-addons-for-elementor-lite' ),
				],
			]
		);

		$this->add_control(
			'ignore_sticky_posts',
			array(
				'label'     => __( 'Ignore Sticky Posts', 'athemes-addons-for-elementor-lite' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => __( 'Yes', 'athemes-addons-for-elementor-lite' ),
				'label_off' => __( 'No', 'athemes-addons-for-elementor-lite' ),
				'default'   => 'yes',
				'condition'   => array(
					'post_type_filter' => 'post',
				),
			)
		);

		$this->add_control(
			'offset',
			[
				'label' => __( 'Offset', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 0,
			]
		);  

		$this->end_controls_section();

		//Item
		$this->start_controls_section(
			'section_item_settings',
			[
				'label' => __( 'Elements', 'athemes-addons-for-elementor-lite' ),
			]
		);

		$this->add_control(
			'title_tag',
			[
				'label' => __('Title tag', 'athemes-addons-for-elementor-lite'),
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
			'show_thumbnail',
			[
				'label' => __( 'Show featured image', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on'  => __( 'Yes', 'athemes-addons-for-elementor-lite' ),
				'label_off' => __( 'No', 'athemes-addons-for-elementor-lite' ),
				'return_value' => 'yes',
				'default' => 'yes',     
				'separator' => 'before',
			]
		);
		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			[
				'name'      => 'image',
				'default'   => 'thumbnail',
				'separator' => 'none',
			]
		);

		$this->add_control(
			'show_date',    
			[
				'label' => __( 'Show date', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on'  => __( 'Yes', 'athemes-addons-for-elementor-lite' ),
				'label_off' => __( 'No', 'athemes-addons-for-elementor-lite' ),
				'return_value' => 'yes',
				'default' => 'yes',    
				'separator' => 'before',           
			]
		);

		$this->end_controls_section();

		//settings
		$this->start_controls_section(
			'section_settings',
			[
				'label' => __( 'Settings', 'athemes-addons-for-elementor-lite' ),
			]
		);

		$this->add_responsive_control(
			'height',
			[
				'label' => __( 'Height', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range' => [
					'px' => [
						'min' => 40,
						'max' => 120,
					],
				],
				'default' => [
					'size' => 60,
				],
				'selectors' => [
					'{{WRAPPER}} .athemes-addons-news-ticker-container' => '--ticker-height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		//transition speed
		$this->add_control(
			'transition_speed',
			[
				'label' => __( 'Transition Speed', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 6000,
				'separator' => 'before',
			]
		);

		//autoplay speed
		$this->add_control(
			'autoplay_speed',
			[
				'label' => __( 'Autoplay Speed', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 5000,
			]
		);

		//end section
		$this->end_controls_section();   
		
		$this->start_controls_section(
			'section_style_wrapper',
			[
				'label' => __( 'Wrapper', 'athemes-addons-for-elementor-lite' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'wrapper_background_color',
			[
				'label' => __( 'Bar Background Color', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .athemes-addons-news-ticker' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'wrapper_border',
				'selector' => '{{WRAPPER}} .athemes-addons-news-ticker-container',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'wrapper_box_shadow',
				'selector' => '{{WRAPPER}} .athemes-addons-news-ticker-container',
			]
		);

		$this->add_control(
			'wrapper_border_radius',
			[
				'label' => __( 'Border Radius', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .athemes-addons-news-ticker-container' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .athemes-addons-news-ticker-container .news-ticker-title' => 'border-top-left-radius: {{TOP}}{{UNIT}}; border-bottom-left-radius: {{RIGHT}}{{UNIT}};',
					'{{WRAPPER}} .athemes-addons-news-ticker-container .athemes-addons-news-ticker' => 'border-top-right-radius: {{TOP}}{{UNIT}}; border-bottom-right-radius: {{RIGHT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_title',
			[
				'label' => __( 'Title', 'athemes-addons-for-elementor-lite' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'title_color',
			[
				'label' => __( 'Color', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .news-ticker-title .title' => 'color: {{VALUE}};',
					'{{WRAPPER}} .news-ticker-title svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'title_background_color',
			[
				'label' => __( 'Background Color', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .news-ticker-title' => '--ticker-background: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'title_typography',
				'selector' => '{{WRAPPER}} .news-ticker-title .title',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_image',
			[
				'label' => __( 'Image', 'athemes-addons-for-elementor-lite' ),
				'tab' => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_thumbnail' => 'yes',
				],
			]
		);

		$this->add_control(
			'image_border_radius',
			[
				'label' => __( 'Border radius', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::DIMENSIONS,
				'selectors' => [
					'{{WRAPPER}} .athemes-post-item .post-item-thumb img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],     
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_titles',
			[
				'label' => __( 'Post titles', 'athemes-addons-for-elementor-lite' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'post_title_color',
			[
				'label' => __( 'Color', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .item-title a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'post_title_color_hover',
			[
				'label' => __( 'Hover color', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .item-title a:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'post_title_typography',
				'selector' => '{{WRAPPER}} .item-title',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_meta',
			[
				'label' => __( 'Post meta', 'athemes-addons-for-elementor-lite' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'meta_color',
			[
				'label' => __( 'Color', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .post-info:not(.cats-label), {{WRAPPER}} .post-info:not(.cats-label) a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'meta_color_hover',
			[
				'label' => __( 'Hover color', 'athemes-addons-for-elementor-lite' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .post-info a:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'meta_typography',
				'selector' => '{{WRAPPER}} .post-info',
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
	 * @access public
	 */
	protected function render() {
		$settings = $this->get_settings();
		$settings['widget_id'] = $this->get_id();

		$helper = Posts_Helper::instance();

		$query_args = $helper->get_query_args( $settings );

		$query = new \WP_Query( $query_args );  

		if ( $query->have_posts() ) :

			$this->add_render_attribute( 'container', 'class', 'athemes-addons-news-ticker-container' );
			$this->add_render_attribute( 'wrapper', 'class', 'athemes-addons-news-ticker swiper-container' );
			$this->add_render_attribute( 'wrapper', 'data-autoplay-speed', esc_attr( $settings['autoplay_speed'] ) );
			$this->add_render_attribute( 'wrapper', 'data-transition-speed', esc_attr( $settings['transition_speed'] ) );
			
			$page_id = '';
			if ( null !== Plugin::$instance->documents->get_current() ) {
				$page_id = Plugin::$instance->documents->get_current()->get_main_id();
			}

			$this->add_render_attribute( 'wrapper', 'data-page', $page_id );
			?>
			<div <?php $this->print_render_attribute_string( 'container' ); ?>>
				<div class="news-ticker-title title-decoration-<?php echo esc_attr( $settings['title_decoration'] ); ?>">
				<?php if ( ! empty( $settings['icon']['value'] ) ) : ?>
					<span class="news-ticker-icon">
						<?php Icons_Manager::render_icon( $settings['icon'], [ 'aria-hidden' => 'true' ] ); ?>
					</span>
				<?php endif; ?>					
				<?php if ( ! empty( $settings['title'] ) ) : ?>
					<?php $settings['main_title_tag'] = athemes_addons_validate_html_tag( $settings['main_title_tag'] ); ?>
					<<?php echo tag_escape( $settings['main_title_tag'] ); ?> class="title"><?php echo esc_html( $settings['title'] ); ?></<?php echo tag_escape( $settings['main_title_tag'] ); ?>>
				<?php endif; ?>
				</div>
				<div <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
					<div class="swiper-wrapper">
					<?php while ( $query->have_posts() ) : $query->the_post(); ?>
						<?php $this->post_template(); ?>
					<?php endwhile; ?>
					</div>
				</div>
			</div>
			<?php

		endif; //end have_posts() check
		wp_reset_postdata();
	}

	/**
	 * Loop item
	 */
	public function post_template() {
		$settings = $this->get_settings_for_display();

		?>
		<div class="athemes-post-item swiper-slide">
			<?php if ( has_post_thumbnail() && $settings['show_thumbnail'] ) : ?>
			<div class="post-item-thumb">
				<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_post_thumbnail( $settings['image_size'] ); ?></a>
			</div>
			<?php endif; ?>		

			<div class="post-content">
				<?php $settings['title_tag'] = athemes_addons_validate_html_tag( $settings['title_tag'] ); ?>
				<?php the_title( '<' . tag_escape( $settings['title_tag'] ) . ' class="item-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></' . tag_escape( $settings['title_tag'] ) . '>' ); ?>
				<div class="post-info">
					<?php if ( $settings['show_date'] ) : ?>
						<?php athemes_addons_get_post_date(); ?>
					<?php endif; ?>
				</div>	
			</div>	
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