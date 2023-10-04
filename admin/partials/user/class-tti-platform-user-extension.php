<?php
/**
 * Class to extend the user functionality.
 *
 * This class is used to define main user related functionality in WordPress admin user's profile.
 *
 * @since   1.0.0
 * @package     TTI_Platform
 * @subpackage  TTI_Platform/includes
 * @author  Presstigers
 */


class TTI_Platform_Admin_User_Extension_Class {

    /**
    * String contains user id
    * @var string
    */
    private $user_id;

    /**
    * array contains assessments lists
    * @var array
    */
    protected $list_assessments;

    /**
    * contains list page action
    * @var string
    */
    public $list_action;
   

    /**
     * Constructor function to class initialize properties and hooks.
     *
     * @since       1.7.0
     */
    public function __construct() { 
        $this->user_id = $this->get_user_id(); // set user id property
        add_action( 'admin_menu' ,array( $this, 'user_profile_menu' ), 150 );
        //add_action( 'admin_init' , array($this, 'tti_platform_check_user_page'));
        //add_filter( 'admin_init' , array($this, 'display_my_submenu'));
        add_action( 'admin_init' , array($this, 'tti_platform_admin_user_scripts'));
        add_action( 'edit_user_profile', array( $this, 'tti_platform_user_template'), 999, 1);
        add_action( 'show_user_profile', array( $this, 'tti_platform_user_template'), 999, 1);

        /* Add mapping page */
        add_action('admin_menu', array($this, 'tti_add_menu'));

        /* AJAX to validate user assessment */
        add_action('wp_ajax_validate_user_assessment', array($this, 'tti_validate_user_assessment'));
        add_action('wp_ajax_nopriv_validate_user_assessment', array($this, 'tti_validate_user_assessment'));

        /* AJAX to validate user assessment */
        add_action('wp_ajax_update_user_assessment', array($this, 'tti_update_user_assessment'));
        add_action('wp_ajax_nopriv_update_user_assessment', array($this, 'tti_update_user_assessment'));

        /* AJAX to insert user assessment */
        add_action('wp_ajax_insert_user_assessments', array($this, 'tti_insert_user_assessments'));
        add_action('wp_ajax_nopriv_insert_user_assessments', array($this, 'tti_insert_user_assessments'));

        /* AJAX to insert user assessment */
        add_action('wp_ajax_save_mapping_data', array($this, 'tti_save_mapping_data'));
        add_action('wp_ajax_nopriv_save_mapping_data', array($this, 'tti_save_mapping_data'));

        /* AJAX to insert user assessment settings */
        add_action('wp_ajax_insert_user_asses_settings', array($this, 'tti_insert_user_asses_settings'));
        add_action('wp_ajax_nopriv_insert_user_asses_settings', array($this, 'tti_insert_user_asses_settings'));

    }

    /**
     * Function to validate given user assessment
     *
     * @since   1.7.0
     */
    public function tti_insert_user_asses_settings( ) {

        require plugin_dir_path( __FILE__ ) . 'includes/class-tti-platform-user-ext-ajax.php';
        
        $ajax_obj = new TTI_Platform_Admin_User_Ext_Ajax_Class();
        $ajax_obj->tti_save_user_assessment_sett();
        
    }

    /**
     * Function to validate given user assessment
     *
     * @since   1.7.0
     */
    public function tti_validate_user_assessment( ) {

        require plugin_dir_path( __FILE__ ) . 'includes/class-tti-platform-user-ext-ajax.php';
        
        $ajax_obj = new TTI_Platform_Admin_User_Ext_Ajax_Class();
        $ajax_obj->tti_validate_user_assessment();
        
    }

    /**
     * Function to validate given user assessment
     *
     * @since   1.7.0
     */
    public function tti_insert_user_assessments( ) {

        require plugin_dir_path( __FILE__ ) . 'includes/class-tti-platform-user-ext-ajax.php';
        
        $ajax_obj = new TTI_Platform_Admin_User_Ext_Ajax_Class();
        $ajax_obj->tti_insert_user_assessments();
        
    }

    /**
     * Function to validate given user assessment
     *
     * @since   1.7.0
     */
    public function tti_update_user_assessment( ) {

        require plugin_dir_path( __FILE__ ) . 'includes/class-tti-platform-user-ext-ajax.php';
        
        $ajax_obj = new TTI_Platform_Admin_User_Ext_Ajax_Class();
        $ajax_obj->tti_update_user_assessment();
        
    }

