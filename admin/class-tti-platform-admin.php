<?php
/**
 * Class to handle main admin functionality.
 *
 * This class is used to define main admin code internationalization and dashboard-specific hooks.
 *
 * @since   1.0.0
 * @package     TTI_Platform
 * @subpackage  TTI_Platform/includes
 * @author  Presstigers
 */


class TTI_Platform_Admin_Main_Class {

    /**
    * Array contains text list feedback types
    * @var array
    */
    public $list_text_feed_array;

    /**
    * Array contains graphic list feedback types
    * @var array
    */
    public $both_text_grpahic_array;

    /**
    * Error log object
    * @var object
    */
    public $error_log;

    /**
     * Constructor function to class initialize properties and hooks.
     *
     * @since       1.0.0
     */
    public function __construct() {

        /* error log */
        $this->error_log = new TTI_Platform_Deactivator_Error_Log();

        add_action('admin_init', array($this, 'init_feed_array'));

        add_action('init', array($this, 'assessment_post_type'));
        add_action('init', array($this, 'tti_platform_users_tab'));
        add_action('admin_init', array($this, 'tti_platform_admin_scripts'));
        add_action('add_meta_boxes', array($this, 'assessment_meta_box'));
        add_action('save_post', array($this, 'assessment_meta_box_information'));
        add_action('admin_menu', array($this, 'add_menu'));
        add_action('wp_ajax_get_assessments', array($this, 'get_assessmentsFunc'));
        add_action('wp_ajax_nopriv_get_assessments', array($this, 'get_assessmentsFunc'));
        add_action('wp_ajax_insert_assessments', array($this, 'insert_assessments'));
        add_action('wp_ajax_nopriv_insert_assessments', array($this, 'insert_assessments'));

        add_action('wp_ajax_get_assessment_can_print_option', array($this, 'get_assessment_can_print_option'));
        add_action('wp_ajax_nopriv_get_assessment_can_print_option', array($this, 'get_assessment_can_print_option'));

       
        /*
         * Ajax hook initialization to get all metadata values assigned to the assessment
         */
        add_action('wp_ajax_get_assessments_metadeta', array($this,'get_assessments_metadeta'));
        add_action('wp_ajax_nopriv_get_assessments_metadeta', array($this,'get_assessments_metadeta'));

        /* Ajax to restore the trashed post */
        add_action('wp_ajax_restore_trashed_post', array($this,'restore_trashed_post'));
        add_action('wp_ajax_nopriv_restore_trashed_post', array($this,'restore_trashed_post'));

        /*
         * Ajax Hook initialization to Get All Assessments as list
         */
        add_action('wp_ajax_list_assessments', array($this,'list_assessments'));
        add_action('wp_ajax_nopriv_list_assessments', array($this,'list_assessments'));
        /*
         * Ajax Hook Initialization Assessment Feedback
         */
        add_action('wp_ajax_list_assessments_for_feedback', array($this,'list_assessments_for_feedback'));
        add_action('wp_ajax_nopriv_list_assessments_for_feedback', array($this,'list_assessments_for_feedback'));
        /*
         * Ajax Hook Initialization PDF Feedback
         */
        add_action('wp_ajax_list_assessments_for_pdf', array($this,'list_assessments_for_pdf'));
        add_action('wp_ajax_nopriv_list_assessments_for_pdf', array($this,'list_assessments_for_pdf'));
        
        /*
         * Ajax Hook Initialization to Get Assessment Metadata Checklist
         */
        add_action('wp_ajax_get_assessments_metadeta_checklist', array($this,'get_assessments_metadeta_checklist'));
        add_action('wp_ajax_nopriv_get_assessments_metadeta_checklist', array($this,'get_assessments_metadeta_checklist'));
        /*
         * Ajax Hook Initialization to Get Generate Secret Key
         */
        add_action('wp_ajax_secret_key', array($this,'secret_key'));
        add_action('wp_ajax_nopriv_secret_key', array($this,'secret_key'));
        /*
         * Ajax Hook Initialization to Save Secret Key
         */
        add_action('wp_ajax_save_secret_key', array($this,'save_secret_key'));
        add_action('wp_ajax_nopriv_save_secret_key', array($this,'save_secret_key'));
        /*
         * Tabs styles & scripts
         */
        add_action('init', array($this, 'tti_platform_admin_scripts_tabs'));
        /*
         * Assessment Permalink Change
         */
        add_action( 'admin_footer', array($this, 'assessment_change_permalink'));
        add_action( 'after_wp_tiny_mce', array($this, 'assessment_tinymce_extra_vars' ));
        add_filter( 'manage_tti_assessments_posts_columns', array($this, 'assessment_add_custom_column'));
        add_action( 'manage_tti_assessments_posts_custom_column', array($this, 'assessment_add_custom_column_data' ), 10, 2);
        add_filter( 'manage_edit-tti_assessments_sortable_columns', array($this, 'assessment_add_custom_column_make_sortable'));
        add_action( 'admin_head-edit.php', array($this, 'remove_password_from_quick_edit' ));
        add_filter( 'bulk_actions-edit-tti_assessments', array( $this , 'assessment_register_bulk_action_active' ));
        add_action( 'admin_action_assessment_activation', array($this, 'assessment_bulk_process_active_status' ));
        add_action( 'admin_notices', array($this , 'assessment_active_order_status_notices'));
        add_filter( 'bulk_actions-edit-tti_assessments', array($this, 'assessment_register_bulk_action_suspended'));
        add_action( 'admin_action_assessment_suspended', array($this, 'assessment_bulk_process_suspended_status' ));
        add_action( 'admin_notices', array($this, 'assessment_suspended_order_status_notices'));
        add_action( 'after_setup_theme', array($this, 'assessment_theme_setup' ));
    }


    /**
    * Initialize list feedback array which contains indexes which we need from ttisi api response.
    *
    * @since 1.2.0
    */
    public function init_feed_array() {
       
       $this->list_text_feed_array = array(
            'INTRO',
            'TITLE',
            'GENCHAR',
            'VAL',
            'DOS',
            'DONTS',
            'COMMTIPS',
            'PERCEPT',
            'BEHAVIOR_AVOIDANCE',
            'NASTYLE',
            'ADSTY',
            'TWASTERS',
            'INTRO12',
            'AREA',
            'MOTGENCHAR',
            'DFSTRWEAK',
            'TRICOACHINTRO2',
            'DFENGSTRESS',
            'INTEGRATIONINTRO_DF',
            'POTENTIALSTR_DR',
            'POTENTIALCONFLIT_DR',
            'MOTIVATINGDR',
            'MANAGINGDR',
            'IDEALENVDR',
            'BLENDINGSADFEQ',
            'BLENDING_DF_INTRO',
            'EQ_INTRO',
            'EQGENCHAR',
            'MOT',
            'MAN',
            'AREA',
            'BFI',
            'IDEALENV',
            'ACTION2'
       ); 
       $this->both_text_grpahic_array = array(
        // 'DES',
        'EQTABLES2',
       );
    }

     /**
     * Add assessment tab to user profile page
     *
     * @since 1.7.0
     */
    public function tti_platform_users_tab () {
        if(is_admin()) {
            require plugin_dir_path( __FILE__ ) . 'partials/user/class-tti-platform-user-extension.php';
        }
    }

    /**
     * Enqueue scripts/styles for admin panel.
     *
     * @since 1.2.0
     */
    public function tti_platform_admin_scripts() {
        if(is_admin() && $this->is_tti_admin_pages()) { /* Only load id is_admin() */
            wp_enqueue_style('tti_platform_admin_style', plugin_dir_url(__FILE__) . 'css/tti-platform-admin.css', array(), $this->generateRandomString(), 'all');
           
            wp_enqueue_style('tti_platform_admin_style_sweetalert', 'https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.33.1/sweetalert2.css', array(), $this->generateRandomString(), 'all');

            wp_enqueue_script('tti_platform_admin_script', plugin_dir_url(__FILE__) . 'js/tti-platform-admin.js', array('jquery'), $this->generateRandomString(), true);

            wp_enqueue_script('tti_platform_admin_script_sweetalert', 'https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.33.1/sweetalert2.all.min.js', array(), $this->generateRandomString(), true);
            
            wp_localize_script(
                'tti_platform_admin_script',
                'tti_platform_admin_ajax_obj',
                array( 
                    'ajaxurl' => admin_url( 'admin-ajax.php' ),
                    'siteurl' => site_url()
                )
           );
        }
    }

    /**
    * Check if admin page opening related to our plugin.
    *
    * @since   1.4.2
    * @return boolean return true if current page is related to this plugin
    */
    function is_tti_admin_pages() {
        global $post, $pagenow;
        
        $post_id = isset($_GET['post']) ?  $_GET['post'] : 0;

        /* if assessment post type page */
        if( strpos($_SERVER['REQUEST_URI'], 'tti_assessments') !== false ||
        strpos($_SERVER['REQUEST_URI'], 'ttiplatform_settings') !== false ){
            return TRUE;
        } elseif(($post_id != 0 && $post_id > 0) || ($pagenow == 'post-new.php')) {
            $p_type = get_post_type($post_id);
            //echo '<pre>';print_r($pagenow);'</pre>'; exit();
            if( $p_type == 'tti_assessments' || 
                $pagenow == 'post-new.php' ||
                $pagenow == 'post.php' ||
                $pagenow == 'edit.php' ) {
                return TRUE;
            }
        }
    }


    /**
     * Enqueue scripts/styles for popup shortcode.
     *
     * @since 1.2.0
     */
    public function tti_platform_admin_scripts_tabs() {
        $actual_link = esc_url_raw("$_SERVER[REQUEST_URI]");
        $arr = explode('/', $actual_link);
        /* Only load if specific page */
        if(isset($arr[count($arr)-1]) && $arr[count($arr)-1] == 'shortcode-popup.php') {
            wp_enqueue_style('tti_platform_admin_style_tabs', plugin_dir_url(__FILE__) . 'css/tti-platform-admin-tabs.css', array(), $this->generateRandomString(), 'all');
            wp_enqueue_script('tti_platform_admin_script_tabs', plugin_dir_url(__FILE__) . 'js/tti-platform-admin-tabs.js', array('jquery'), $this->generateRandomString(), true);
            wp_enqueue_script('tti_platform_admin_script_tinymce', site_url().'/wp-includes/js/tinymce/wp-tinymce.js', array('jquery'), $this->generateRandomString(), true);
            wp_localize_script(
                'tti_platform_admin_script_tabs',
                'tti_platform_admin_script_tabs_ajax_obj',
                array( 
                    'tti_platform_admin_script_tabsajaxurl' => admin_url( 'admin-ajax.php' )
                )
           );
        }
        
    }

