<?php
/**
 * Class to handle include some of files.
 *
 * This is used to define internationalization, dashboard-specific hooks, and
 * public-facing site hooks.
 *
 * @since 	1.0.0
 * @package 	TTI_Platform
 * @subpackage 	TTI_Platform/includes
 * @author 	Presstigers
 */

class TTI_Platform_Main_Class {
    /**
     * Define the core functionality of the plugin.
     *
     * @since 		1.0.0
     */
    public function __construct() {
        $this->load_text_domain();
        add_action( 'init', array( $this, 'load_dependencies' ) );
        add_action( 'admin_menu', array( $this, 'hide_menu_add_new' ) );
    }

    /**
     * Hide the add new link tti assessment submenu
     *
     * @since       1.0.0
     */
    public function hide_menu_add_new () {
       
    }

     /**
     * Load the dependencies
     *
     * @since 		1.0.0
     */
    public function load_dependencies() {
        /**
         * The class responsible for defining all actions that occur in the Dashboard.
         */
    }	

    /**
     * Load the dependencies
     * @since       1.0.0
     */
    public function load_text_domain() {
        /* Load the translation for dependencies */
        load_plugin_textdomain(
          'tti-platform' , false, dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }
}
new TTI_Platform_Main_Class();