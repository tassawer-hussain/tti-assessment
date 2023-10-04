<?php

/**
 * Class for complete profiles functionality.
 *
 * This is used to define complete profiles internationalization, dashboard-specific hooks, and
 * public-facing site hooks.
 *
 * @since   1.5.1
 * @package     TTI_Platform
 * @subpackage  TTI_Platform/includes
 * @author  Presstigers
 */



class TTI_Platform_Completed_Profiles {

    /**
    * array contains graphic list feedback types
    * @var array
    */
    public $data;

    /**
     * Define the core functionality of the plugin for frontend.
     *
     * @since       1.5.1
     */
    public function __construct() {

        $this->data = array();

        /*
         * Fronend Styles and Scripts Initialization
         */
        //add_action('wp_enqueue_scripts', array($this, 'tti_platform_public_cp_scripts_styles'), 1);

        /* Display the front end */
        $this->define_constants();

        /* Enqueue scripts and styles */        
        $this->tti_platform_public_cp_scripts_styles();

        /* Enqueue scripts and styles */
        $this->tti_platform_cp_enqueue_styles_scripts();

        /* Get users data */
        $this->get_current_leader_users_data();

        /* Display the front end */
        $this->output_cp_tables();
    }

    /**
    * Function to define constants.
    *
    * @since   1.5.1
    */
    public function define_constants() {
        if(!defined('TTI_PLAT_CP_ASSETS_PATH')) {
            define('TTI_PLAT_CP_ASSETS_PATH', plugin_dir_path( __FILE__ ) . 'assets/');
        }
    }


    /**
     * Function to register frontend styles and scripts.
     *
     * @since   1.5.1
     */
    public function tti_platform_public_cp_scripts_styles() { 
        if(!is_admin()) {

            /* General CSS and JS */
            wp_register_style( 'tti_platform_public_completed_profiles', plugin_dir_url(__FILE__) . 'assets/css/tti-platform-completed-profiles.css', array(), $this->generateRandomString(), 'all' );

            wp_register_style( 'tti_platform_as_public_app_dashboard_cp_jq',  'https://cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css', array(), $this->generateRandomString(), 'all' );

            wp_register_style( 'tti_platform_as_public_app_dashboard_cp_responsive',  'https://cdn.datatables.net/responsive/2.2.3/css/responsive.dataTables.min.css', array(), $this->generateRandomString(), 'all' );

            wp_register_script( 'tti_platform_as_public_scr_cp_jquery',  'https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js', array('jquery'), $this->generateRandomString(), 'all' );

            wp_register_script( 'tti_platform_as_public_cp_datatables_res',  'https://cdn.datatables.net/responsive/2.2.3/js/dataTables.responsive.min.js', array(), $this->generateRandomString(), 'all' );

            wp_register_script( 'tti_platform_as_public_scr_cp_jquery_table',   plugin_dir_url(__FILE__) . 'assets/js/tti-platform-completed-profiles.js', array('jquery'), $this->generateRandomString(), 'all' );

            wp_localize_script(
                    'tti_platform_as_public_scr_cp_jquery_table', 
                    'tti_platform_public_ajax_obj', 
                    array (
                        'ajaxurl' => admin_url('admin-ajax.php'),
                        'siteurl' => site_url(),
                        'menu_display' => __('Display _MENU_ entries', 'tti-platform-application-screening'),
                        'zeroRecords' => __('Nothing found - sorry', 'tti-platform-application-screening'),
                        'info' => __('Showing page _PAGE_ of _PAGES_', 'tti-platform-application-screening'),
                        'infoEmpty' => __('No records available', 'tti-platform-application-screening'),
                        'infoFiltered' => __('(filtered from _MAX_ total records)', 'tti-platform-application-screening'),
                        'Search' => __('Search', 'tti-platform-application-screening'),
                        'First' => __('First', 'tti-platform-application-screening'),
                        'Previous' => __('Previous', 'tti-platform-application-screening'),
                        'Last' => __('Last', 'tti-platform-application-screening'),
                        'Next' => __('Next', 'tti-platform-application-screening'),
                    )
            );
        }
    }


     /**
     * Function to enqueue the styles and scripts.
     *
     * @since       1.5.1
     */
    public function tti_platform_cp_enqueue_styles_scripts() {
        wp_enqueue_style('tti_platform_public_completed_profiles');
        wp_enqueue_style('tti_platform_as_public_app_dashboard_cp_jq');
        wp_enqueue_style('tti_platform_as_public_app_dashboard_cp_responsive');
        wp_enqueue_script('tti_platform_as_public_scr_cp_jquery');
        wp_enqueue_script('tti_platform_as_public_cp_datatables_res');
        wp_enqueue_script('tti_platform_as_public_scr_cp_jquery_table');
    }

     /**
     * Function to get current leader users data.
     *
     * @since       1.5.1
     */
    public function get_current_leader_users_data() {
        /* include completed profile class */
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-tti-platform-cp-users.php';
        if(is_user_logged_in()){
            /* Proceed only if user logged in */
            $users_of_leader = new TTI_Platform_Cp_Users();
            $users_of_leader->init_get_users_data(); 
            $this->data = $users_of_leader->users_details;
  
        } else {
            _e('Please logged in to see your group users.', 'tti-platform');
        }
        
    }

     /**
     * Function to display the output data.
     *
     * @since       1.5.1
     */
    public function output_cp_tables() {

        $data = $this->data;
        /* include completed profile class */
        require_once plugin_dir_path( __FILE__ ) . 'front/class-tti-platform-cp-front.php';
    }

     /**
     * Function to generate random string.
     *
     * @since   1.0.0
     * @access  public
     * @param integer $length contains 
     * @return string returns random generated string
     */

    public function generateRandomString( $length = 20 )  {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

}

/* initialize pubic main class */
new TTI_Platform_Completed_Profiles();
