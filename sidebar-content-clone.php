<?php
/**
  Plugin Name: Sidebar Content Clone
  Plugin URI: https://wordpress.org/plugins/sidebar-content-clone
  Description: Clone one Sidebar widgets to another sidebar by one click.
  Author: Gaurang Sondagar
  Author URI: http://gaurangsondagar99.wordpress.com/
  Copyright: Gaurang Sondagar
  Version: 1.1
  Requires at least: 4.0
  Tested up to: 5.3
  License: GPLv2 or later
 */

/**
 * Exit if accessed directly
 */
if (!defined('ABSPATH')) {
    exit;
}

    /**
     * Constructor for sidebar content clone class
     */
    add_filter( 'admin_head', 'clone_sidebar_contents' );
    add_action( 'plugins_loaded', 'sidebar_content_clone_translate' );

        
        /**
         * Function for language translations
         */
        function sidebar_content_clone_translate() {

            load_plugin_textdomain('sidebar_content_clone', false, basename(dirname( __FILE__ ) ) . '/languages' );

        }

        /**
         * Function for sidebar clone
         * @global type $pagenow
         * @return type
         */
	function clone_sidebar_contents() {

		global $pagenow;

		if( $pagenow != 'widgets.php' ) {
			return;
                }

            ?>
            <style>
                .sbc_wrap a.clone-sidebar-action {
                    text-decoration: none;
                    border-radius: 3px;
                    color: #ffffff;
                    padding: 5px 10px;
                    vertical-align: middle;
                    margin-left: 10px;
                }

                .sbc_wrap a.clone-sidebar-action {
                    background: #0073aa none repeat scroll 0 0;
                }

                .sbc_wrap a.clone-sidebar-action:hover,
                .sbc_wrap a.clear-sidebar:hover {
                    opacity: 0.8;
                }
                .sbc_wrap .sbc_selection_div {
                    display: inline-block;
                    padding: 10px;
                    text-align: left;
                }
                .sbc_wrap .sbc_selection_div span {
                    display: inline-block;
                    font-weight: bold;
                    margin-bottom: 7px;
                    padding-left: 5px;
                }

            </style>
            <script>
            /**
             * Js code for clone sidebar contents
             * @returns {undefined}
             */
            jQuery(document).ready(function(event) {
                    var $sbc_content = '';
                    $sbc_content += '<div class="sbc_wrap">';
                    $sbc_content += '<div class="sbc_selection_div">';
                    $sbc_content += '<span><?php _e('Source Sidebar', 'sidebar_content_clone'); ?></span><br/>';
                    $sbc_content += '<select class="source_sidebar"><option value=""><?php _e('Select Sidebar', 'sidebar_content_clone'); ?></option><?php foreach ($GLOBALS['wp_registered_sidebars'] as $sidebar) { ?><option value="<?php echo $sidebar['id']; ?>"><?php echo ucwords($sidebar['name']); ?></option><?php } ?></select>';
                    $sbc_content += '</div>';
                    $sbc_content += '<div class="sbc_selection_div">';
                    $sbc_content += '<span><?php _e('Destination Sidebar', 'sidebar_content_clone'); ?></span><br/>';
                    $sbc_content += '<select class="destination_sidebar"><option value=""><?php _e('Select Sidebar', 'sidebar_content_clone'); ?></option><?php foreach ($GLOBALS['wp_registered_sidebars'] as $sidebar) { ?><option value="<?php echo $sidebar['id']; ?>"><?php echo ucwords($sidebar['name']); ?></option><?php } ?></select>';
                    $sbc_content += '</div>';
                    $sbc_content += '<a href="#" class="clone-sidebar-action sbc-clone-action"><?php _e('Clone Widgets', 'sidebar_content_clone'); ?></a>';
                    $sbc_content += '</div>';
                    jQuery('#widgets-right').before($sbc_content);
                    
                    jQuery('.clone-sidebar-action').live('click', function() {
                        var $main_wrap = jQuery('#widgets-right');
                        var $source_sidebar_name = jQuery('.source_sidebar').val();
                        var $main_container = $main_wrap.find('#'+$source_sidebar_name);
                        var $dest_sidebar_name = jQuery('.destination_sidebar').val();
                        jQuery($main_container.find('.widget').get()).each(function() {

                            var $sidebar = jQuery(this).clone();
                            var id_base = $sidebar.find('input[name="id_base"]').val();
                            var number = $sidebar.find('input[name="widget_number"]').val();
                            var maximum = 0;
                            jQuery('input.widget-id[value|="' + id_base + '"]').each(function() {
                                    var match = this.value.match(/-(\d+)$/);
                                    if(match && parseInt(match[1]) > maximum)
                                            maximum = parseInt(match[1]);
                            });
                            var newest_num = maximum + 1;
                            $sidebar.find('.widget-content').find('input,select,textarea').each(function () {
                                    if (jQuery(this).attr('name'))
                                        jQuery(this).attr('name', jQuery(this).attr('name').replace(number, newest_num));
                                });
                                jQuery('.widget').each(function () {
                                    var match = this.id.match(/^widget-(\d+)/);
                                    if (match && parseInt(match[1]) > maximum)
                                        maximum = parseInt(match[1]);
                                });
                                var widgetid = maximum + 1;
                                var add = jQuery('#widget-list .id_base[value="' + id_base + '"]').siblings('.add_new').val();
                                $sidebar[0].id = 'widget-' + widgetid + '_' + id_base + '-' + newest_num;
                                $sidebar.find('input.widget-id').val(id_base + '-' + newest_num);
                                $sidebar.find('input.widget_number').val(newest_num);
                                $main_wrap.find('#' + $dest_sidebar_name).append($sidebar);
                                $sidebar.fadeIn();
                                $sidebar.find('.multi_number').val(newest_num);
                                wpWidgets.save($sidebar, 0, 0, 1);
                                jQuery(document).trigger('widget-added', [$sidebar]);
                            });
                            event.preventDefault();
                        });
                    });
           
            </script>
            <?php
	}

