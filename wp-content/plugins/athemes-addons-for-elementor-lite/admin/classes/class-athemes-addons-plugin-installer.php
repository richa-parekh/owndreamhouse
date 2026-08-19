<?php

/**
 * Plugin Installer.
 * This class is responsible for installing and activating plugins. This could be from wp.org and external sources.
 * 
 * @package aThemes_Addons
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'aThemes_Addons_Plugin_Installer' ) ) {

    class aThemes_Addons_Plugin_Installer {

        /**
         * Constructor.
         * 
         */
        public function __construct() {
            add_action( 'admin_init', array( $this, 'init' ) );
        }

        /**
         * Initialize the class.
         * 
         * @return void
         */
        public function init() {
            if ( ! is_admin() ) {
                return;
            }

            if ( ! current_user_can( 'install_plugins' ) ) {
                return;
            }

            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
            add_action( 'wp_ajax_addons_install_plugin', array( $this, 'install_plugin' ) );
            add_action( 'wp_ajax_addons_install_external_plugin', array( $this, 'install_external_plugin' ) );
        }

        /**
         * Enqueue scripts.
         * 
         * @return void
         */
        public function enqueue_scripts() {
            wp_enqueue_script( 'addons-plugin-installer', ATHEMES_AFE_URI . '/assets/js/admin/plugin-installer.min.js', array( 'jquery' ), ATHEMES_AFE_VERSION, true );

            wp_localize_script( 'addons-plugin-installer', 'addonsPluginInstallerConfig', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( 'addons_plugin_installer_nonce' ),
                'i18n' => array(
                    'defaultText' => esc_html__( 'Install and Activate', 'athemes-addons-for-elementor-lite' ),
                    'installingText' => esc_html__( 'Installing...', 'athemes-addons-for-elementor-lite' ),
                    'activatingText' => esc_html__( 'Activating...', 'athemes-addons-for-elementor-lite' ),
                ),
            ) );
        }

        /**
         * Install plugin.
         * This method is responsible for installing plugins from the wp.org.
         * The slug (e.g. `vibe-ai`) is posted by the install button; the main
         * plugin file is derived from it rather than trusted from the
         * request, since a client-supplied path could otherwise be used to
         * activate an arbitrary already-installed plugin file.
         *
         * @return void
         */
        public function install_plugin() {
            check_ajax_referer( 'addons_plugin_installer_nonce', 'nonce' );

            if ( ! current_user_can( 'install_plugins' ) ) {
                wp_send_json_error( array( 'message' => esc_html__( 'You do not have permission to install plugins.', 'athemes-addons-for-elementor-lite' ) ) );
            }

            if ( empty( $_POST['slug'] ) ) {
                wp_send_json_error( array( 'message' => esc_html__( 'Plugin slug is required.', 'athemes-addons-for-elementor-lite' ) ) );
            }

            $slug        = sanitize_key( wp_unslash( $_POST['slug'] ) );
            $plugin_path = $slug . '/' . $slug . '.php';

            if ( ! function_exists( 'plugins_api' ) ) {
                require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
            }

            if ( ! class_exists( 'Plugin_Upgrader' ) ) {
                require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
            }

            if ( ! function_exists( 'activate_plugin' ) ) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }

            $status = self::get_plugin_status( $plugin_path );

            if ( 'not_installed' === $status ) {

                $api = plugins_api(
                    'plugin_information',
                    array(
                        'slug'   => $slug,
                        'fields' => array(
                            'short_description' => false,
                            'sections'          => false,
                            'requires'          => false,
                            'rating'            => false,
                            'ratings'           => false,
                            'downloaded'        => false,
                            'last_updated'      => false,
                            'added'             => false,
                            'tags'              => false,
                            'compatibility'     => false,
                            'homepage'          => false,
                            'donate_link'       => false,
                        ),
                    )
                );

                if ( is_wp_error( $api ) ) {
                    wp_send_json_error( array( 'message' => $api->get_error_message() ) );
                }

                // Use the AJAX upgrader skin instead of the plugin installer
                // skin, matching core's wp_ajax_install_plugin().
                $upgrader  = new Plugin_Upgrader( new WP_Ajax_Upgrader_Skin() );
                $installed = $upgrader->install( $api->download_link );

                if ( is_wp_error( $installed ) || false === $installed ) {
                    wp_send_json_error( array( 'message' => esc_html__( 'Failed to install the plugin.', 'athemes-addons-for-elementor-lite' ) ) );
                }

                $status = self::get_plugin_status( $plugin_path );
            }

            if ( 'inactive' === $status ) {

                $activated = activate_plugin( $plugin_path, '', false, true );

                if ( is_wp_error( $activated ) ) {
                    wp_send_json_error( array( 'message' => $activated->get_error_message() ) );
                }

                $status = self::get_plugin_status( $plugin_path );
            }

            if ( 'active' !== $status ) {
                wp_send_json_error( array( 'message' => esc_html__( 'Failed to install or activate the plugin.', 'athemes-addons-for-elementor-lite' ) ) );
            }

            wp_send_json_success(
                array(
                    'message' => esc_html__( 'Plugin activated successfully.', 'athemes-addons-for-elementor-lite' ),
                )
            );
        }

        /**
         * Get the install/activation status of a plugin.
         *
         * @param string $plugin_path Plugin path relative to the plugins directory, e.g. `vibe-ai/vibe-ai.php`.
         * @return string 'active', 'inactive', or 'not_installed'.
         */
        public static function get_plugin_status( $plugin_path ) {

            if ( ! current_user_can( 'install_plugins' ) ) {
                return 'not_installed';
            }

            if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }

            if ( ! file_exists( WP_PLUGIN_DIR . '/' . $plugin_path ) ) {
                return 'not_installed';
            }

            if ( in_array( $plugin_path, (array) get_option( 'active_plugins', array() ), true ) || is_plugin_active_for_network( $plugin_path ) ) {
                return 'active';
            }

            return 'inactive';
        }

        /**
         * Install external plugin.
         * This method is responsible for installing plugins from external sources.
         * 
         * @return void
         */
        public function install_external_plugin() {
            check_ajax_referer( 'addons_plugin_installer_nonce', 'nonce' );

            if ( ! current_user_can( 'install_plugins' ) ) {
                wp_send_json_error( array( 'message' => esc_html__( 'You do not have permission to install plugins.', 'athemes-addons-for-elementor-lite' ) ) );
            }

            if ( empty( $_POST['url'] ) ) {
                wp_send_json_error( array( 'message' => esc_html__( 'Plugin URL is required.', 'athemes-addons-for-elementor-lite' ) ) );
            }

            $url = esc_url_raw( wp_unslash( $_POST['url'] ) );
            
            if ( empty( $_POST['plugin_name'] ) ) {
                wp_send_json_error( array( 'message' => esc_html__( 'Plugin name is required.', 'athemes-addons-for-elementor-lite' ) ) );
            }

            $plugin_name = sanitize_text_field( wp_unslash( $_POST['plugin_name'] ) );

            include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
            require_once ATHEMES_AFE_DIR . 'admin/classes/class-athemes-addons-silent-upgrader-skin.php';
            
            $upgrader = new Plugin_Upgrader( new aThemes_Addons_Silent_Upgrader_Skin() );
            $install = $upgrader->install( $url );       

            if ( is_wp_error( $install ) ) {
                wp_send_json_error( array( 'message' => $install->get_error_message() ) );
            }

            $activate = activate_plugin( $plugin_name );

            if ( is_wp_error( $activate ) ) {
                wp_send_json_error( array( 'message' => $activate->get_error_message() ) );
            }

            wp_send_json_success( 
                array( 
                    'message' => esc_html__( 'Plugin activated successfully.', 'athemes-addons-for-elementor-lite' ), 
                ) 
            );
        }
    }

    new aThemes_Addons_Plugin_Installer();
}