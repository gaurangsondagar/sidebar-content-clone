<?php
/*
  Plugin Name: Sidebar Content Clone
  Plugin URI: https://wordpress.org/plugins/sidebar-content-clone
  Description: Clone one Sidebar widgets to another sidebar by one click.
  Version: 1.2
  Author: Gaurang Sondagar
  Author URI: http://gaurangsondagar99.wordpress.com/
  License: GPLv2 or later
  License URI: https://www.gnu.org/licenses/gpl-2.0.html
  Text Domain: sidebar-content-clone
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

class Sidebar_Content_Clone {

    public function __construct() {
        // Load text domain
        add_action('plugins_loaded', array($this, 'scc_load_textdomain'));

        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'wp_ajax_clone_widget_area', array( $this, 'clone_widget_area' ) );
    }

    public function scc_load_textdomain() {
        load_plugin_textdomain(
            'sidebar-content-clone',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages'
        );
    }

    public function enqueue_assets( $hook ) {
        if ( 'widgets.php' !== $hook ) {
            return;
        }

        wp_enqueue_script(
            'sidebar-content-clone-js',
            plugin_dir_url( __FILE__ ) . 'assets/js/scc-widget-cloner.js',
            array( 'jquery' ),
            '1.0',
            true
        );

        wp_enqueue_style(
            'sidebar-content-clone-css',
            plugin_dir_url( __FILE__ ) . 'assets/css/scc-widget-cloner.css',
            array(),
            '1.0'
        );

        global $wp_registered_sidebars;
        $sidebars = array();
        foreach ( $wp_registered_sidebars as $sidebar ) {
            $sidebars[ $sidebar['id'] ] = $sidebar['name'];
        }

        wp_localize_script(
            'sidebar-content-clone-js',
            'SidebarCloneData',
            array(
                'sidebars' => $sidebars,
                'nonce'    => wp_create_nonce( 'sidebar_clone_nonce' ),
                'ajaxurl'  => admin_url( 'admin-ajax.php' ),
                'i18n'     => array(
                    'sidebarCloneTitle'     => __('Sidebar Clone Widget Area:', 'sidebar-content-clone'),
                    'sourceLabel'           => __('Source Widget Area:', 'sidebar-content-clone'),
                    'destinationLabel'      => __('Destination Widget Area:', 'sidebar-content-clone'),
                    'selectOption'          => __('Select a widget area', 'sidebar-content-clone'),
                    'cloneButton'           => __('Clone Widgets', 'sidebar-content-clone'),
                    'selectBoth'            => __('Please select both source and destination', 'sidebar-content-clone'),
                    'sameSourceDestination' => __('Source and destination cannot be the same', 'sidebar-content-clone'),
                    'cloning'               => __('Cloning...', 'sidebar-content-clone'),
                    'ajaxError'             => __('Error: ', 'sidebar-content-clone'),
                )
            )
        );
    }

    public function clone_widget_area() {
        check_ajax_referer( 'sidebar_clone_nonce', 'nonce' );

        if ( ! current_user_can( 'edit_theme_options' ) ) {
            wp_send_json_error( __( 'Insufficient permissions.', 'sidebar-content-clone' ) );
        }

        $source      = isset( $_POST['source'] ) ? sanitize_text_field( wp_unslash( $_POST['source'] ) ) : '';
        $destination = isset( $_POST['destination'] ) ? sanitize_text_field( wp_unslash( $_POST['destination'] ) ) : '';


        if ( empty( $source ) || empty( $destination ) ) {
            wp_send_json_error( __( 'Source or destination not specified.', 'sidebar-content-clone' ) );
        }

        if ( $source === $destination ) {
            wp_send_json_error( __( 'Source and destination cannot be the same.', 'sidebar-content-clone' ) );
        }

        $sidebars_widgets = get_option( 'sidebars_widgets' );

        if ( ! isset( $sidebars_widgets[ $source ] ) ) {
            wp_send_json_error( __( 'Source widget area not found.', 'sidebar-content-clone' ) );
        }

        $sidebars_widgets[ $destination ] = array();

        foreach ( $sidebars_widgets[ $source ] as $widget_id ) {
            $type   = preg_replace( '/-[0-9]+$/', '', $widget_id );
            $number = str_replace( $type . '-', '', $widget_id );
            $opts   = get_option( 'widget_' . $type );

            if ( isset( $opts[ $number ] ) ) {
                $new_number = $this->get_next_widget_number( $type );
                $opts[ $new_number ] = $opts[ $number ];
                update_option( 'widget_' . $type, $opts );
                $sidebars_widgets[ $destination ][] = $type . '-' . $new_number;
            }
        }

        update_option( 'sidebars_widgets', $sidebars_widgets );

        wp_send_json_success( __( 'Sidebar cloned successfully.', 'sidebar-content-clone' ) );
    }

    private function get_next_widget_number( $type ) {
        $opts = get_option( 'widget_' . $type );

        if ( ! is_array( $opts ) ) {
            return 1;
        }

        unset( $opts['_multiwidget'] );

        return empty( $opts ) ? 1 : ( max( array_keys( $opts ) ) + 1 );
    }

}

new Sidebar_Content_Clone();