    /**
     * Creates a new custom post type (tti_assessments).
     *
     * @since   1.0.0
     */
    public function assessment_post_type() {
        $labels = array(
            'name' => _x('Assessments', 'Post Type General Name', 'tti-platform'),
            'singular_name' => _x('Assessment', 'Post Type Singular Name', 'tti-platform'),
            'menu_name' => __('Assessments', 'tti-platform'),
            'parent_item_colon' => __('Parent Assessment', 'tti-platform'),
            'all_items' => __('All Assessments', 'tti-platform'),
            'view_item' => __('View Assessment', 'tti-platform'),
            'add_new_item' => __('Add New Assessment', 'tti-platform'),
            'add_new' => __('Add New', 'tti-platform'),
            'edit_item' => __('Edit Assessment', 'tti-platform'),
            'update_item' => __('Update Assessment', 'tti-platform'),
            'search_items' => __('Search Assessment', 'tti-platform'),
            'not_found' => __('Not Found', 'tti-platform'),
            'not_found_in_trash' => __('Not found in Trash', 'tti-platform'),
        );

        // Set other options for Custom Post Type

        $args = array(
            'label' => __('assessments', 'tti-platform'),
            'description' => __('', 'tti-platform'),
            'labels' => $labels,
            'supports' => array('title'),
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => false,
            'show_in_admin_bar' => false,
            'menu_position' => 5,
            'can_export' => true,
            'has_archive' => false,
            'exclude_from_search' => false,
            'publicly_queryable' => false,
            'capability_type' => 'page',
        );
        // Registering your Custom Post Type
        register_post_type('tti_assessments', $args);
    }

    /**
     * Add custom admin submenu pages using WordPress hooks.
     *
     * @since   1.0.0
     */
    public function add_menu() {
        add_submenu_page(
            'edit.php?post_type=tti_assessments', __('Add New', 'tti-platform'), __('Add New', 'tti-platform'), 'manage_options', 'ttiplatform_api', array($this, 'page_options')
        );
        add_submenu_page(
            'edit.php?post_type=tti_assessments',  __('Settings', 'tti-platform'), __('Settings', 'tti-platform'), 'manage_options', 'ttiplatform_settings', array($this, 'ttiplatform_setting')
        );
    }


    /**
     * Restore the trashed post by post id.
     *
     * @since   1.0.0
     */
    public function restore_trashed_post() {
        $status = 0;
        if(isset($_POST['post_id'])) {
            $post_id = sanitize_text_field($_POST['post_id']);
            $result = wp_untrash_post($post_id);
            if(count($result) > 0) {
                $status = 1;
            }
        }
        
        $arr = array('status' => $status);
        echo json_encode($arr);
        wp_die();
    }

    /**
     * Admin setting page callback function.
     *
     * @since   1.0.0
     */
    public function page_options() {

        $get_key_option = get_option('ttiplatform_secret_key');
        $get_ttiplatform_secret_key_listener = get_option('ttiplatform_secret_key_listener');

        if(isset($get_key_option) && !empty($get_key_option) && isset($get_ttiplatform_secret_key_listener) && 
            !empty($get_ttiplatform_secret_key_listener)) {
        ?>
        <div class="assessment-wrap-left">
            <div class="assessment-wrap">
                <h2><?php _e( 'Add Assessment', 'tti-platform' ); ?></h2>
                <?php
                    /**
                     * Fires before add assessment form first field
                     * 
                     * @since   1.2
                     */
                    do_action('ttisi_platform_add_assessment_form_before_first_field');
                ?>
                <label for="organization"><strong><?php _e('Title ', 'tti-platform'); ?></strong><span style="color: #929292; display: inline-block"> (optional)</span></label>
                <input type="text" name="organization" id="organization" />
                
                <label for="api_key"><strong><?php _e('API Key', 'tti-platform'); ?></strong><span id="api-info" class="ttiinfo"></span></label>
                <input type="text" name="api_key" id="api_key" class="demoInputBox" />

                <label for="account_login"><strong><?php _e('Account Login', 'tti-platform'); ?></strong><span id="account-info" class="ttiinfo"></span></label>
                <input type="text" name="account_login" id="account_login" class="demoInputBox" />

                <label for="api_service_location"><strong><?php _e('API Service Location', 'tti-platform'); ?></strong><span id="service-info" class="ttiinfo"></span></label>
                <input type="text" name="api_service_location" id="api_service_location" />

                <label for="survay_location"><strong><?php _e('Survey Location', 'tti-platform'); ?></strong><span id="survay-info" class="ttiinfo"></span></label>
                <input type="text" name="survay_location" id="survay_location" class="demoInputBox" />

                 <label for="tti_link_id"><strong><?php _e('Link ID', 'tti-platform'); ?></strong><span id="link-info" class="ttiinfo"></span></label>
                
                <?php if(isset($tti_link_id)) { ?>
                     <input type="text" name="tti_link_id" id="tti_link_id" value="<?php echo esc_attr($tti_link_id); ?>"  />
                <?php } else { ?>
                 <input type="text" name="tti_link_id" id="tti_link_id" value=""  />
                <?php } ?>  

                <?php
                    /**
                     * Fires after add assessment form last field (before Validate button)
                     * 
                     * @since   1.2
                     */
                    do_action('ttisi_platform_add_assessment_form_after_last_field');
                ?>

                <button class="button button-primary button-large" id="validate_assessment"><?php _e('Validate Data', 'tti-platform'); ?></button>
                <span id="status-ok"></span>
                <span id="status-error"><?php _e('This Link Login cannot be added. Please provide a valid details.', 'tti-platform'); ?></span>
                <span id="loader"><img src="<?php echo site_url() . '/wp-content/plugins/tti-platform/admin/images/loader.gif'; ?>" alt="" width="20" /></span>
            </div>

            <div id="afterResponse">
                <?php
                    /**
                     * Fires before assessments links dropdown
                     * 
                     * @since   1.2
                     */
                    do_action('ttisi_platform_before_assessments_links');
                ?>
               
                <?php
                    /**
                     * Fires after assessments links dropdown
                     * 
                     * @since   1.2
                     */
                    do_action('ttisi_platform_after_assessments_links');
                ?>
                <!-- Assessment name -->
                <div class="assessment_name_block" id="assessment_name_block" style="display: none;">
                    <h3><span id="assessment_name_span_head">Assessment Name :</span> <span id="assessment_name_span"></span></h3>
                </div>

                <!-- Assessment locked status -->
                <div class="assessment_locked_status" id="assessment_locked_status" style="display: none;">
                    <h3><span id="assessment_locked_status_head">Assessment Locked Status :</span> <span id="assessment_locked_status_span"></span></h3>
                </div>

                <div class="print_report" id="print_report_settings">
                    <h3><?php _e('Can Print Report?', 'tti-platform'); ?></h3>
                    <input type="radio" name="print_report" id="print_report_yes" value="Yes" /> <label for="print_report_yes"><?php _e('Yes', 'tti-platform'); ?></label>
                    <input type="radio" name="print_report" id="print_report_no" value="No" /> <label for="print_report_no"><?php _e('No', 'tti-platform'); ?></label>
                </div>

                 <!-- Send report to group leaders -->
                 <div class="send_report_to_leader" id="send_report_to_leader" >
                     <h3><?php _e('Send report to group leader', 'tti-platform'); ?></h3>
                    <input type="radio" name="send_rep_group_lead" id="send_rep_group_lead_yes" value="Yes" <?php if($send_rep_group_lead == 'Yes') {echo 'checked';} ?> /> <span for="send_rep_group_lead" style="margin-right: 25px;"><?php _e('Yes', 'tti-platform'); ?></span>
                    <input type="radio" name="send_rep_group_lead" id="send_rep_group_lead_no" value="No" <?php if($send_rep_group_lead == 'No') {echo 'checked';} ?> /> <span for="send_rep_group_lead"><?php _e('No', 'tti-platform'); ?></span>
                </div>
                <!-- ---------------------------- -->

                <!-- Report download option -->
                 <div class="report_api_check" id="report_api_check" >
                     <h3><?php _e('Download report using API', 'tti-platform'); ?></h3>
                    <input type="radio" name="report_api_check" id="report_api_check_yes" value="Yes" <?php if($report_api_check == 'Yes') {echo 'checked';} ?> /> <span for="report_api_check" style="margin-right: 25px;"><?php _e('Yes', 'tti-platform'); ?></span>
                    <input type="radio" name="report_api_check" id="report_api_check_no" value="No" <?php if($report_api_check == 'No') {echo 'checked';} ?> /> <span for="report_api_check"><?php _e('No', 'tti-platform'); ?></span>
                </div>
                <!-- ---------------------------- -->

                <div class="print_report">
                    <?php
                        /**
                         * Fires before Save assessment button
                         * 
                         * @since   1.2
                         */
                        do_action('ttisi_platform_before_assessments_save_button');
                    ?>
                    <div class="add_record_assessment">
                        <button class="button button-primary button-large" id="add_assessment"><?php _e('Save', 'tti-platform'); ?></button>
                        <span id="record_inserted"></span>
                        <span id="loader_insert_assessment"><img src="<?php echo site_url() . '/wp-content/plugins/tti-platform/admin/images/loader.gif'; ?>" alt="" width="20" /></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="assessment-wrap-right">
            <?php
                    /**
                     * Fires before assessments result
                     * 
                     * @since   1.2
                     */
                    do_action('ttisi_platform_before_assessments_result');
                ?>
            <!-- <div id="assessment-result"></div> -->
            <?php
                    /**
                     * Fires after assessments result
                     * 
                     * @since   1.2
                     */
                    do_action('ttisi_platform_after_assessments_result');
                ?>
        </div>
    <?php } else { ?>
        <div class="error_popup_tti">
            <h2><?php _e('You must has to generate a secret key to add new assessments.', 'tti-platform'); ?><a href="<?php echo site_url(); ?>/wp-admin/edit.php?post_type=tti_assessments&page=ttiplatform_settings"><?php _e('Click here to generate key', 'tti-platform'); ?></a></h2>
        </div>
    <?php } 
    }
    