    /**
     * Function to validate given user assessment
     *
     * @since   1.7.0
     */
    public function tti_save_mapping_data( ) {

        require plugin_dir_path( __FILE__ ) . 'includes/class-tti-platform-mapping-handler.php';
        
        $ajax_obj = new TTI_Platform_Mapping_Handler();
        $ajax_obj->tti_update_mapping_data();
        
    }

    /**
     * Function to add pages related to user functionality
     *
     * @since   1.7.0
     */
    public function tti_add_menu () {
        add_submenu_page (
            'edit.php?post_type=tti_assessments',  __('Mappings', 'tti-platform'), __('Mapping', 'tti-platform'), 'manage_options', 'ttiplatform_mappings', array($this, 'tti_mapping_page')
        );
    }

    /**
     * Function to process mapping page
     *
     * @since   1.7.0
     */
    public function tti_mapping_page () {
       require plugin_dir_path( __FILE__ ) . 'includes/class-tti-platform-mapping-handler.php';
        
       $ajax_obj = new TTI_Platform_Mapping_Handler();
       $map_data = $ajax_obj->return_mapping_data();
       
       require_once plugin_dir_path( __FILE__ ) . 'templates/tti-platform-user-mappings.php';
    }


     /**
     * Function to register scripts and styles related to user partial functionality 
     *
     * @since   1.7.0
     */
    public function tti_platform_admin_user_scripts( ) {
        
        wp_enqueue_style (
            'tti_platform_admin_user_style', plugin_dir_url(__FILE__) . 'assets/css/tti-platform-user-extension.css', 
            array(), 
            $this->generateRandomString(), 
            'all'
        );

        wp_enqueue_style (
            'tti_platform_admin_mapping', plugin_dir_url(__FILE__) . 'assets/css/tti-platform-mapping.css', 
            array(), 
            $this->generateRandomString(), 
            'all'
        );

        wp_enqueue_style(
            'tti_platform_admin_style_sa_userassess', 
            'https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.33.1/sweetalert2.css', 
            array(), $this->generateRandomString(), 
            'all'
        );

        wp_enqueue_script (
            'tti_platform_admin_user_ext_scripts', 
            plugin_dir_url(__FILE__) . 'assets/js/tti-platform-user-extension.js', 
            array('jquery'), 
            $this->generateRandomString(), 
            true
        );

        wp_enqueue_script (
            'tti_platform_mapp_scripts', 
            plugin_dir_url(__FILE__) . 'assets/js/tti-platform-mapping.js', 
            array('jquery'), 
            $this->generateRandomString(), 
            true
        );

        wp_enqueue_script (
            'tti_platform_admin_script_sa_userassess', 
            'https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.33.1/sweetalert2.all.min.js', 
            array(), 
            $this->generateRandomString(), 
            true
        );

        wp_localize_script (
            'tti_platform_admin_user_ext_scripts',
            'tti_platform_admin_user_obj',
            array( 
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
                'siteurl' => site_url()
            )
        );
        
    }

    /**
     * Register the assessment page for user profile tab
     *
     * @since 1.7.0
     *
     */
    public function user_profile_menu() { 
        // Register the hidden submenu.
        add_submenu_page (
            'profile.php' // Use the parent slug as usual.
            , null
            , ''
            , 'manage_options'
            , 'tti-profile-assessment-page'
            , array($this, 'tti_display_user_assessments')
        );
    }

    /**
     * Function to check request to user page content
     *
     * @since   1.7.0
     */
    public function tti_display_user_assessments( ) {
        $user_id = $this->user_id;
        $settings_data = $this->get_assess_settings();
        $profile_url = get_edit_user_link( $this->user_id );
        $tab_url = get_site_url().'/wp-admin/users.php?user_id='.$this->user_id.'&page=tti-profile-assessment-page'; 
        $this->tti_platform_decide_action();
        if($this->list_action == 'edit') {
            /* edit assessment action form */
            $this->tti_platform_handle_edit_action();
        } elseif($this->list_action == 'delete') {
            /* edit assessment action form */
            $this->tti_platform_handle_del_action();
        } else {
            /* display assessments list */
            $this->tti_platform_add_wp_lists();
            $ass_lists = $this->get_assess_list();
            $lists_obj = new TTI_Platform_Admin_WP_Lists_Class($ass_lists);
            require_once plugin_dir_path( __FILE__ ) . 'templates/tti-platform-user-templates.php';
        }
        
    }

    /**
     * Function to handle edit assessment action
     *
     * @since   1.7.0
     * @return array
     */
    public function get_assess_settings() {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-tti-platform-assess-handler.php';
        $ass_hndlr_obj = new TTI_Platform_Admin_User_Ass_Handler_Class();
        $settings_data = $ass_hndlr_obj->tti_return_assessments_settings($this->user_id);
        return $settings_data;
    }

