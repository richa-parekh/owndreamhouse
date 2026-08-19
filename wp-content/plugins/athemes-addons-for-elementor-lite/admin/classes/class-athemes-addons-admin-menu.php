<?php
/**
 * Admin_Menu Class.
 */
namespace aThemesAddons;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Admin_Menu' ) ) {

	class Admin_Menu {

		/**
		 * Page title.
		 */
		public $page_title = 'aThemes Addons';

		/**
		 * Plugin slug.
		 */
		public $plugin_slug = 'athemes-addons';

		/**
		 * Plugin capability.
		 */
		public $capability = 'manage_options';

		/**
		 * Plugin priority.
		 */
		public $priority = 58;

		/**
		 * Plugin notifications.
		 */
		public $notifications = array();

		/**
		 * The single class instance.
		 */
		private static $instance = null;

		/**
		 * Instance.
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor.
		 */
		public function __construct() {
			if( $this->is_patcher_page() ) {
				add_action('admin_enqueue_scripts', array( $this, 'enqueue_patcher_scripts' ));
			}

			add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
			add_action( 'wp_ajax_athemes_addons_notifications_read', array( $this, 'ajax_notifications_read' ) );

			add_action('admin_footer', array( $this, 'footer_internal_scripts' ));
		}

		/**
		 * Is aThemes Patcher page.
		 * 
		 * @return bool
		 */
		public function is_patcher_page() {
			global $pagenow;

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return $pagenow === 'admin.php' && ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] === 'athemes-patcher-preview-ap' );
		}

		/**
		 * Enqueue aThemes Patcher preview scripts and styles.
		 * 
		 * @return void
		 */
		public function enqueue_patcher_scripts() {
			wp_enqueue_style( 'wp-components' );
		}

		/**
		 * Include required classes.
		 */
		public function add_admin_menu() {

			global $submenu;

			// Dashboard
			add_menu_page(
				$this->page_title,
				$this->page_title,
				$this->capability,
				$this->plugin_slug,
				array( $this, 'page_dashboard' ),
				ATHEMES_AFE_URI . 'assets/images/athemes-addons-logo.svg',
				$this->priority
			);

			add_submenu_page(
				$this->plugin_slug,
				esc_html__( 'Widgets', 'athemes-addons-for-elementor-lite' ),
				esc_html__( 'Widgets', 'athemes-addons-for-elementor-lite' ),
				'manage_options',
				$this->plugin_slug,
				'',
				1
			);

			add_submenu_page(
				$this->plugin_slug,
				esc_html__( 'Extensions', 'athemes-addons-for-elementor-lite' ),
				esc_html__( 'Extensions', 'athemes-addons-for-elementor-lite' ),
				'manage_options',
				'admin.php?page=' . $this->plugin_slug . '&section=extensions',
				'',
				2
			);

			add_submenu_page(
				$this->plugin_slug,
				esc_html__( 'Settings', 'athemes-addons-for-elementor-lite' ),
				esc_html__( 'Settings', 'athemes-addons-for-elementor-lite' ),
				'manage_options',
				'admin.php?page=' . $this->plugin_slug . '&section=settings',
				'',
				3
			);

			// Add 'aThemes Patcher' link
			add_submenu_page( // phpcs:ignore WPThemeReview.PluginTerritory.NoAddAdminPages.add_menu_pages_add_submenu_page
				$this->plugin_slug,
				esc_html__('Patcher', 'athemes-addons-for-elementor-lite'),
				esc_html__('Patcher', 'athemes-addons-for-elementor-lite'),
				'manage_options',
				'athemes-patcher-preview-ap',
				array( $this, 'html_patcher' ),
				4
			);

			if ( ! defined( 'ATHEMES_AFE_PRO_VERSION' ) ) {
				add_submenu_page(
					$this->plugin_slug,
					esc_html__('Upgrade to Pro', 'athemes-addons-for-elementor-lite'),
					esc_html__('Upgrade to Pro', 'athemes-addons-for-elementor-lite'), 
					'manage_options',
					athemes_addons_admin_upgrade_link( 'https://athemes.com/addons', array( 'utm_source' => 'theme_submenu_page', 'utm_medium' => 'button', 'utm_campaign' => 'Addons' ), 'dashboard-submenu-upgrade-link' ),
					'',
					4
				);
			}
		}

		/**
		 * Get Notifications
		 */
		public function get_notifications() {
			$notifications = get_transient( 'athemes_addons_notifications' );

			if ( ! empty( $notifications ) ) {
				$this->notifications = $notifications;
			} else {

				/**
				 * Hook: athemes_addons_changelog_api_url
				 * 
				 * @since 1.0
				 */

				$response = wp_remote_get( apply_filters( 'athemes_addons_changelog_api_url', 'https://athemes.com/wp-json/wp/v2/notifications?theme=7105&per_page=3' ) );

				if ( ! is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) === 200 ) {
					$this->notifications = json_decode( wp_remote_retrieve_body( $response ) );
					set_transient( 'athemes_addons_notifications', $this->notifications, 24 * HOUR_IN_SECONDS );
				}
			}

			return $this->notifications;
		}

		/**
		 * Check if the latest notification is read
		 */
		public function is_latest_notification_read() {

			if ( ! isset( $this->notifications ) || empty( $this->notifications ) ) {
				return false;
			}
			
			$user_id        = get_current_user_id();
			$user_read_meta = get_user_meta( $user_id, 'athemes_addons_dashboard_notifications_latest_read', true );

			$last_notification_date      = strtotime( is_string( $this->notifications[0]->post_date ) ? $this->notifications[0]->post_date : '' );
			$last_notification_date_ondb = $user_read_meta ? strtotime( $user_read_meta ) : false;

			if ( ! $last_notification_date_ondb ) {
				return false;
			}

			if ( $last_notification_date > $last_notification_date_ondb ) {
				return false;
			}

			return true;
		}

		/**
		 * Ajax notifications.
		 */
		public function ajax_notifications_read() {
			check_ajax_referer( 'athemes-addons-elementor', 'nonce' );

			// Check current user capabilities
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( 'You are not allowed to do this.' );
			}

			$latest_notification_date = ( isset( $_POST[ 'latest_notification_date' ] ) ) ? sanitize_text_field( wp_unslash( $_POST[ 'latest_notification_date' ] ) ) : false;

			update_user_meta( get_current_user_id(), 'athemes_addons_dashboard_notifications_latest_read', $latest_notification_date );

			wp_send_json_success();
		}

		public function page_dashboard() {
			require_once ATHEMES_AFE_DIR . 'admin/pages/page-dashboard.php';
		}

		/**
		 * Dashboard tabs
		 */
		public function dashboard_tabs() {
			$tabs = array(
				'widgets' => array(
					'title' => __( 'Widgets', 'athemes-addons-for-elementor-lite' ),
					'link'  => 'admin.php?page=athemes-addons&section=widgets',
				),
				'theme-builder' => array(
					'title' => __( 'Theme Builder', 'athemes-addons-for-elementor-lite' ),
					'link'  => 'admin.php?page=athemes-addons&section=theme-builder',
				),
				'extensions' => array(
					'title' => __( 'Extensions', 'athemes-addons-for-elementor-lite' ),
					'link'  => 'admin.php?page=athemes-addons&section=extensions',
				),
				
				'settings' => array(
					'title' => __( 'Settings', 'athemes-addons-for-elementor-lite' ),
					'link'  => 'admin.php?page=athemes-addons&section=settings',
				),
				
			);

			if ( \athemes_addons_host_theme_has_template_builder() ) {
				unset( $tabs['theme-builder'] );
			}

			if ( ! defined( 'ATHEMES_AFE_PRO_VERSION' ) ) {
				$tabs['upgrade'] = array(
					'title' => __( 'Free vs Pro', 'athemes-addons-for-elementor-lite' ),
					'link'  => 'admin.php?page=athemes-addons&section=upgrade',
				);
			}
	
			return $tabs;
		}

				/**
		 * Footer internal scripts
		 *
		 * @return void
		 */
		public function footer_internal_scripts() {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? str_replace( '/wp-admin/', '', wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
			
			// Generate upgrade link URL
			$upgrade_url = athemes_addons_admin_upgrade_link( 'https://athemes.com/addons', array( 'utm_source' => 'theme_submenu_page', 'utm_medium' => 'button', 'utm_campaign' => 'Addons' ), 'dashboard-submenu-upgrade-link' );
			?>
			<style>
				#adminmenu .toplevel_page_athemes-addons .wp-submenu li.current a {
                    color: rgba(240, 246, 252, 0.7);
                    font-weight: 400;
                }

				#adminmenu .toplevel_page_athemes-addons .wp-submenu li a[href="<?php echo $request_uri; //phpcs:ignore ?>"] {
					color: #fff;
                    font-weight: 600;
				}

				#adminmenu .toplevel_page_athemes-addons .wp-submenu a[href="<?php echo esc_url( $upgrade_url ); ?>"] {
					color: #05d105;
				}
			</style>
            <script type="text/javascript">
                document.addEventListener("DOMContentLoaded", function () {

					if (typeof URLSearchParams !== 'undefined') {
						const params = new URLSearchParams(window.location.search);
						const section = params.get('section');
						if (section) {
							const tabNavItem = document.querySelector(`.athemes-addons-tab-nav-item[data-tab="${section}"]`);
							if (tabNavItem) {
								const siblings = tabNavItem.parentElement.children;
								for (let sibling of siblings) {
									sibling.classList.remove('active');
								}
								tabNavItem.classList.add('active');
							}
						}
					}

                    const AddonsUpsellMenuItem = document.querySelector('#adminmenu .toplevel_page_athemes-addons .wp-submenu a[href="<?php echo esc_js( $upgrade_url ); ?>"]');

                    if (AddonsUpsellMenuItem) {
                        AddonsUpsellMenuItem.addEventListener('click', function (e) {
                            e.preventDefault();

                            const href = this.getAttribute('href');
                            window.open(href, '_blank');
                        });
                    }
                });
            </script>
			<?php
		}

		/**
		 * HTML aThemes Patcher.
		 *
		 * @return void 
		 */
		public function html_patcher() {
			require_once ATHEMES_AFE_DIR . 'admin/pages/page-patcher.php';
		}
	}

	Admin_Menu::instance();

}