    /**
     * Function to create HTML of admin settings page.
     *
     * @since   1.0.0
     */
    public function ttiplatform_setting() {
        /**
         * Fires before settings page content.
         * 
         * @since   1.2
         */
        do_action('ttisi_platform_settings_page_after');
        ?>
        <div class="assessment-wrap-left">
            <div class="assessment-wrap">
                <h2><?php _e('Settings', 'tti-platform'); ?></h2>
                <div class="ttiplatform_settings">
                    <label for="secret_key"><strong><?php _e('Secret Key', 'tti-platform'); ?></strong></label>
                    <input type="text" name="secret_key" id="secret_key" value="<?php echo esc_attr(get_option('ttiplatform_secret_key')); ?>" disabled="disabled" />
                    <button class="button button-primary button-large" id="generate_secret_key"><?php _e('Generate', 'tti-platform'); ?></button>
                </div>
                <?php
                    /**
                     * Fires after settings page content (before save button).
                     * 
                     * @since   1.2
                     */
                    do_action('ttisi_platform_settings_page_before_save_btn');
                ?>
                <button class="button button-primary button-large" id="save_secret_key"><?php _e('Save', 'tti-platform'); ?></button>
                <span id="loader_insert_assessment" style="float: none;"><img src="<?php echo site_url() . '/wp-content/plugins/tti-platform/admin/images/loader.gif'; ?>" alt="" width="20" /></span>
                <span class="secret_key_response"></span>
                <div class="clear"></div>
                <?php
                   /**
                    * Fires after settings page content (after save button but before noitification)
                    * 
                    * @since   1.2
                    */
                    do_action('ttisi_platform_settings_page_after_save_btn');
                ?>
                <?php $saved_secret_key_listener = get_option('ttiplatform_secret_key_listener');
                    if(isset($saved_secret_key_listener) && !empty($saved_secret_key_listener)) { ?>
                    <div class="return_url">
                        <label for="secret_key"><strong><?php _e('Use following URL as a Return URL', 'tti-platform'); ?></strong></label>
                        <?php echo $saved_secret_key_listener; ?>
                    </div>
                <?php } ?>
            </div>
        </div>
        <?php
           
    }

    /**
     * Function to generate secret key.
     * 
     * @since   1.0.0
     */
    public function secret_key() {
        $randstr = $this->generateRandomString();
        echo json_encode($randstr);
        wp_die();
    }

    /**
     * Function to save secret key.
     *
     * @since   1.0.0
     */
    public function save_secret_key() {
        
        global $post;
        $getStatus = array();
        $secret_key = sanitize_text_field($_POST['secret_key']);

        if(empty($secret_key)) {
            $message = __( 'Error! Please generate secret key first.', 'tti-platform' );
            echo json_encode('<span class="error">'.esc_html($message).'</span>');
        } else {
            $get_key_option = get_option('ttiplatform_secret_key');
            $get_ttiplatform_secret_key_listener = get_option('ttiplatform_secret_key_listener');

            if(isset($get_key_option) && !empty($get_key_option) && $secret_key == $get_key_option) {
               
                // check users assessments
                $this->update_user_levels_urls($get_ttiplatform_secret_key_listener);
               
                $args = array( 'post_type' => 'tti_assessments', 'posts_per_page' => -1 );
                $loop = new WP_Query( $args );
                while ( $loop->have_posts() ) : $loop->the_post();
                    $account_login = get_post_meta($post->ID, 'account_login', true);

                    $link_id = get_post_meta($post->ID, 'link_id', true);
                    $api_key = get_post_meta($post->ID, 'api_key', true);
                    $api_service_location = get_post_meta($post->ID, 'api_service_location', true);
                    
                    /* API < v3.0 url */  
                    //$url = $api_service_location . '/api/accounts/' . $account_login . '/links/' . $link_id;
                    
                    /* API v3.0 url */  
                    $url = $api_service_location . '/api/v3/links/'.$link_id;
                    // Dummy URL:  https://api.ttiadmin.com/api/v3/links/{link_login}

                    $payload = array(
                        "return_url" => $get_ttiplatform_secret_key_listener,
                        "webhook_url" => $get_ttiplatform_secret_key_listener,
                        "express_return" => true
                    );
                    $data = wp_remote_post($url, array(
                            'headers'     => array(
                            'Content-Type' => 'application/json; charset=utf-8', 
                            'Authorization' => $api_key, 
                            'Accept' => 'application/json'
                        ),
                        'body'        => json_encode($payload),
                        'method'      => 'PUT',
                        'data_format' => 'body',
                    ));
                    $getStatus[] = json_decode(wp_remote_retrieve_body($data));
                    
                endwhile;

                if ( is_wp_error( $response ) ) {
                        $error_message = $response->get_error_message();
                        echo json_encode([
                            'error' => $error_message,
                            'status' => '0'
                        ]);
                } else {
                    $message = __( 'Secret key already saved in the system.', 'tti-platform' );
                    echo json_encode([
                        'message' => '<span class="warning">'.$message.'</span>', 
                        'status' => '1'
                    ]);
                }

            } else {
                

                $saved_secret_key = update_option( 'ttiplatform_secret_key', $secret_key );
                $saved_secret_key_listener = update_option( 'ttiplatform_secret_key_listener', site_url().'/listener/?link=$LINK&password=$PASSWORD&key='.$secret_key );

                $get_ttiplatform_secret_key_listener = get_option('ttiplatform_secret_key_listener');
                if($saved_secret_key) {

                    // check users assessments
                    $this->update_user_levels_urls($saved_secret_key_listener);

                    $args = array( 'post_type' => 'tti_assessments', 'posts_per_page' => -1 );
                    $loop = new WP_Query( $args );
                    while ( $loop->have_posts() ) : $loop->the_post();
                        $account_login = get_post_meta($post->ID, 'account_login', true);
                        $link_id = get_post_meta($post->ID, 'link_id', true);
                        $api_key = get_post_meta($post->ID, 'api_key', true);
                        $api_service_location = get_post_meta($post->ID, 'api_service_location', true);
                        
                        /* API v3.0 url */  
                        $url = $api_service_location . '/api/v3/links/'.$link_id;

                        $payload = array(
                            "return_url" => $get_ttiplatform_secret_key_listener,
                            "webhook_url" => $get_ttiplatform_secret_key_listener,
                            "express_return" => true
                        );
                        $data = wp_remote_post($url, array(
                                'headers'     => array(
                                'Content-Type' => 'application/json; charset=utf-8', 
                                'Authorization' => $api_key, 
                                'Accept' => 'application/json'
                            ),
                            'body'        => json_encode($payload),
                            'method'      => 'PUT',
                            'data_format' => 'body',
                        ));
                        $getStatus[] = json_decode(wp_remote_retrieve_body($data));
                        
                    endwhile;

                    if ( is_wp_error( $response ) ) {
                            $error_message = $response->get_error_message();
                            echo json_encode([
                                'error' => $error_message,
                                'status' => '0'
                            ]);
                    } else {
                        $message = __( 'Secret key has been saved successfully.', 'tti-platform' );
                        echo json_encode([
                            'message' => '<span class="success">'.esc_html($message).'</span>', 
                            'status' => '1'
                        ]);
                    }
                }
            }
        }
        wp_die();
    }


    /**
     * Function to update user level urls
     *
     * @since   1.7.0
     */
    public function update_user_levels_urls($get_ttiplatform_secret_key_listener) {
        $users = get_users (
            array (
                'meta_key' => 'user_assessment_data',
            )
        );
        foreach($users as $user) {
            $assess_user_details = get_user_meta($user->ID, 'user_assessment_data', true);
            if($assess_user_details) {
                $assess_user_details = unserialize($assess_user_details);
                foreach ($assess_user_details as $innkey => $innvalue) {
                    // echo '<pre>';print_r($innvalue);'</pre>';
                    $account_login = $innvalue['account_login'];
                    $link_id = $innvalue['link_id'];
                    $api_key = $innvalue['api_key'];
                    $api_service_location = $innvalue['api_service_location'];
                     /* API v3.0 url */  
                    $url = $api_service_location . '/api/v3/links/'.$link_id;
                    
                    $payload = array (
                        "return_url" => $get_ttiplatform_secret_key_listener,
                        "webhook_url" => $get_ttiplatform_secret_key_listener,
                        "express_return" => true
                    );
                    $data = wp_remote_post (
                        $url, 
                        array (
                            'headers'     => array(
                            'Content-Type' => 'application/json; charset=utf-8', 
                            'Authorization' => $api_key, 
                            'Accept' => 'application/json'
                        ),
                        'body'        => json_encode($payload),
                        'method'      => 'PUT',
                        'data_format' => 'body',
                    ));
                }
            }
        }
    }

    /**
     * Function to generate random string given by length.
     *
     * @since   1.0.0
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
    * Function to get assessment print option status.
    *
    * @since    1.2
    */
    public function get_assessment_can_print_option() {
        $can_print_report = 'false';
        $resp_arr = array();
        if(isset($_POST['api_service_location'])) {
            $api_service = sanitize_text_field($_POST['api_service_location']);
        }
        if(isset($_POST['api_key'])) {
            $access_token = sanitize_text_field($_POST['api_key']);   
        }
        if(isset($_POST['link_id'])) {
            $link_id  = sanitize_text_field($_POST['link_id']);
        }

        if(isset($access_token) && !empty($access_token)) {
                    /* API v3.0 url */  
                    $url = $api_service . '/api/v3/links/'.$link_id;
                   
                    $headers = array(
                        'Authorization' => $access_token,
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json'
                    );
                    $args = array(
                        'method' => 'GET',
                        'headers' => $headers,
                    );
                    $data = wp_remote_request($url, $args);

                    $response = json_decode(wp_remote_retrieve_body($data));
                    
                    if ( is_wp_error( $response ) ) {
                        $error_message = $response->get_error_message();
                        echo $error_message;
                    } else {
                        
                        if(isset($response->email_to) && $response->email_to == true) {
                            $can_print_report = 'true';
                        }
                    }

                    $resp_arr['status'] = $can_print_report;
        }
        echo json_encode($resp_arr);
        exit;
    }
    