    /**
     * Function to handle edit assessment action
     *
     * @since   1.7.0
     * @return array
     */
    public function get_assess_list( ) {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-tti-platform-assess-handler.php';
        $ass_hndlr_obj = new TTI_Platform_Admin_User_Ass_Handler_Class();
        $lists = $ass_hndlr_obj->tti_return_assessments_curr_user($this->user_id);
        return $lists;
    }

    /**
     * Function to handle edit assessment action
     *
     * @since   1.7.0
     * @return array
     */
    public function tti_platform_handle_edit_action () {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-tti-platform-assess-handler.php';
        $ass_hndlr_obj = new TTI_Platform_Admin_User_Ass_Handler_Class();
        $result = $ass_hndlr_obj->tti_show_edit_user_form($this->user_id);
    }

    /**
     * Function to handle delete assessment action
     *
     * @since   1.7.0
     * @return array
     */
    public function tti_platform_handle_del_action() {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-tti-platform-assess-handler.php';
        $ass_hndlr_obj = new TTI_Platform_Admin_User_Ass_Handler_Class();
        $result = $ass_hndlr_obj->tti_delete_user_assessment( $this->user_id );
        if($result) {
            $this->render_notice('true');
        } else {
            $this->render_notice('false');
        }
    }

     /**
     * Function to render action message
     *
     * @since   1.7.0
     * @return array
     */
    function render_notice($msg) {
        if($msg == 'true') {

            echo '<a href="'.get_site_url().'/wp-admin/users.php?page=tti-profile-assessment-page&user_id='.$this->user_id.'">Back To Assessments</a>
            <div class="tti-ass-del-success"><p><strong>Success</strong>: Assessment deleted successfully</p></div>';
        } else {
            echo '<a href="'.get_site_url().'/wp-admin/users.php?page=tti-profile-assessment-page&user_id='.$this->user_id.'">Back To Assessments</a>
            <div class="tti-ass-del-error"><p><strong>Error</strong>: Assessment deletion failed</p></div>';
        }
        
    }


    /**
     * Function to decide list page action
     *
     * @since   1.7.0
     * @return array
     */
    public function tti_platform_decide_action ( ) {
        $this->list_action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
    }

    /**
     * Function to add logic files related to WP_List_Table
     *
     * @since   1.7.0
     * @return array
     */
    public function tti_platform_add_wp_lists ( ) {
        /**
         * Autoload Classes
         */
        // require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-list-table.php';
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-tti-platform-wp-lists.php';
    }

    /**
     * Function to register the button
     *
     * @since   1.7.0
     * @param array $buttons contains buttons
     * @return array
     */
    public function tti_platform_user_template ( $buttons ) {
        $position_css = '';


        if(is_plugin_active('buddypress/bp-loader.php')) {
            $position_css = 'left: 17.4%;';
        } else {
            $position_css = '
                border-bottom: 1px solid #ccc;
                margin: 0;
                padding-top: 9px;
                padding-bottom: 0;
                line-height: inherit;';
        }
        $tab_url = get_site_url().'/wp-admin/users.php?user_id='.$this->user_id.'&page=tti-profile-assessment-page'; 
        ?>
        <h2 id="tti-profile-user-nav" class="tti-nav-tab-wrapper" style="<?php echo esc_attr($position_css); ?>">
            <?php
            if ( current_user_can( 'edit_user', $this->user_id ) ) : ?>
                <a class="tti-nav-tab" href="<?php echo esc_url($tab_url); ?>"><?php _e( 'Assessment', 'tti-platform' ); ?></a>
            <?php endif; ?>
        </h2>
        <?php
    }

     /**
     * Function to generate random string given by length.
     *
     * @since   1.7.0
     * @param string $length contains length of string (Default is 30)
     * @return string returns generated key according to length given
     */
    public function generateRandomString($length = 30) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

     /**
     * Get the user ID.
     *
     * Look for $_GET['user_id']. If anything else, force the user ID to the
     * current user's ID so they aren't left without a user to edit.
     *
     * @since 1.7.0
     *
     * @return int
     */
    private function get_user_id() {
       
        $this->user_id = (int) get_current_user_id();

        // We'll need a user ID when not on self profile.
        if ( ! empty( $_GET['user_id'] ) ) {
            $this->user_id = (int) $_GET['user_id'];
        }

        return $this->user_id;
    }
}

/* Initialize the admin user class */
new TTI_Platform_Admin_User_Extension_Class();