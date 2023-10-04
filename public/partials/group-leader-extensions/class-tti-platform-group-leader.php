<?php

/**
 * Class to extend Learndashboard Group functionality using this class.
 *
 * This is used to define internationalization, dashboard-specific hooks, and
 * public-facing site hooks.
 *
 * @since   1.5.1
 * @package     TTI_Platform
 * @subpackage  TTI_Platform/includes
 * @author  Presstigers
 */



class TTI_Platform_Group_Leader {

    /**
    * Srray contains graphic list feedback types
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
        $this->tti_platform_public_gl_scripts_styles();

        /* Enqueue scripts and styles */
        $this->tti_platform_gl_enqueue_styles_scripts();

    }

    /**
    * Function to define constants.
    *
    * @since   1.5.1
    */
    public function define_constants() { 
        if(!defined('TTI_PLAT_GL_ASSETS_PATH')) {
            define('TTI_PLAT_GL_ASSETS_PATH', plugin_dir_path( __FILE__ ) . 'assets/');
        }
        if(!defined('TTI_PLAT_GL_IMG_ASSETS_PATH')) {
            define('TTI_PLAT_GL_IMG_ASSETS_PATH', plugins_url( ) . '/tti-platform/public/partials/group-leader-extensions/assets/images/');
        }
    }


    /**
     * Function to register fronend styles and scripts.
     *
     * @since   1.5.1
     */
    public function tti_platform_public_gl_scripts_styles() { 
        if(!is_admin()) {

            /* General CSS and JS */
            wp_register_style( 'tti_platform_public_group_leader_css', plugin_dir_url(__FILE__) . 'assets/css/tti-platform-group-leader.css', array(), $this->generateRandomString(), 'all' );

            wp_register_style( 'tti_platform_as_public_app_dashboard_gl_jq',  'https://cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css', array(), $this->generateRandomString(), 'all' );

            wp_register_style( 'tti_platform_as_public_app_dashboard_gl_responsive',  'https://cdn.datatables.net/responsive/2.2.3/css/responsive.dataTables.min.css', array(), $this->generateRandomString(), 'all' );

            wp_register_style( 'tti_platform_admin_style_sweetalert_gl', 'https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.33.1/sweetalert2.css', array(), $this->generateRandomString(), 'all' );

            wp_register_script( 'tti_platform_as_public_scr_gl_jquery',  'https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js', array('jquery'), $this->generateRandomString(), 'all' );

            wp_register_script( 'tti_platform_as_public_gl_datatables_res',  'https://cdn.datatables.net/responsive/2.2.3/js/dataTables.responsive.min.js', array(), $this->generateRandomString(), 'all' );

            wp_register_script( 'tti_platform_as_public_scr_group_leader',  plugin_dir_url(__FILE__) . 'assets/js/tti-platform-group-leader.js', array('jquery'), $this->generateRandomString(), 'all' );
           
            
            wp_register_script( 'tti_platform_admin_script_sweetalert_gl', 'https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.33.1/sweetalert2.all.min.js', array('jquery'), $this->generateRandomString(), 'all' );

            wp_localize_script(
                    'tti_platform_as_public_scr_group_leader', 
                    'tti_platform_public_ajax_obj', 
                    array (
                        'ajaxurl' => admin_url('admin-ajax.php'),
                        'siteurl' => site_url(),
                        'menu_display' => __('Show _MENU_ Users', 'tti-platform'),
                        'zeroRecords' => __('Nothing found - sorry', 'tti-platform'),
                        'info' => __('Showing page _PAGE_ of _PAGES_', 'tti-platform'),
                        'infoEmpty' => __('No records available', 'tti-platform'),
                        'infoFiltered' => __('(filtered from _MAX_ total records)', 'tti-platform'),
                        'Search' => __('Search', 'tti-platform'),
                        'First' => __('First', 'tti-platform'),
                        'Previous' => __('Previous', 'tti-platform'),
                        'Last' => __('Last', 'tti-platform'),
                        'Next' => __('Next', 'tti-platform'),
                        'limit_ends' => __('This group don\'t have any user registration left. Please buy more registrations before allow user to retake assessment.', 'tti-platform')
                    )
            );
        }
    }


     /**
     * Function to enqueue the styles and script.
     *
     * @since       1.5.1
     */
    public function tti_platform_gl_enqueue_styles_scripts() {
        //wp_enqueue_style('tti_platform_as_public_app_dashboard_gl_responsive');
        //wp_enqueue_style('tti_platform_as_public_app_dashboard_gl_jq');
        wp_enqueue_style('tti_platform_public_group_leader_css');
        wp_enqueue_style('tti_platform_admin_style_sweetalert_gl');
        //wp_enqueue_script('tti_platform_as_public_scr_gl_jquery');
        //wp_enqueue_script('tti_platform_as_public_gl_datatables_res');
        wp_enqueue_script('tti_platform_admin_script_sweetalert_gl');
        wp_enqueue_script('tti_platform_as_public_scr_group_leader');
        
    }


     /**
     * Function to generate random string.
     * @since   1.0.0
     * @access  public
     * @param integer $length contains length of string
     * @return string return random generated string
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
new TTI_Platform_Group_Leader();