    /**
    * Function to get assessment.
    *
    * @since    1.0.0
    */
    public function get_assessmentsFunc() {
        $queryParams ='';
        $access_token = sanitize_text_field($_POST['api_key']);
        if(isset($_POST['account_login'])) {
            $account_login  = sanitize_text_field($_POST['account_login']);
        }
        if(isset($_POST['tti_link_id'])) {
            $tti_link_id  = sanitize_text_field($_POST['tti_link_id']);
        }
        if(!empty($account_login)) {
            $queryParams = 'account_login=' . $account_login.'&page=1';
        }
        
        /* Validate the assessment by link id */
        $this->tti_validate_assessment_by_link($queryParams, $access_token, $account_login, $tti_link_id);

        wp_die();
    }

     /**
     * Function to validate the assessment by link ID.
     *
     * @since   1.3.2
     * @param array $queryParams contains parametets for query
     * @param string $access_token contains access token for api
     * @param string $account_login contains username 
     * @param string $tti_link_id contain link id of assessment
     */
    public function tti_validate_assessment_by_link($queryParams, $access_token, $account_login, $tti_link_id) {
            $can_print_report = 'false';

            /* API v 3.0 url */  
            $newUrl =  esc_url_raw($_POST['api_service_location']) . '/api/v3/links/'.$tti_link_id ;
            
            $headers = array(
                'Authorization' => $access_token,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            );
            $args = array(
                'method' => 'GET',
                'headers' => $headers,
            );

            $response = wp_remote_request($newUrl, $args);
            
            if ( is_wp_error( $response ) ) { 
                $error_message = $response->get_error_message();
                $api_response['status'] = 'error';
                echo $error_message;
            } else {
                $api_response_pt = json_decode(wp_remote_retrieve_body($response));
                
                if(isset($api_response_pt->status) && $api_response_pt->status == 500) {
                    $api_response['message'] = 'No response from the API. 500 internel server error thrown.';
                    $api_response['status'] = 'error';
                    $api_response = json_encode($api_response);
                } else {
                    /* Check for error */
                    if(isset($api_response_pt->status) && $api_response_pt->status == 'error') {
                        $can_print_report = 'false';
                        $api_response['print_status'] = $can_print_report;
                        $api_response['status'] = 'error';
                        /* error message */
                        if(isset($api_response_pt->message) && $api_response_pt->message != '') {
                            $api_response['message'] = 'This Link Login not found and cannot be added. Please provide a valid details.';
                        } else {
                            $api_response['message'] = 'This Link Login not found and cannot be added. Please provide a valid details.';
                        }
                        $api_response = json_encode($api_response);
                    } else {
                        /* No error */
                        if(isset($api_response_pt->email_to) && $api_response_pt->email_to == true) {
                            $can_print_report = 'true';
                        }
                        /* Assessment status */
                        if(isset($api_response_pt->disabled) && $api_response_pt->disabled == 0) {
                            $api_response['assessment_status_hidden'] = 'true';
                        } else {
                            $api_response['assessment_status_hidden'] = 'false';
                            $api_response['message'] = 'This Link Login is disabled and cannot be added. Please provide a valid Link Login.';
                        }
                        /* Assessment name */
                        if(isset($api_response_pt->name) && $api_response_pt->name != '') {
                            $api_response['assessment_name_hidden'] = $api_response_pt->name;
                        } else {
                            $api_response['assessment_name_hidden'] = 'Assessment';
                        }

                        /* Assessment locked status */
                        if(isset($api_response_pt->locked) && $api_response_pt->locked == true) {
                            $api_response['assessment_locked_status'] = 'true';
                        } else {
                            $api_response['assessment_locked_status'] = 'false';
                        }
                        $api_response['print_status'] = $can_print_report;
                        $api_response['status'] = 'success';
                        $api_response = json_encode($api_response);
                    }
                }
                
                echo $api_response;
            }
            
    }


     /**
     * Function to get assessments data.
     *
     * @since   1.3.2
     * @param array $queryParams contains query parameters
     * @param string $access_token  contains access token for API
     * @param string $account_login contains username for API
     */
    public function get_all_pages_assessments($queryParams, $access_token, $account_login) {
        $all_links = array();

        $stop = 0;
        $page = 1;

        
        for($i = 1; $i > $stop; $i++) {

            /* API v 3.0 url */  
            $newUrl =  esc_url_raw($_POST['api_service_location']) . '/api/v3/links?'. $queryParams;
            
            $headers = array(
                'Authorization' => $access_token,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            );
            $args = array(
                'method' => 'GET',
                'headers' => $headers,
            );

            $response = wp_remote_request($newUrl, $args);
            $api_response = json_decode(wp_remote_retrieve_body($response));

            $no_of_links = count($api_response);

            if($no_of_links >= 25) {
                $all_links[] = $api_response;
                $page++;
                $queryParams = 'account_login=' . $account_login.'&page='.$page;
            } else {
                break; // stop loop
            }
        }

        
        echo '<pre>';print_r($all_links);'</pre>'; exit();
    }

    
    /**
     * Function to insert assessment as a post type tti_assessment.
     *
     * @since   1.0.0
     */
    public function insert_assessments() {
        global $wpdb, $post;

        $get_key_option = get_option('ttiplatform_secret_key');
        $get_ttiplatform_secret_key_listener = get_option('ttiplatform_secret_key_listener');
        if(isset($get_key_option) && !empty($get_key_option) && isset($get_ttiplatform_secret_key_listener) && !empty($get_ttiplatform_secret_key_listener)) {

            $name = sanitize_text_field($_POST['name']);
            $link_id = sanitize_text_field($_POST['link_id']);
            $status_assessment = sanitize_text_field($_POST['status_assessment']);
            $organization_hidden = sanitize_text_field($_POST['organization_hidden']);
            $print_report = sanitize_text_field($_POST['print_report']);
            $send_rep_group_lead = sanitize_text_field($_POST['send_rep_group_lead']);
            $api_key_hidden = sanitize_text_field($_POST['api_key_hidden']);
            $account_login_hidden = sanitize_text_field($_POST['account_login_hidden']);
            $api_service_location_hidden = sanitize_text_field($_POST['api_service_location_hidden']);
            $survay_location_hidden = sanitize_text_field($_POST['survay_location_hidden']);
            $status_locked = sanitize_text_field($_POST['status_locked']);
            $report_api_check = sanitize_text_field($_POST['report_api_check']);

            /* Check application screening addon */
            if(in_array('tti-platform-application-screening/tti-platform-application-screening.php', apply_filters('active_plugins', get_option('active_plugins')))){ 
                /* if addon exists */
            } elseif($status_locked == 'false') {
                /* if addon not exists */
                //$status = 'no_addon';
                echo json_encode(['status' => '6']);
                wp_die();
            }
            
            if($status_assessment == 'true') {
                $status_assessment = 'Active';
            } else {
                $status_assessment = 'Suspended';
            }
            $query = $wpdb->prepare(
                'SELECT ID FROM ' . $wpdb->posts . '
                WHERE post_title = %s
                AND post_type = \'tti_assessments\'',
                $name
            );
            $wpdb->query( $query );

            if ( $wpdb->num_rows ) {
                $message = __( 'Assessment already exist.', 'tti-platform' );
                $popup_message = '';
                if(isset($wpdb->last_result[0]->ID) && $wpdb->last_result[0]->ID > 0) {
                    $post_status = get_post_status( $wpdb->last_result[0]->ID );
                    $post_id = $wpdb->last_result[0]->ID;
                    if($post_status == 'trash') {
                        $status = 3; // 3 status means post is in trash
                        
                        $popup_message = __( 'Your assessment is in the trash. Would you like to restore the assessment?', 'tti-platform' );
                        $message = $post_id;
                    } elseif($post_status == 'publish') {
                        $status = 2; // 0 status means post publish
                        $popup_message = __( 'Assessment Already exists', 'tti-platform' );
                    }else {
                        $status = 0; // 0 status means post publish
                        $popup_message = __( 'Assessment Successfully Added', 'tti-platform' );
                    }
                } else {
                    $status = 0; 
                }
                
            } else {
                $post_id = wp_insert_post(array(
                    'post_type' => 'tti_assessments',
                    'post_title' => $name,
                    'post_status' => 'publish',
                    'comment_status' => 'closed',
                    'ping_status' => 'closed',
                ));
                if ($post_id) {

                    add_post_meta($post_id, 'link_id', $link_id);
                    add_post_meta($post_id, 'status_locked', $status_locked);
                    add_post_meta($post_id, 'organization', $organization_hidden);
                    add_post_meta($post_id, 'api_key', $api_key_hidden);
                    add_post_meta($post_id, 'account_login', $account_login_hidden);
                    add_post_meta($post_id, 'api_service_location', $api_service_location_hidden);
                    add_post_meta($post_id, 'survay_location', $survay_location_hidden);
                    add_post_meta($post_id, 'status_assessment', $status_assessment);
                    add_post_meta($post_id, 'report_api_check', $report_api_check);
                    
                    /*
                     * If assessment successfully inserted then fetch the metadata
                     */
                    $api_service = get_post_meta($post_id, 'api_service_location', true);
                    $account_id = get_post_meta($post_id, 'account_login', true);
                    $link_id = get_post_meta($post_id, 'link_id', true);
                    $api_token = get_post_meta($post_id, 'api_key', true);
                    
                    /* API v3.0 url */  
                    $url = $api_service . '/api/v3/links/'.$link_id;

                    $headers = array(
                        'Authorization' => $api_token,
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json'
                    );
                    $args = array(
                        'method' => 'GET',
                        'headers' => $headers,
                    );
                    $data = wp_remote_request($url, $args);

                    $response = json_decode(wp_remote_retrieve_body($data));
                    
                    if ( is_wp_error( $response ) ) {
                        $error_message = $response->get_error_message();
                        echo $error_message;
                    } else {
                        $can_print_report = 'false';
                        $can_group_leader_mail = 'false';
                        if(isset($response->email_to) && $response->email_to == true) {
                            $can_print_report = 'true';
                            $can_group_leader_mail = 'true';
                        }

                        add_post_meta($post_id, 'can_print_assessment', $can_print_report);

                        /* Update Can Print Report function */
                        if($can_print_report == 'true') {
                            add_post_meta($post_id, 'print_report', $print_report);
                        } else {
                            add_post_meta($post_id, 'print_report', '');
                        }

                        /* Update the Group Leader Mail function */
                        if($can_group_leader_mail == 'true') {
                            add_post_meta($post_id, 'send_rep_group_lead', $send_rep_group_lead);
                        } else {
                            add_post_meta($post_id, 'send_rep_group_lead', '');
                        }

                        foreach ($response->reportviews as $key => $value) {
                            $report_id = $value->id;
                            $api_service_location = get_post_meta($post_id, 'api_service_location', true);
                            $api_key = get_post_meta($post_id, 'api_key', true);
                            $report_data = $this->get_report_metadata($report_id, $api_service_location, $api_key);
                        }
                        add_post_meta($post_id, 'report_metadata', serialize($report_data));

                        /*Update Return URL when new assessment added*/
                        $args = array( 'post_type' => 'tti_assessments', 'posts_per_page' => -1 );
                        $loop = new WP_Query( $args );
                        while ( $loop->have_posts() ) : $loop->the_post();
                            $account_login = get_post_meta($post->ID, 'account_login', true);
                            $link_id = get_post_meta($post->ID, 'link_id', true);
                            $api_key = get_post_meta($post->ID, 'api_key', true);
                            $api_service_location = get_post_meta($post->ID, 'api_service_location', true);
                            
                            /* API v3.0 url */  
                            $url = $api_service_location . '/api/v3/links/'.$link_id;

                            $payload = array(
                                "return_url" => $get_ttiplatform_secret_key_listener,
                                "webhook_url" => $get_ttiplatform_secret_key_listener,
                                "express_return" => true
                            );
                            $data = wp_remote_post($url, array(
                                    'headers'     => array(
                                    'Content-Type' => 'application/json; charset=utf-8', 
                                    'Authorization' => $api_key, 
                                    'Accept' => 'application/json'
                                ),
                                'body'        => json_encode($payload),
                                'method'      => 'PUT',
                                'data_format' => 'body',
                            ));
                            $getStatus[] = json_decode(wp_remote_retrieve_body($data));
                            
                        endwhile;
                    }
                }
                $message = __( 'Assessment successfully added.', 'tti-platform' );
                $status = 1;
            }
            echo json_encode(['message' => $message, 'status' => $status, 'popup_message'=>$popup_message]);
        } else {
            $status = 'return_url';
            echo json_encode(['status' => $status]);
        }
        wp_die();
    }

