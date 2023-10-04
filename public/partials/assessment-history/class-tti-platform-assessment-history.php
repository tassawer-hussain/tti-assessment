<?php

/**
 * Class to contains assessment history functionality.
 *
 * This is used to define assessment history relatedinternationalization, dashboard-specific hooks, and
 * public-facing site hooks.
 *
 * @since   1.6
 * @package     TTI_Platform
 * @subpackage  TTI_Platform/includes
 * @author  Presstigers
 */



class TTI_Platform_Assessment_History {

    /**
    * Contains user id
    * @var integer
    */
    public $user_id;

   /**
    * Contains assessment link id
    * @var string
    */
    public $link_id;

    /**
    * Contains assessment id
    * @var integer
    */
    public $assess_id;

    /**
    * Contains user assessment data
    * @var array
    */
    public $data;

    /**
    * Contains if link show or not
    * @var boolean
    */
    public $show_as_link;

    /**
     * Define the core functionality of the plugin for frontend.
     *
     * @since       1.6
     * @param integer $user_id contains user id
     * @param string $link_id contains assessment link id
     * @param integer $assess_id contains assessment id
     * @param boolean $show_link contains if link show or not
     */
    public function __construct($user_id, $link_id, $assess_id, $show_link) {

        $this->user_id = $user_id;
        $this->link_id = $link_id;
        $this->assess_id = $assess_id;
        $this->show_as_link = $show_link;

        $this->data = array();
        
        /* Display the front end */
        $this->define_ah_constants();

        /* Enqueue scripts and styles */        
        $this->tti_platform_public_ah_scripts_styles();

        /* Enqueue scripts and styles */
        $this->tti_platform_ah_enqueue_styles_scripts();

    }

    /**
    * Function to define constants.
    *
    * @since   1.6
    */
    public function define_ah_constants() { 
        if(!defined('TTI_PLAT_AH_ASSETS_PATH')) {
            define('TTI_PLAT_AH_ASSETS_PATH', plugin_dir_path( __FILE__ ) . 'assets/');
        }
        if(!defined('TTI_PLAT_AH_IMG_ASSETS_PATH')) {
            define('TTI_PLAT_AH_IMG_ASSETS_PATH', plugins_url( ) . '/tti-platform/public/partials/assessment-history/assets/images/');
        }
    }


    /**
     * Function to register fronend styles and scripts.
     *
     * @since   1.6
     */
    public function tti_platform_public_ah_scripts_styles() { 
        if(!is_admin()) {

            /* General CSS and JS */
            wp_register_style( 'tti_platform_public_assess_history_css', plugin_dir_url(__FILE__) . 'assets/css/tti-platform-assessment-history.css', array(), $this->generateRandomString(), 'all' );

            wp_register_script( 'tti_platform_as_public_scr_assess_history',  plugin_dir_url(__FILE__) . 'assets/js/tti-platform-assessment-history.js', array('jquery'), $this->generateRandomString(), 'all' );


            wp_localize_script(
                    'tti_platform_as_public_scr_assess_history', 
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
     * Function to enqueue the styles.
     *
     * @since       1.6
     */
    public function tti_platform_ah_enqueue_styles_scripts() {
        wp_enqueue_style('tti_platform_public_assess_history_css');
        wp_enqueue_script('tti_platform_as_public_scr_assess_history');
    }

    /**
     * Function to process the assessment history layout and data.
     *
     * @since       1.6
     */
    public function show_assessment_history() {
       if(isset($this->user_id) && isset($this->link_id)) {
            require_once plugin_dir_path( __FILE__ ) .'includes/class-tti-platform-assessment-history-details.php';
            $get_user_assess_details = new TTI_Platform_Assess_History_Details();
            $this->data = $get_user_assess_details->get_user_details($this->user_id, $this->link_id);
            return $this->show_assessment_history_template();
       }
    }

     /**
     * Function to add assessment history template.
     *
     * @since       1.6
     */
    public function show_assessment_history_template() {
       $data = $this->data;
       $assess_id = $this->assess_id;
       $show_as_link = $this->show_as_link;
       ob_start();                      // start capturing output
       require_once plugin_dir_path( __FILE__ ) .'templates/tti-platform-assessment-history-tmp.php';
       $content = ob_get_contents();    // get the contents from the buffer
       ob_end_clean();                  // stop buffering and discard contents
       return $content;
    }


     /**
     * Function to random string generator.
     * @since   1.0.0
     * @access  public
     * @param integer $length contains length of string to be generated
     * @return string returns unique generator string
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