    /**
     * Function to add metabox in post type tti_assessment.
     *
     * @since   1.0.0
     */
    public function assessment_meta_box() {
        add_meta_box('assessment-meta-box', __('Assessment Details', 'tti-platform'), array($this, 'assessment_meta_box_render'), 'tti_assessments');
    }

    /**
     * Callback Function to render metabox HTML in post type tti_assessment. 
     *
     * @since   1.0.0
     * @param array $post contains post data
     */
    public function assessment_meta_box_render($post) {

        ?>
            <!-- Change Add New button permalink -->
            <script type="text/javascript">
                jQuery('#wpbody-content .wrap h1+a').attr("href", "<?php echo site_url(); ?>/wp-admin/edit.php?post_type=tti_assessments&page=ttiplatform_api");
                
            </script><?php

        $organization = (!empty(get_post_meta($post->ID, 'organization', true))) ? get_post_meta($post->ID, 'organization', true) : '';
        $api_key = (!empty(get_post_meta($post->ID, 'api_key', true))) ? get_post_meta($post->ID, 'api_key', true) : '';
        $account_login = (!empty(get_post_meta($post->ID, 'account_login', true))) ? get_post_meta($post->ID, 'account_login', true) : '';
        $api_service_location = (!empty(get_post_meta($post->ID, 'api_service_location', true))) ? get_post_meta($post->ID, 'api_service_location', true) : '';
        $survay_location = (!empty(get_post_meta($post->ID, 'survay_location', true))) ? get_post_meta($post->ID, 'survay_location', true) : '';
        $link_id = (!empty(get_post_meta($post->ID, 'link_id', true))) ? get_post_meta($post->ID, 'link_id', true) : '';
        $status_assessment = (!empty(get_post_meta($post->ID, 'status_assessment', true))) ? get_post_meta($post->ID, 'status_assessment', true) : '';
        $print_report = (!empty(get_post_meta($post->ID, 'print_report', true))) ? get_post_meta($post->ID, 'print_report', true) : '';
        $report_metadata = (!empty(get_post_meta($post->ID, 'report_metadata', true))) ? get_post_meta($post->ID, 'report_metadata', true) : '';

         $report_api_check = (!empty(get_post_meta($post->ID, 'report_api_check', true))) ? get_post_meta($post->ID, 'report_api_check', true) : '';
         

         /* Check if assesment can be printed or not */
         $can_print_assessment = (!empty(get_post_meta($post->ID, 'can_print_assessment', true))) ? get_post_meta($post->ID, 'can_print_assessment', true) : '';

         /* Check if email send to group leader or not */
         $send_rep_group_lead = (!empty(get_post_meta($post->ID, 'send_rep_group_lead', true))) ? get_post_meta($post->ID, 'send_rep_group_lead', true) : '';

        ob_start();
        ?>
 
        <div class="assessment-wrap">
            <?php
            /**
             * Fires before assessment admin meta page
             * 
             * @since   1.2
             */
            do_action('ttisi_platform_assessment_meta_box_admin_before');

            ?>
            <label for="organization"><strong><?php _e('Title', 'tti-platform'); ?></strong></label>
            <input type="text" name="organization" id="organization" value="<?php echo esc_attr($organization); ?>" >
            
            <label for="api_key"><strong><?php _e('API Key', 'tti-platform'); ?></strong></label>
            <input type="text" name="api_key" id="api_key" value="<?php echo esc_attr($api_key); ?>" >

            <label for="account_login"><strong><?php _e('Account Login', 'tti-platform'); ?></strong></label>
            <input type="text" name="account_login" id="account_login" value="<?php echo esc_attr($account_login); ?>" >

            <label for="api_service_location"><strong><?php _e('API Service Location', 'tti-platform'); ?></strong></label>
            <input type="text" name="api_service_location" id="api_service_location" value="<?php echo esc_attr($api_service_location); ?>" >

            <label for="survay_location"><strong><?php _e('Survey Location', 'tti-platform'); ?></strong></label>
            <input type="text" name="survay_location" id="survay_location" value="<?php echo esc_attr($survay_location); ?>"  />
            
            <label for="link_id"><strong><?php _e('Link ID', 'tti-platform'); ?></strong></label>
            <input type="text" name="link_id" id="link_id" value="<?php echo esc_attr($link_id); ?>"  />
            
            <label for="assessment_status"><strong><?php _e('Assessment Status', 'tti-platform'); ?></strong></label>
            <input type="text" name="assessment_status" id="assessment_status" value="<?php echo esc_attr($status_assessment); ?>" disabled="disabled" />

            <?php
            /**
             * Fires after assessment admin meta box last field 
             * 
             * @since   1.2
             */
            do_action('ttisi_platform_assessment_meta_box_admin_after_input_boxes');
            ?>
            

            <?php if($can_print_assessment == 'true') { ?>
            <label><?php _e('Can Print Report?', 'tti-platform'); ?> </label>
            <input type="radio" name="print_report" id="print_report_yes" value="Yes" <?php if($print_report == 'Yes') {echo 'checked';} ?> /> <span for="print_report_yes" style="margin-right: 10px;"><?php _e('Yes', 'tti-platform'); ?></span>
            <input type="radio" name="print_report" id="print_report_no" value="No" <?php if($print_report == 'No') {echo 'checked';} ?> /> <span for="print_report_no"><?php _e('No', 'tti-platform'); ?></span>
            <br>
            <br>
            <?php } ?>
           
            <!-- Send report to group leaders -->
             <label><?php _e('Send report to group leader', 'tti-platform'); ?> </label>
            <input type="radio" name="send_rep_group_lead" id="send_rep_group_lead_yes" value="Yes" <?php if($send_rep_group_lead == 'Yes') {echo 'checked';} ?> /> <span for="send_rep_group_lead" style="margin-right: 10px;"><?php _e('Yes', 'tti-platform'); ?></span>
            <input type="radio" name="send_rep_group_lead" id="send_rep_group_lead_no" value="No" <?php if($send_rep_group_lead == 'No') {echo 'checked';} ?> /> <span for="send_rep_group_lead"><?php _e('No', 'tti-platform'); ?></span>
            <!-- ---------------------------- -->
            
             <br>
            <br>
             <!-- Send report to group leaders -->
             <label><?php _e('Download report using API', 'tti-platform'); ?> </label>
            <input type="radio" name="report_api_check" id="send_rep_group_lead_yes" value="Yes" <?php if($report_api_check == 'Yes') {echo 'checked';} ?> /> <span for="report_api_check" style="margin-right: 10px;"><?php _e('Yes', 'tti-platform'); ?></span>
            <input type="radio" name="report_api_check" id="send_rep_group_lead_no" value="No" <?php if($report_api_check == 'No') {echo 'checked';} ?> /> <span for="report_api_check"><?php _e('No', 'tti-platform'); ?></span>
            <!-- ---------------------------- -->

            <br />
            <br />
            <br />
 
            <?php
            /**
             * Fires after assessment admin meta box last field 
             * 
             * @since   1.2
             */
            do_action('ttisi_platform_assessment_meta_box_admin_after');
            ?>
            <?php
            /**
             * Fires after assessment admin meta page before print response
             * 
             * @since   1.2
             */
            do_action('ttisi_platform_assessment_meta_box_admin_before_print_response');
            ?>
                
            <?php
            /**
             * Fires after assessment admin meta page after print response
             * 
             * @since   1.2
             */
            do_action('ttisi_platform_assessment_meta_box_admin_after_print_response');
            ?>
                
        </div>
        <?php
        echo ob_get_clean();
    }


    /**
     * Function to add meta boxes with the input fields.
     *
     * @since   1.0.0
     * @param integer $post_id contains post ID
     */
    public function assessment_meta_box_information($post_id) {
        if (wp_is_post_autosave($post_id))
            return;
        if (wp_is_post_revision($post_id))
            return;
        if (!current_user_can('manage_options'))
            return;
        $posted_data = filter_input_array(INPUT_POST);

        if (isset($posted_data['send_rep_group_lead'])) {
            update_post_meta($post_id, 'send_rep_group_lead', sanitize_text_field($posted_data['send_rep_group_lead']));
        }

        if (isset($posted_data['organization']))
            update_post_meta($post_id, 'organization', sanitize_text_field($posted_data['organization']));

        if (isset($posted_data['api_key']))
            update_post_meta($post_id, 'api_key', sanitize_text_field($posted_data['api_key']));
        if (isset($posted_data['account_login']))
            update_post_meta($post_id, 'account_login', sanitize_text_field($posted_data['account_login']));
        if (isset($posted_data['api_service_location']))
            update_post_meta($post_id, 'api_service_location', sanitize_text_field($posted_data['api_service_location']));
        if (isset($posted_data['survay_location']))
            update_post_meta($post_id, 'survay_location', sanitize_text_field($posted_data['survay_location']));
        if (isset($posted_data['link_id']))
            update_post_meta($post_id, 'link_id', sanitize_text_field($posted_data['link_id']));
        if (isset($posted_data['print_report']))
            update_post_meta($post_id, 'print_report', sanitize_text_field($posted_data['print_report']));
        if (isset($posted_data['report_api_check']))
            update_post_meta($post_id, 'report_api_check', sanitize_text_field($posted_data['report_api_check']));
    }
    
    /**
    * Function to change URL to API Call URL.
    *
    * @since    1.0.0
    */
    public function assessment_change_permalink() {
        if(isset($_GET['post_type'])) {
            $post_type = sanitize_text_field($_GET['post_type']);
            if ($post_type == 'tti_assessments') {?>
                <script type="text/javascript">
                    jQuery('#wpbody-content .wrap h1+a').attr("href", "<?php echo site_url(); ?>/wp-admin/edit.php?post_type=tti_assessments&page=ttiplatform_api");
                    
                </script><?php
            }
        }
    }
    
    /**
    * Function to render all assessments metadata in the shortcode generator.
    *
    * @since    1.0.0
    */
    public function get_assessments_metadeta() {

        $assessment_id = sanitize_text_field($_POST['assessment_text_feedback_id']);
        $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : 'text';
        $link_id = get_post_meta($assessment_id, 'link_id', true);
        $api_service_location = get_post_meta($assessment_id, 'api_service_location', true);
        $api_key = get_post_meta($assessment_id, 'api_key', true);
        $report_metadata = (!empty(get_post_meta($assessment_id, 'report_metadata', true))) ? get_post_meta($assessment_id, 'report_metadata', true) : '';
       
        $report_data = unserialize($report_metadata);
        
        $assessmenrArr = array();
        if(isset($report_data->report_page_metadata->metadata) && count($report_data->report_page_metadata->metadata) >0 ) {
            foreach ($report_data->report_page_metadata->metadata as $arrayResponseData){
                
                /* Skip full intro parts */
                if( $arrayResponseData->ident == 'TITLE' || 
                    $arrayResponseData->ident == 'TRICOACHINTRO2' ||
                    $arrayResponseData->ident == 'INTRO' ||
                    $arrayResponseData->ident == 'INTEGRATIONINTRO_DF' ||
                    $arrayResponseData->ident == 'EQ_INTRO' ||
                    $arrayResponseData->ident == 'BLENDING_DF_INTRO'
                ) {
                    continue;
                }

                if( 
                    $arrayResponseData->ident == 'TITLE' || 
                    //$arrayResponseData->ident == 'INTRO' || 
                    $arrayResponseData->ident == 'PIAVWHEEL12' || 
                    $arrayResponseData->ident == 'PIAVWHEEL12_2' ||
                    $arrayResponseData->ident == 'DES'
                ) {
                    continue;
                }
                
                /* if request is thrown by graphic */
                    if($type == 'graphic') {
                        if( !in_array($arrayResponseData->ident, $this->list_text_feed_array ) ||
                            in_array($arrayResponseData->ident, $this->both_text_grpahic_array )
                        ) { 
                            if($arrayResponseData->ident == 'EQTABLES2') {
                                $this->handle_eqtables_section(
                                    $arrayResponseData, 
                                    $link_id,
                                    $assessmenrArr // Pass by refrence
                                );
                            } else {
                                 /* list graphic feedbacks */
                                 $assessmenrArr[] = array(
                                    'title' => html_entity_decode($arrayResponseData->title),
                                    'ident' => html_entity_decode($arrayResponseData->ident),
                                    'link_id' => $link_id,
                                );
                            }
                        }
                    } else {
                        /* if request is thrown by text */
                        if( in_array($arrayResponseData->ident, $this->list_text_feed_array ) ||
                            in_array($arrayResponseData->ident, $this->both_text_grpahic_array ) 
                        ) { 

                            if($arrayResponseData->ident == 'EQTABLES2') {
                                 $this->handle_eqtables_section(
                                    $arrayResponseData, 
                                    $link_id,
                                    $assessmenrArr // Pass by refrence
                                );
                            } else {
                                 /* list text feedbacks */
                                 $assessmenrArr[] = array(
                                    'title' => html_entity_decode($arrayResponseData->title),
                                    'ident' => html_entity_decode($arrayResponseData->ident),
                                    'link_id' => $link_id,
                                );
                            }
                        }
                    }
            }
        }

        
        /**
         * Filter to update assessment metadata
         * 
         * @since  1.2
         */
        $assessmenrArr = apply_filters('ttisi_platform_get_assessments_metadeta', $assessmenrArr);
        echo json_encode($assessmenrArr);
        exit;
    }

    /**
    * Function to handle the EQ Tables section.
    *
    * @param array $arrayResponseData contains array get from API response
    * @param string $link_id contains assessment link ID
    * @param array $assessmenrArr contains assessment data
    */
    public function handle_eqtables_section($arrayResponseData, $link_id, &$assessmenrArr) {
            if(isset($arrayResponseData->content)) { 
                foreach ($arrayResponseData->content as $key => $value) {
                    $assessmenrArr[] = array(
                        'title' => html_entity_decode($value->title),
                        'ident' => 'EQTABLES2-'.html_entity_decode($value->ident), //html_entity_decode($value->ident)
                        'link_id' => $link_id
                    );
                    
                }    
            }
        }


    /**
    * Function to get all report metadata.
    *
    * @since    1.0.0
    * @param array $Function to report_view_id contains report view id
    * @param string $api_service_location contains service location link 
    * @param string $api_key contains api key
    * @return array contains api response
    */
    public function get_report_metadata($report_view_id, $api_service_location, $api_key) {
        /* API v 3.0 url */  
        $url =   $api_service_location . '/api/v3/reportviews/'. $report_view_id;
        $api_response = $this->send_api_request($url, $api_key);
        /**
         * Filter to update assessment report metadata
         * 
         * @since  1.2
         */
        $api_response = apply_filters('ttisi_platform_get_report_metadata', $api_response);
        return $api_response;
    }

    
    
    /**
    * Function to render all assessments in the shortcode generator in the editor.
    *
    * @since    1.0.0
    */
    public function list_assessments() {
        $args = array( 'post_type' => 'tti_assessments', 'posts_per_page' => -1 );
        $loop = new WP_Query( $args );
        $assessmenrArr = array();
        while ( $loop->have_posts() ) : $loop->the_post();
          $assesment_id = get_the_ID();
          $status_assessment = get_post_meta($assesment_id, 'status_assessment', true);
          $status_assessment = (!empty($status_assessment)) ? $status_assessment : '';
          /* check suspend status */
          if($status_assessment != 'Suspended') {
            $assessmenrArr[] = array(
              'title' => html_entity_decode(get_the_title()),
              'id' => get_the_ID()
            );
          }
        endwhile;
        if(count($assessmenrArr) > 0) {
            /**
             * Filter to update listing assessments
             * 
             * @since  1.2
             */
            $assessmenrArr = apply_filters('ttisi_platform_list_assessments', $assessmenrArr);
            echo json_encode($assessmenrArr);
        } else {
            echo json_encode('none');
        }
        
        exit;
    }
    
    /**
    * Function to render all assessments for text feedback in the shortcode generator in the editor.
    *
    * @since    1.0.0
    */
    public function list_assessments_for_feedback() {

        $args = array( 'post_type' => 'tti_assessments', 'posts_per_page' => -1 );
        $loop = new WP_Query( $args );
        $assessmenrArr = array();
        while ( $loop->have_posts() ) : $loop->the_post();
          $assesment_id = get_the_ID();
          $status_assessment = get_post_meta($assesment_id, 'status_assessment', true);
          $status_assessment = (!empty($status_assessment)) ? $status_assessment : '';
          
          $status_locked = get_post_meta($assesment_id, 'status_locked', true);
          $status_locked = (!empty($status_locked)) ? $status_locked : $status_locked;

          /* check suspend status */
          if($status_assessment != 'Suspended' && $status_locked == 'true') {
              $assessmenrArr[] = array(
                  'title' => html_entity_decode(get_the_title()),
                  'id' => get_the_ID()
              );
           }
        endwhile;
        
        if(count($assessmenrArr) > 0) {
            /**
             * Filter to update listing feedback assessments
             * 
             * @since  1.2
             */
            $assessmenrArr = apply_filters('ttisi_platform_list_feedback_assessments', $assessmenrArr);
            echo json_encode($assessmenrArr);
        } else {
            echo json_encode('none');
        }
        exit;
    }

    /**
    * Function to render all assessments for PDF report in the shortcode generator in the editor.
    *
    * @since    1.0.0
    */
    public function list_assessments_for_pdf() {
        global $post;
        $args = array( 'post_type' => 'tti_assessments', 'posts_per_page' => -1 );
        $loop = new WP_Query( $args );
        $assessmenrArr = array();
        while ( $loop->have_posts() ) : $loop->the_post();
          $assesment_id = get_the_ID();
          $status_assessment = get_post_meta($assesment_id, 'status_assessment', true);
          $status_assessment = (!empty($status_assessment)) ? $status_assessment : '';

          $status_locked = get_post_meta($assesment_id, 'status_locked', true);
          $status_locked = (!empty($status_locked)) ? $status_locked : $status_locked;

            $report_status = get_post_meta($post->ID, 'print_report', true);
            if($status_assessment != 'Suspended' && $status_locked == 'true') {
                if($report_status == 'Yes') {
                    $assessmenrArr[] = array(
                        'title' => html_entity_decode(get_the_title()),
                        'id' => get_the_ID()
                    );
                }
            }
        endwhile;
        if(count($assessmenrArr) > 0) {
            /**
             * Filter to update listing pdf assessments
             * 
             * @since  1.2
             */
            $assessmenrArr = apply_filters('ttisi_platform_list_pdf_assessments', $assessmenrArr);
            echo json_encode($assessmenrArr);
        } else {
            echo json_encode('none');
        }
        exit;
    }
    

    /**
    * Function to render all opened assessments for PDF report in the shortcode generator in the editor.
    *
    * @since    1.0.0
    */
    public function list_opened_assessments_list() {
        $args = array( 'post_type' => 'tti_assessments', 'posts_per_page' => -1 );
        $loop = new WP_Query( $args );
        $assessmenrArr = array();
        while ( $loop->have_posts() ) : $loop->the_post();
          $assesment_id = get_the_ID();
          $status_locked = get_post_meta($assesment_id, 'status_locked', true);
          $status_locked = (!empty($status_locked)) ? $status_locked : $status_locked;
          /* check suspend status */
          if($status_locked == 'false') {
            $assessmenrArr[] = array(
              'title' => html_entity_decode(get_the_title()),
              'id' => get_the_ID()
            );
          }
        endwhile;
        if(count($assessmenrArr) > 0) {
            /**
             * Filter to update listing assessments
             * 
             * @since  1.2
             */
            $assessmenrArr = apply_filters('ttisi_platform_list_locked_assessments', $assessmenrArr);
            echo json_encode($assessmenrArr);
        } else {
            echo json_encode('none');
        }
        
        exit;
    }
    

    /**
    * Function to render all feedback metadata checklist in the shortcode generator in the editor.
    *
    * @since    1.0.0
    */
    public function get_assessments_metadeta_checklist() {

        $assessmenrArr = array();
        $assessment_feedback_value = sanitize_text_field($_POST['assessment_feedback_value']);
        $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : 'text';
        $assessment_id = sanitize_text_field($_POST['assess_id']);
        $api_service_location = get_post_meta($assessment_id, 'api_service_location', true);
        $api_key = get_post_meta($assessment_id, 'api_key', true);
        $link_id = (!empty(get_post_meta($assessment_id, 'link_id', true))) ? get_post_meta($assessment_id, 'link_id', true) : '';
        $report_metadata = (!empty(get_post_meta($assessment_id, 'report_metadata', true))) ? get_post_meta($assessment_id, 'report_metadata', true) : '';
        
        $report_data = unserialize($report_metadata);

        if(isset($report_data->report_page_metadata->metadata) && count($report_data->report_page_metadata->metadata) > 0) {

        $assessmenrArr = array();
            foreach ($report_data->report_page_metadata->metadata as $arrayResponseData){

                if($arrayResponseData->ident == $assessment_feedback_value && $arrayResponseData->ident != 'EQTABLES2') {
                    $title = $arrayResponseData->title;
                    $content = $arrayResponseData->content;
                    $intro = $arrayResponseData->intro;
                    $assessmenrArr[] = array(
                        'title' => $title,
                        'content' => $content,
                        'intro' => $intro,
                    );
                } elseif($arrayResponseData->ident == 'EQTABLES2') {
                    $this->handle_eqtables_section_metachecklist($arrayResponseData, $link_id, $assessment_feedback_value, $type, $assessmenrArr);
                }
            }
        }
        
        /**
        * Filter to update assessments metadeta checklist
        * 
        * @since  1.2
        */
        $assessmenrArr = apply_filters('ttisi_platform_assessments_metadeta_checklist', $assessmenrArr);
        echo json_encode($assessmenrArr);
        exit;
    }

        /**
        * Function to handle the EQ Tables section meta checklist section.
        *
        * @since    1.0.0
        * @param array $arrayResponseData contains api response array
        * @param string $link_id contains link id
        * @param array $assessment_feedback_value contains assessment feedback value
        * @param string $type contains assessment type
        * @param array $assessmenrArr returns assessment data array related to EQ tables
        */
        public function handle_eqtables_section_metachecklist($arrayResponseData, $link_id, $assessment_feedback_value, $type ,&$assessmenrArr) {
            if(isset($arrayResponseData->content)) { 
                $get_page = explode('-', $assessment_feedback_value);
                foreach ($arrayResponseData->content as $key => $value) {
                    if($value->ident == $get_page[1]) {
                        if($type =='text') {
                            /* Unset description and scorebar for text */
                            unset($value->content[0]);
                            unset($value->content[2]);
                            $intro = 1;
                        } else {
                            /* Unset description and bullets for graphics */
                            unset($value->content[0]);
                            unset($value->content[1]);
                            $intro = 0;
                        }
                        $assessmenrArr[] = array(
                            'title' => $value->title,
                            'content' => $value->content,
                            'intro' => $intro,
                        );
                    }
                }    
            }
        }

    /**
    * Function to send API request.
    *
    * @since    1.0.0
    * @param $input string contains url for CURL request
    * @param $input string contains API key
    * @return array contains response from API 
    */
    public function send_api_request($url, $api_key) {

        $headers = array(
            'Authorization' => $api_key,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        );
        $args = array(
            'method' => 'GET',
            'headers' => $headers,
        );

        $response = wp_remote_request($url, $args);

        if ( is_wp_error( $response ) ) {
            $error_message = $response->get_error_message();
            return $error_message;
        } else {
            $api_response = json_decode(wp_remote_retrieve_body($response));
            return $api_response;
        }
    }
    
   


    /**
    * Function to get the version of assessment.
    *
    * @since    1.6
    * @param array $data contains assessment data
    */
    public function get_latest_version($data) {
        if(isset($data) && (count($data) == 0 || count($data) == 1)) {
            return $data;
        } else {
            foreach ($data as $key => $value) {
                echo '<pre>';print_r($value);'</pre>';
            }exit;
        }
    }

    
    /**
    * Function to add assessment shortcode generator icon in WYSISYG.
    *
    * @since    1.0.0
    */
    
    public function assessment_tinymce_extra_vars() { ?>
        <script type="text/javascript">
            var tinyMCE_object = <?php echo json_encode(
                array(
                    'button_name' => esc_html__('TTI Shortcodes', 'tti-platform'),
                )
            );?>;
        </script><?php
    }

    /**
    * Function to add the custom column to the tti_assessment post type.
    *
    * @since    1.0.0
    * @param array $columns contains custom post type tti_assessment columns
    * @return array return columns
    */
    public function assessment_add_custom_column($columns) {

        $org = __( 'Title', 'tti-platform' );
        $account_id = __( 'Account ID', 'tti-platform' );
        $link_id = __( 'Link ID', 'tti-platform' );
        $group_leader = __( 'Group Leader(s)', 'tti-platform' );
        $status_assessment = __( 'Status', 'tti-platform' );
        $status_locked = __( 'Type', 'tti-platform' );
        $report_api_check = __( 'Report Type', 'tti-platform' );

        $columns['organization'] = $org;
        $columns['account_id'] = $account_id;
        $columns['link_id'] = $link_id;
        $columns['group_leader'] = $group_leader;
        $columns['status_assessment'] = $status_assessment;
        $columns['report_api_check'] = $report_api_check;
        return $columns;
    }

    /**
     * Function to add the data to the custom column of the assessment post type tti_assessment.
     *
     * @since   1.0.0
     *
     * @param string $column contains all columns data for post type tti_assessment
     * @param integer $post_id contains post ID
     */
    public function assessment_add_custom_column_data($column, $post_id) {
        switch ($column) {
            case 'organization' :
                echo get_post_meta($post_id, 'organization', true); // the data that is displayed in the column
                break;
            case 'account_id' :
                echo get_post_meta($post_id, 'account_login', true); // the data that is displayed in the column
                break;
            case 'link_id' :
                echo get_post_meta($post_id, 'link_id', true); // the data that is displayed in the column
                break;
            case 'status_assessment' :
                echo get_post_meta($post_id, 'status_assessment', true); // the data that is displayed in the column
                break;
           
            case 'group_leader' :
                $group_leader = get_post_meta($post_id, 'send_rep_group_lead', true); // the data that is displayed in the column
                echo $group_leader;
                break;

            case 'report_api_check' :
                $report_api_check = get_post_meta($post_id, 'report_api_check', true); // the data that is displayed in the column
                if(strtolower($report_api_check) == 'yes' || empty($report_api_check)) {
                    echo 'API';
                } else {
                    echo 'Response';
                }
                break;
            
        }
    }

    /**
     * Function to make the custom column sortable for assessment post type tti_assessment.
     *
     * @since   1.0.0
     *
     * @param array $columns contains columns data for assessment post type tti_assessment
     * @return array contains latest columns data
     */
    public function assessment_add_custom_column_make_sortable($columns) {
        $columns['organization'] = 'organization';
        $columns['account_id'] = 'Account ID';
        $columns['link_id'] = 'Link ID';
        $columns['group_leader'] = 'Group Leader(s)';
        $columns['status_assessment'] = 'Status';
        $columns['report_api_check'] = 'Report Type';
        return $columns;
    }

    /**
     * Function to hide password field from Quick Edits in the assessment post type tti_assessment.
     *
     * @since   1.0.0
     */
    public function remove_password_from_quick_edit() {    
        global $current_screen;
        if( 'edit-tti_assessments' != $current_screen->id )
            return;
        ?>
        <script type="text/javascript">         
            jQuery(document).ready( function($) {
                $('span:contains("Password")').each(function (i) {
                    $(this).parent().parent().remove();
                });
            });    
        </script>
        <?php
    }

    /**
     * Function to add your custom bulk action in dropdown.
     * 
     * @since    1.0.0
     *
     * @param array $bulk_actions contains bulk action data
     * @return array contains latest bulk option data
     */
    public function assessment_register_bulk_action_active( $bulk_actions ) {
        $active = __( 'Active', 'tti-platform' );
        $bulk_actions['assessment_activation'] = $active;
        return $bulk_actions;
    }

    /**
     * Custom sanitization function that will take the incoming input, and sanitize
     * the input before handing it back to WordPress to save to the database.
     *
     * @since    1.0.0
     *
     * @param array $input contains array
     * @return array contains sanitized array
     */
    public function sanitize_the_array( $input ) {
        // Initialize the new array that will hold the sanitize values
        $new_input = array();
        // Loop through the input and sanitize each of the values
        foreach ( $input as $key => $val ) {
            $new_input[ $key ] = sanitize_text_field( $val );
        }
        return $new_input;
    }

    /**
     * Function to make sure that action name in the hook is the same like the option value.
     *
     * @since   1.0.0
     */
    public function assessment_bulk_process_active_status() {
       
        if( !isset( $_REQUEST['post'] ) && !is_array( $_REQUEST['post'] ) )
            return;
        
        /* Sanitizing the array */
        $post_req_array = $this->sanitize_the_array($_REQUEST['post']);
       
        foreach( $post_req_array as $post_id ) {
            $api_key = (!empty(get_post_meta($post_id, 'api_key', true))) ? get_post_meta($post_id, 'api_key', true) : '';
            $account_login = (!empty(get_post_meta($post_id, 'account_login', true))) ? get_post_meta($post_id, 'account_login', true) : '';
            $api_service_location = (!empty(get_post_meta($post_id, 'api_service_location', true))) ? get_post_meta($post_id, 'api_service_location', true) : '';
            $link_id = (!empty(get_post_meta($post_id, 'link_id', true))) ? get_post_meta($post_id, 'link_id', true) : '';
            $access_token = $api_key;
            
            /* API v3.0 url */  
            $url = $api_service_location . '/api/v3/links/'.$link_id.'/enable';

            $payload = array(
                'disabled' => 0,
            );
            $data = wp_remote_post($url, array(
                    'headers'     => array(
                    'Content-Type' => 'application/json; charset=utf-8', 
                    'Authorization' => $access_token, 
                    'Accept' => 'application/json'
                ),
                'method'      => 'PUT',
            ));
            $getStatus = json_decode(wp_remote_retrieve_body($data));
            
            if(isset($data['response']['code']) && ($data['response']['code'] == 200 || $data['response']['code'] == 204)) {
                $statusVal = 0;
            } 

            if ( is_wp_error( $response ) ) {
                    $error_message = $response->get_error_message();
                    echo json_encode([
                        'error' => $error_message,
                        'status' => '1'
                    ]);
            } else if ($statusVal == 0) {
                update_post_meta( $post_id, 'status_assessment', 'Active' );
            } else {

            }
        }

        /* Sanitizing the array */
        $post_req_array = $this->sanitize_the_array($_REQUEST['post']);

        $location = add_query_arg( array(
            'post_type' => 'tti_assessments',
            'assessment_suspended' => 'Active',
            'changed' => count( $post_req_array ),
            'ids' => join( $post_req_array, ',' ),
            'post_status' => 'all'
        ), 'edit.php' );
        wp_redirect( admin_url( $location ) );
        exit;
    }

    /**
     * Function to assessment update notices on activation.Function to 
     *
     * @since   1.0.0
     */
    public function assessment_active_order_status_notices() {
        global $pagenow, $typenow;
        if( $typenow == 'tti_assessments'
         && $pagenow == 'edit.php'
         && isset( $_REQUEST['assessment_activation'] )
         && $_REQUEST['assessment_activation'] == 'Active'
         && isset( $_REQUEST['changed'] ) ) {
            $changed = sanitize_text_field($_REQUEST['changed']);
            $message = sprintf( _n( 'Status Activated.', '%s Statuses Activated.', $changed ), number_format_i18n( $changed ) );

        /**
         * Filter to update assessment active order status notices
         * 
         * @since  1.2
         */
         $message = apply_filters('ttisi_platform_assessment_active_order_status_notices', $message);
         echo "<div class=\"updated\"><p>{$message}</p></div>";
        }
    }

    /**
     * Function to add your custom bulk action in dropdown.
     *
     * @since   1.0.0
     *
     * @param array $bulk_actions contains bulk action data
     * @return array return updated bulk action array
     */
    public function assessment_register_bulk_action_suspended( $bulk_actions ) {
        $bulk_actions['assessment_suspended'] = __( 'Suspended', 'tti-platform' );
        return $bulk_actions;
    }

    /**
     * Function to make sure that action name in the hook is the same like the option value.
     *
     * @since   1.0.0
     */
    public function assessment_bulk_process_suspended_status() {

        if( !isset( $_REQUEST['post'] ) && !is_array( $_REQUEST['post'] ) )
            return;

        /* Sanitizing the array */
        $post_req_array = $this->sanitize_the_array($_REQUEST['post']);

        foreach( $post_req_array as $post_id ) {
            $api_key = (!empty(get_post_meta($post_id, 'api_key', true))) ? get_post_meta($post_id, 'api_key', true) : '';
            $account_login = (!empty(get_post_meta($post_id, 'account_login', true))) ? get_post_meta($post_id, 'account_login', true) : '';
            $api_service_location = (!empty(get_post_meta($post_id, 'api_service_location', true))) ? get_post_meta($post_id, 'api_service_location', true) : '';
            $link_id = (!empty(get_post_meta($post_id, 'link_id', true))) ? get_post_meta($post_id, 'link_id', true) : '';
            $access_token = $api_key;
            
            /* API v3.0 url */  
            $url = $api_service_location . '/api/v3/links/'.$link_id.'/enable';
         
             $headers = array(
                'Authorization' => $access_token,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            );
            $args = array(
                'method' => 'DELETE',
                'headers' => $headers,
            );

            $data = wp_remote_request($url, $args);
            
            $getStatus = json_decode(wp_remote_retrieve_body($data));
            
            if(isset($data['response']['code']) && ($data['response']['code'] == 200 || $data['response']['code'] == 204)) {
                $statusVal = 1;
            } 
            
            if ($statusVal == 1) {
                update_post_meta( $post_id, 'status_assessment', 'Suspended' );
            } else {

            }
        }

        /* Sanitizing the array */
        $post_req_array = $this->sanitize_the_array($_REQUEST['post']);

        $location = add_query_arg( array(
            'post_type' => 'tti_assessments',
            'assessment_suspended' => 'Suspended',
            'changed' => count( $post_req_array ),
            'ids' => join( $post_req_array, ',' ),
            'post_status' => 'all'
        ), 'edit.php' );
        wp_redirect( admin_url( $location ) );
        exit;
    }

    /**
     * Function to assessment update notices on suspend.
     *
     * @since   1.0.0
     *
     */
    public function assessment_suspended_order_status_notices() {
        global $pagenow, $typenow;
        if( $typenow == 'tti_assessments' 
         && $pagenow == 'edit.php'
         && isset( $_REQUEST['assessment_suspended'] )
         && $_REQUEST['assessment_suspended'] == 'Suspended'
         && isset( $_REQUEST['changed'] ) ) {
            $changed = sanitize_text_field($_REQUEST['changed']);
            $message = sprintf( _n( 'Status Suspended.', '%s Statuses Suspended.', $changed ), number_format_i18n( $changed ) );
            /**
            * Filter to update assessment suspended order status notices message
            * 
            * @since  1.2
            */
            $message = apply_filters('ttisi_platform_assessment_suspended_order_status_notices', $message);
            echo "<div class=\"updated\"><p>{$message}</p></div>";
        }
    }

    /**
     * Function to init TTI Shortcode button.
     *
     * @since   1.0.0
     */    
    public function assessment_theme_setup() {
        add_action( 'init', array($this, 'assessment_buttons'));
    }
    
    /**
     * Function to add shortcode button in WYSISYG.
     *
     * @since   1.0.0
     */
    public function assessment_buttons() {
        if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
            return;
        } 
        if ( get_user_option( 'rich_editing' ) !== 'true' ) {
            return;
        }
        add_filter( 'mce_external_plugins', array($this, 'assessment_add_buttons'));
        add_filter( 'mce_buttons', array($this, 'assessment_register_buttons'));
    }

    /**
     * Function to include script.
     *
     * @since   1.0.0
     * @param array $plugin_array contains plugins data
     * @return array return updated plugins data
     */
    public function assessment_add_buttons( $plugin_array ) {
        $plugin_array['mybutton'] = plugin_dir_url(__FILE__) . '/js/tti-platform-admin.js';
        /**
         * Filter to update assessment add buttons
         * 
         * @since  1.2
         */
         $plugin_array = apply_filters('ttisi_platform_assessment_add_buttons', $plugin_array);
        return $plugin_array;
    }

    /**
     * Function to register the button
     *
     * @since   1.0.0
     * @param array $buttons contains buttons
     * @return array
     */
    public function assessment_register_buttons( $buttons ) {
        array_push( $buttons, 'mybutton' );
        /**
         * Filter to registering assessment buttons
         * 
         * @since  1.2
         */
        $buttons = apply_filters('ttisi_platform_assessment_register_buttons', $buttons);
        return $buttons;
    }
}

/* Initialize the admin main class */
new TTI_Platform_Admin_Main_Class();