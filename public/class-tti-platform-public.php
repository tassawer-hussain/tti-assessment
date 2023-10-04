<?php

/**
 * Class to handle public end data.
 *
 * This is used to define internationalization, dashboard-specific hooks, and
 * public-facing site hooks. And to show public related data.
 *
 * @since   1.0.0
 * @package     TTI_Platform
 * @subpackage  TTI_Platform/includes
 * @author  Presstigers
 */

class TTI_Platform_Public_Main_Class
{

    /**
     * contains error log
     * @var object
     */
    public $error_log;

    /**
     * Define the core functionality of the plugin for frontend.
     *
     * @since       1.0.0
     */
    public function __construct()
    {

        /* error log */
        $this->error_log = new TTI_Platform_Deactivator_Error_Log();

        /*
         * Fronend Styles and Scripts Initialization
         */
        add_action('wp_enqueue_scripts', array($this, 'tti_platform_public_scripts'), 1);

        /*
         * Ajax Hook Initialization to Save Selected Feedback
         */
        add_action('wp_ajax_insertIsSelectedData', array($this, 'insertIsSelectedData'));
        add_action('wp_ajax_nopriv_insertIsSelectedData', array($this, 'insertIsSelectedData'));

        /*
         * Retaking ajax hooks
         */
        add_action('wp_ajax_tti_retaking_assessment', array($this, 'tti_retaking_assessment'));
        add_action('wp_ajax_nopriv_tti_retaking_assessment', array($this, 'tti_retaking_assessment'));

        /*
         * Save group settings
         */
        add_action('wp_ajax_tti_group_save_settings', array($this, 'tti_group_save_settings'));
        add_action('wp_ajax_nopriv_tti_group_save_settings', array($this, 'tti_group_save_settings'));

        /*
         * Ajax hook initialization to Take Assessment Process
         */
        add_action('wp_ajax_take_assessment', array($this, 'take_assessment'));
        add_action('wp_ajax_nopriv_take_assessment', array($this, 'take_assessment'));

        add_action('init', array($this, 'assessment_pdf_download_button'));

        /*
         * Assessment Completed Profiles Shortcode init
         */
        add_action('init', array($this, 'assessment_cp_shortcode_init'), 10);

        add_action('init', array($this, 'listenerLoader'), 1);

        /*
         * Assessment Shortcode init
         */
        add_action('init', array($this, 'assessment_shortcode_init'), 9);

        /*
         * Text Feedback Assessment Shortcode init
         */
        add_action('init', array($this, 'assessment_text_feedback_init'), 10);

        /*
         * Text Feedback Assessment Shortcode init
         */
        add_action('init', array($this, 'assessment_assessment_history'), 10);

        /*
         * Graphic Feedback Assessment Shortcode init
         */
        add_action('init', array($this, 'assessment_graphic_feedback_init'), 10);

        /*
         * PDF Download Shortcode init
         */
        add_action('init', array($this, 'assessment_PDF_init'), 10);

        /*
         * Print PDF download button
         */
        add_action('init', array($this, 'assessment_print_pdf_button_init'), 10);

        /**
         * Add shortcode to take assessment on site. 
         */
        add_shortcode( 'take_assessment_on_site', array($this, 'tti_take_assessment_on_site' ) );

        /**
         * roll back seat if user didn't start the course.
         */
        add_action( 'wdm_removal_request_accepted_successfully', array( $this, 'tti_rollback_group_seat_on_removal' ), 9999, 2 );

        /**
         * Update Labels for product purchase using "LearnDash Group Registration"
         */
        add_filter('wdm_gr_single_label', array( $this, 'tti_update_wdm_gr_single_label' ), 99, 1 );
        add_filter('wdm_gr_group_label', array( $this, 'tti_update_wdm_gr_group_label' ), 99, 1 );

        // Payment complete. - Check retake assessment process.
        add_action( 'woocommerce_payment_complete', array( $this, 'retake_assessment_on_self_purchase' ) );

        add_filter( 'wdm_group_email_subject', array( $this, 'th_wdm_group_email_subject' ), 999, 3 );
        add_filter( 'wdm_group_email_body', array( $this, 'th_wdm_group_email_body' ), 999, 3 );
    }

    public function th_wdm_group_email_subject( $subject, $group_id, $member_user_id ) {
        global $wpdb;

        $mylink = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->usermeta WHERE meta_key = '%s'", "learndash_group_leaders_$group_id" ) );
        $group_leader_id = $mylink->user_id;

        $leader_data  = get_user_by( 'id', $group_leader_id );

        $subject = str_replace( ucfirst( strtolower( $leader_data->first_name ) ), $leader_data->first_name, $subject );
        $subject = str_replace( ucfirst( strtolower( $leader_data->last_name ) ), $leader_data->last_name, $subject );

        return $subject;
    }

    public function th_wdm_group_email_body( $body, $group_id, $member_user_id ) {
        global $wpdb;

        $mylink = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->usermeta WHERE meta_key = '%s'", "learndash_group_leaders_$group_id" ) );
        $group_leader_id = $mylink->user_id;

        $leader_data  = get_user_by( 'id', $group_leader_id );

        $body = str_replace( ucfirst( strtolower( $leader_data->first_name ) ), $leader_data->first_name, $body );
        $body = str_replace( ucfirst( strtolower( $leader_data->last_name ) ) , $leader_data->last_name, $body );

        return $body;
    }

    public function retake_assessment_on_self_purchase( $order_id ) {
        
        // retrive order object
        $order = wc_get_order( $order_id );
        $user = $order->get_user();

        // echo "<pre>";
        // $this->error_log->put_error_log('*********************************** Self Purchase Item Function Start ********************************************');
        foreach ( $order->get_items() as $item_id => $item ) {
            
            // 1 item bought for this product and it is self purchase 
            if( 'New Group' == $item->get_meta('Option Selected') && 1 == $item->get_quantity() ) {

                // $this->error_log->put_error_log('*********************************** Self Purchase Item ********************************************');
                
                //echo "self purchase";
                $product_id = $item->get_product_id();
                $course_id = get_post_meta( $product_id, '_related_course', true );

                // course and its content ids
                $content_ids = array();
                foreach($course_id as $c_id) {
                    global $wpdb;
    
                    $key = 'ld_course_'.$c_id;
                    $content_ids[] = $c_id; /* assign course id */
                    
                    $meta = $wpdb->get_results("SELECT * FROM `".$wpdb->postmeta."` WHERE meta_key='".$wpdb->escape($key)."'");
                    foreach ($meta as $key => $value) {
                        if(isset($value->post_id) && !empty($value->post_id)) {
                            $content_ids[] = $value->post_id;
                        }
                    }
                    
                    $meta = $wpdb->get_results("SELECT * FROM `".$wpdb->postmeta."` WHERE meta_key='course_id' AND meta_value=".$c_id);
                    foreach ($meta as $key => $value) {
                        if(isset($value->post_id) && !empty($value->post_id)) {
                            $content_ids[] = $value->post_id;
                        }
                    }
    
                    $content_ids = array_unique($content_ids);
                }

                // get all TTISI assessments
                $args = array( 'post_type' => 'tti_assessments');
                $loop = new WP_Query( $args );

                $tti_assessments = array();
                foreach( $content_ids as $content_id ) {
                    $content_post = get_post($content_id);
                    $content = $content_post->post_content;

                    while ( $loop->have_posts() ) : $loop->the_post();

                        $searc_string  = '[take_assessment assess_id="'.get_the_id();
                        $searc_string2 = "[take_assessment assess_id='".get_the_id();

                        if (
                            strpos( $content, $searc_string ) !== false || 
                            strpos( $content, $searc_string2 ) !== false 
                        ) {
                            $tti_assessments[] = array(
                                'link_id'    => get_post_meta( get_the_id(), 'link_id', true ),
                                'assess_ids' => get_the_id(),

                            );
                        }
                    endwhile;
                }

                // $this->error_log->put_error_log( $content_ids, 'array' );
                // $this->error_log->put_error_log( $tti_assessments, 'array' );
                
                // Check 
                // - User already completed assessment
                // - User limit record exist 
                global $wpdb;
                $users_limit = $wpdb->prefix.'tti_users_limit';
                $assessment_table_name = $wpdb->prefix.'assessments';
                
                foreach( $tti_assessments as $assessment ) {
                    $exists_in_db = false;
                    $limit = 0;
                    $usrID = $user->ID;
                    $group_id = 0;
                    $link_id = $assessment['link_id'];

                    $results = $wpdb->get_row("SELECT * FROM $assessment_table_name WHERE user_id ='$usrID' AND link_id='$link_id' AND status = 1");  
                    // $this->error_log->put_error_log( $results, 'array' );  
                    
                    if( isset( $results ) && !empty( $results ) ) {
                        
                        // user already completed assessment
                        $results = $wpdb->get_row("SELECT * FROM $users_limit WHERE user_id ='$usrID' AND data_link = '$link_id'");
                        // $this->error_log->put_error_log( $results, 'array' );  
                        if( isset( $results ) && !empty( $results ) ) {
                            
                            // user limit record exits in database
                            $limit = $results->limits;
                            $updateQuery = $wpdb->update (
                                $users_limit, 
                                array (
                                    'limits' => $limit + 1
                                ), 
                                array (
                                    'user_id' => $usrID,
                                    'data_link' => $link_id
                                )
                            );

                            // $this->error_log->put_error_log( $updateQuery);  
                        } else {
                            // user limit record do not exists
                            $insertQuery = $wpdb->insert (
                                $users_limit, 
                                array (
                                   "user_id" => $usrID,
                                   "email" => $user->user_email,
                                   "group_id" => $group_id,
                                   "limits" => 1,
                                   "data_link" => $link_id
                                ),
                                array ( '%d', '%s', '%s', '%d', '%s' )
                            );
                            // $this->error_log->put_error_log( $insertQuery);  
                        }
                    }
                }
            }

        }
        // print_r($order);
        // print_r($user->ID);
        // print_r($user->user_email);
        // echo "</pre>";
        
    }

    /**
     * Change Individual Label
     *
     * @param string $label
     * @return string
     */
    public function tti_update_wdm_gr_single_label( $label ) {
        return  __( 'Take Myself', 'wdm_ld_group' );
    }

    /**
     * Change Group Label
     *
     * @param string $label
     * @return string
     */
    public function tti_update_wdm_gr_group_label( $label ) {
        return  __( 'Assign to Others', 'wdm_ld_group' );
    }

    /**
     * roll back seat if user didn't start the course.
     */
    public function tti_rollback_group_seat_on_removal( $group_id, $user_id ) {

        $seat_rollback = false;
        $course_id = th_get_current_group_courses( $group_id );
        $is_assessment_group = is_group_has_assessment_shortcode( $group_id );

        if ( $is_assessment_group ) {
            $content_ids = array();
            $links_id = array();
            $assess_ids = array();

            if( count( $course_id ) > 0 ) {
                foreach ($course_id as $c_id) { 
                    $content_ids = array_merge( $content_ids, get_contents_post_id_by_course_id( $c_id ) );
                }
                $content_ids = array_merge( $content_ids, $course_id );
                
                if( count( $content_ids ) > 0) {
                    $content_ids = array_unique($content_ids);

                    $args = array( 'post_type' => 'tti_assessments');
                    $loop = new WP_Query( $args );
                    
                    foreach ($content_ids as $key => $content_id) { 
                        $content_post = get_post( $content_id );
                        if( isset( $content_post->post_content ) ) {
                            $content = $content_post->post_content;
                            $content = wpautop( $content );
                            
                            while ( $loop->have_posts() ) : $loop->the_post();
                            $searc_string = '[take_assessment assess_id="'.get_the_id().'"';
                            if (strpos($content, $searc_string) !== false) {
                                $links_id[] = get_post_meta( get_the_id(), 'link_id', true );
                                $assess_ids[] =  get_the_id();
                            }
                        endwhile;
                    
                        }
                    }
                }
            }
            if( count( $links_id ) > 0) {
                foreach ($links_id as $link_id ) {

                    global $wpdb;
                    $assessment_table_name = $wpdb->prefix.'assessments';
                    $results = $wpdb->get_row("SELECT * FROM $assessment_table_name WHERE user_id ='$user_id' AND link_id='$link_id' AND status = 1");
                    
                    if( empty($results) ) {
                        $seat_rollback = true;
                    }
                    
                }
            }
        } else {
            $course_status = learndash_user_get_course_progress( $user_id,  $course_id[0] );
            if ( "not_started" == $course_status['status'] ) {
                $seat_rollback = true;
            }
        }


        if ( $seat_rollback ) {
            $group_limit = get_post_meta( $group_id, 'wdm_group_total_users_limit_' . $group_id, true );
            if ( $group_limit == '' ) {
                $group_limit = 0;
            }

            $group_limit = $group_limit + 1;
            update_post_meta( $group_id, 'wdm_group_total_users_limit_' . $group_id, $group_limit );

            // Log the user removal.
            $group_title = get_the_title( $group_id );
            $user_data = get_user_by( 'id', $user_id );
            $removal_date = date("Y-m-d");
            
            $message = 'User ('. $user_data->first_name .' '. $user_data->last_name. ' with email address '. $user_data->user_email .' ) removed from Group on '. $removal_date .' by Group Leader and';
            $message .= ' a seat was added to group "' . $group_title . '"';
            
            $this->error_log->put_error_log('*********************************** User Removed & Seat Rollback - Group: '. $group_title .' ********************************************');
            $this->error_log->put_error_log($message);

            $user_query_args = array(
				'orderby'    => 'display_name',
				'order'      => 'ASC',
				'meta_query' => array(
					array(
						'key'     => 'learndash_group_leaders_' . intval( $group_id ),
						'value'   => intval( $group_id ),
						'compare' => '=',
						'type'    => 'NUMERIC',
					),
				),
			);
			$user_query      = new WP_User_Query( $user_query_args );
			if ( isset( $user_query->results ) ) {
				$group_user_objects = $user_query->results;
                foreach ( $group_user_objects as $key => $value ) {
                    $leader_data = get_user_by( 'id', $value->ID );
                    $leader_details = 'Group Leader ('. $leader_data->first_name .' '. $leader_data->last_name. ' with email address '. $leader_data->user_email .' )';
                    $this->error_log->put_error_log($leader_details);
                }
			}
        }

    }

    /**
     * Function to print PDF download button shortcode function.
     *
     * @since    1.6.3
     */
    public function assessment_print_pdf_button_init()
    {
        if (isset($_GET['tti_print_consolidation_report']) && $_GET['tti_print_consolidation_report'] == 1) {

            $this->tti_print_pdf_report();
        }
        add_shortcode('assessment_print_pdf_button_download_report', array($this, 'assessment_print_pdf_button_shortcode'));
    }

    /**
     * Function to print PDF download button shortcode function.
     *
     * @since    1.6.3
     *
     * @param array $atts contains shortcode options
     * @param array $content contains shortcode content
     * @param array $tag contains shortcode tag
     * @return string contains download PDF link
     */
    public function assessment_print_pdf_button_shortcode($atts = [], $content = null, $tag = '')
    {
        require_once plugin_dir_path(__FILE__) . 'pdf/class-tti-platform-pdf-report.php';
        $print_button    = new TTI_Platform_Public_PDF_Report();
        $atts            = array_change_key_case((array) $atts, CASE_LOWER);
        $assessment_atts = shortcode_atts([
            'assess_id' => '',
            'type'      => 'type_one',
        ], $atts, $tag);
        $assess_id   = $assessment_atts['assess_id'];
        $report_type = $assessment_atts['type'];

        if (!empty($assess_id)) {
            // $pageURL = esc_url_raw($_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"]);
            $pageURL = get_site_url();
            return '
                <a style="" target="__blank" href="' . $pageURL . '?report_type=' . esc_attr($report_type) . '&assess_id=' . esc_attr($assess_id) . '&tti_print_consolidation_report=1" class="tti_print_report_pdf" data-type="' . $assess_id . '" data-assessid="' . $report_type . '"><img width="40" src="' . plugin_dir_url(__FILE__) . 'images/download.png" alt="" /></a>
            ';
        }

    }

    /**
     * Function to print PDF download button shortcode function implementation.
     *
     * @since    1.6.3
     */
    public function tti_print_pdf_report()
    {
        require_once plugin_dir_path(__FILE__) . 'pdf/class-tti-platform-pdf-report.php';
        $print_button = new TTI_Platform_Public_PDF_Report();
        $assess_id    = sanitize_text_field($_GET['assess_id']);
        $report_type  = sanitize_text_field($_GET['report_type']);
        $print_button->download_report($assess_id, $report_type);
        wp_die();
    }

    /**
     * Function to retaking assessment ajax action process.
     *
     * @since    1.5.1
     */
    public function tti_retaking_assessment()
    {

        $user_id         = sanitize_text_field($_POST['user_id']);
        $email           = sanitize_text_field($_POST['email']);
        $group_id        = sanitize_text_field($_POST['group_id']);
        $link_id         = sanitize_text_field($_POST['link_id']);
        $group_leader_id = sanitize_text_field($_POST['group_leader_id']);

        /* Classes required in AJAX class */
        require_once plugin_dir_path(__FILE__) . 'partials/group-leader-extensions/includes/class-tti-platform-ajax-class.php';
        $retake_ass = new TTI_Platform_Group_Leader_Ajax($user_id, $email, $group_id, $link_id, $group_leader_id);
        $retake_ass->retake_assessment_process();

    }

    /**
     * Function to retaking assessment ajax action process.
     *
     * @since    1.5.1
     */
    public function tti_group_save_settings()
    {

        $block_email     = sanitize_text_field($_POST['block_email']);
        $group_id        = sanitize_text_field($_POST['group_id']);
        $group_leader_id = sanitize_text_field($_POST['group_leader_id']);

        /* Classes required in AJAX class */
        require_once plugin_dir_path(__FILE__) . 'partials/group-leader-extensions/group-settings/class-tti-platform-gp-settings-ajax-class.php';
        $tti_group_save_settings = new TTI_Platform_Group_Leader_Settings_Ajax($group_leader_id, $group_id);
        $tti_group_save_settings->tti_group_save_settings($block_email);
    }

    /**
     * Function to for a custom sanitization that will take the incoming input, and sanitize
     * the input before handing it back to WordPress to save to the database.
     *
     * @since    1.0.0
     *
     * @param array $input contains array needs to sanitize
     * @return array contains sanitized array
     */
    public function sanitize_the_array($input)
    {
        // Initialize the new array that will hold the sanitize values
        $new_input = array();
        // Loop through the input and sanitize each of the values
        foreach ($input as $key => $val) {
            $new_input[$key] = sanitize_text_field($val);
        }
        return $new_input;
    }

    /**
     * Function to handle listener shortcode process.
     *
     * @since   1.0.0
     */
    public function listenerLoader()
    {

        /*
         * Listener shortcode Initialization
         */
        global $current_user, $wpdb;

        $url             = strtok(esc_url_raw($_SERVER["REQUEST_URI"]), '?');
        $getSecretOption = get_option('ttiplatform_secret_key');
        // $getLink = sanitize_text_field($_GET['link']);
        // $getPassword = sanitize_text_field($_GET['password']);
        // $getSecret = sanitize_text_field($_GET['key']);
        $getLink     = isset($_GET['link']) ? sanitize_text_field($_GET['link']) : '';
        $getPassword = isset($_GET['password']) ? sanitize_text_field($_GET['password']) : '';
        $getSecret   = isset($_GET['key']) ? sanitize_text_field($_GET['key']) : '';

        if (!is_admin() && strpos($url, 'listener') != false) {

            if (is_user_logged_in()) {
                wp_get_current_user();

                /* Enqueue style and script */
                //$this->tti_platform_preloader_scripts();

                /* Check assessment lock status */
                $assessment_id = $this->get_post_id_by_meta_key_and_value('link_id', $getLink);
                $status_locked = get_post_meta($assessment_id, 'status_locked', true);

                $assessmentListener       = get_transient('assessmentListener' . $current_user->ID);
                $asseListRetakeAsseStatus = get_transient('assessmentListenerRetakeAsseStatus' . $current_user->ID);

                if ($asseListRetakeAsseStatus == 'true') {
                    $status_locked = 'true';
                    $assessment_id = -1; // it means user assessment will be used
                }

                /* if assessment is opened */
                if ($status_locked == 'false') {
                    //$this->error_log->put_error_log('Assessment Type : Open');
                    /**
                     * Hook to check and initiate process if assessment locked status is opened
                     */
                    do_action('tti_assessment_open_link_take_assessment', $assessment_id, $getLink, $getPassword);
                }

                if (isset($getLink) && !empty($getLink) && isset($getPassword) && !empty($getPassword) && $getSecret == $getSecretOption && $status_locked != 'false') {
                    /* if assessment is locked */
                    //$this->error_log->put_error_log('Assessment Type : Closed');

                    /* set transient to update webhook should not call */
                    set_transient('ttiPlatformCheckWebhookNeed' .$current_user->ID , 'no', DAY_IN_SECONDS);

                    add_shortcode('assessment_listener', array($this, 'listener_shortcode'));
                    $assessment_table_name  = $wpdb->prefix . 'assessments';
                    $results                = $wpdb->get_row("SELECT * FROM $assessment_table_name WHERE user_id ='$current_user->ID' AND password='$getPassword'");
                    $status_of_the_user_ass = $results->status;
                    ?>
                    <?php
$w .= '
                            <style>
                            .tti-platform {
                                position: fixed;
                                width: 100%;
                                height: 100%;
                                z-index: 999999999;
                                background: #042545;
                                top: 0;
                                left: 0;
                            }
                            </style>
                            <div class="tti-platform">
                                <div class="preloader-wrap">
                                    <div id="precent" class="percentage"></div>
                                    <div class="loader">
                                        <div class="trackbar">
                                            <div class="loadbar"></div>
                                        </div>
                                        <p>Scoring Assessment Please Wait</p>
                                    </div>
                                </div>
                            </div>';
                    if ($status_of_the_user_ass != 1) {
                        // echo $w;
                    }
                } elseif ($status_locked != 'false') {
                    $w .= ' <style>
                        .tti-platform {
                            position: fixed;
                            width: 100%;
                            height: 100%;
                            z-index: 999999999;
                            background: #042545;
                            top: 0;
                            left: 0;
                        }
                        </style>
                        <div class="tti-platform">
                            <div class="preloader-wrap">
                                <div class="loader" style="border: none; text-align: center;">
                                    <p style="font-size: 30px;">Error! There is something wrong in the URL.</p>
                                </div>
                            </div>
                        </div>';
                    if ($status_of_the_user_ass != 1) {
                        // echo $w;
                    }
                }
            } elseif ($status_locked != 'false') {
                $w .= ' <style>
                        .tti-platform {
                            position: fixed;
                            width: 100%;
                            height: 100%;
                            z-index: 999999999;
                            background: #042545;
                            top: 0;
                            left: 0;
                        }
                        </style>
                        <div class="tti-platform">
                            <div class="preloader-wrap">
                                <div class="loader" style="border: none; text-align: center;">
                                    <p style="font-size: 30px;">Sorry! You must be logged in to access this page.</p>
                                    <a href="' . esc_url(wp_login_url()) . '" alt="">Login Now</a>
                                </div>
                            </div>
                        </div>';
                if ($status_of_the_user_ass != 1) {
                    // echo $w;
                }
            }

        }
    }

    /**
     * Function to embed needed fronend Styles and Scripts
     *
     * @since   1.0.0
     */
    public function tti_platform_public_scripts()
    {
        if (!is_admin()) {

            /* General CSS and JS */
            wp_register_style('tti_platform_public_style', plugin_dir_url(__FILE__) . 'css/tti-platform-public.css', array(), $this->generateRandomString(), 'all');

            wp_register_style('tti_platform_admin_style_sweetalert', 'https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.33.1/sweetalert2.css', array(), $this->generateRandomString(), 'all');

            wp_register_script('tti_platform_public_script', plugin_dir_url(__FILE__) . 'js/tti-platform-public.js', array('jquery'), $this->generateRandomString(), 'all');

            wp_register_script('tti_platform_admin_script_sweetalert', 'https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.33.1/sweetalert2.all.min.js', array('jquery'), $this->generateRandomString(), 'all');

            /* Datatables */
            wp_localize_script(
                'tti_platform_public_script', 'tti_platform_public_ajax_obj', array('ajaxurl' => admin_url('admin-ajax.php'))
            );

            /** Deques the WDM File and register modified version. */
            wp_dequeue_script( 'wdm_remove_js' );
            wp_deregister_script( 'wdm_remove_js' );
            wp_register_script('wdm_remove_js', plugin_dir_url(__FILE__) . 'js/wdm_remove.js', '', $this->generateRandomString(), 'all');
        }
    }

    /**
     * Function to enqueue the styles.
     *
     * @since       1.4.2
     */
    public function ttisi_enqueue_styles()
    {
        wp_enqueue_style('tti_platform_public_style');
        wp_enqueue_style('tti_platform_admin_style_sweetalert');
        wp_enqueue_script('tti_platform_public_script');
        wp_enqueue_script('tti_platform_admin_script_sweetalert');
    }

    /**
     * Function to get post id by meta key and value.
     *
     * @since   1.2
     *
     * @param string $key contains meta key
     * @param string $value contains meta value
     *
     * @return boolean/integer return post id or falseFunction to
     */
    public function get_post_id_by_meta_key_and_value($key, $value)
    {
        global $wpdb;
        $meta = $wpdb->get_results("SELECT * FROM `" . $wpdb->postmeta . "` WHERE meta_key='" . $wpdb->escape($key) . "' AND meta_value='" . $wpdb->escape($value) . "'");
        if (is_array($meta) && !empty($meta) && isset($meta[0])) {
            $meta = $meta[0];
        }
        if (is_object($meta)) {
            return $meta->post_id;
        } else {
            return false;
        }
    }

    /**
     * Function to show loading bar after assessment completed.
     *
     * @since   1.2
     *
     * @param integer $current_user_ID contains user id
     */

    public function loading_bar_completing_assessment($current_user_ID)
    {
        $assessmentListener = get_transient('assessmentListener' . $current_user_ID);
        ?>
            <script type="text/javascript">
                setTimeout(function(){
                    window.location = "<?php echo $assessmentListener; ?>";
                }, 1000);
            </script>
        <?php
}

    /**
     * Function to load scripts.
     *
     * @since   1.0.0
     */
    public function tti_platform_preloader_scripts()
    {
        wp_enqueue_style('tti_platform_public_style', plugin_dir_url(__FILE__) . 'css/tti-platform-public.css', array(), $this->generateRandomString(), 'all');

        wp_enqueue_script('tti_platform_public_script', plugin_dir_url(__FILE__) . 'js/tti-platform-public.js', array('jquery'), $this->generateRandomString(), true);
    }

    /**
     * Function to show success assessment complete message.
     *
     * @since   1.0.0
     */
    public function show_success_message()
    {
        ?>
        <script type="text/javascript">
           $('.tti-platform .preloader-wrap .trackbar p').text('Assessment Completed');
        </script>
        <?php
}

    /**
     * Function to show loading bar while redirecting to assessment page.
     *
     * @since   1.0.0
     *
     */
    public function display_list_loading_bar()
    {

        $w = '
                            <style>
                            .tti-platform {
                                position: fixed;
                                width: 100%;
                                height: 100%;
                                z-index: 999999999;
                                background: #042545;
                                top: 0;
                                left: 0;
                            }

                            </style>
                            <div class="tti-platform">
                                <div class="preloader-wrap">
                                    <div id="precent" class="percentage"></div>
                                    <div class="loader">
                                        <div class="trackbar">
                                            <div class="loadbar"></div>
                                        </div>
                                        <p>Scoring Assessment Please Wait</p>
                                    </div>
                                </div>
                            </div>';

        echo $w;

    }

    /**
     *  Function to get user assessment latest version.
     *
     * @since   1.6
     *
     * @param integer $c_usrid contains user id
     * @param string $link_id contains assessment link id
     *
     * @return integer contains count of assessments
     */
    public function get_current_user_assess_version($c_usrid, $link_id)
    {
        global $wpdb;
        $results               = array('one');
        $assessment_table_name = $wpdb->prefix . 'assessments';
        $results               = $wpdb->get_results("SELECT * FROM $assessment_table_name WHERE user_id ='$c_usrid' AND link_id='$link_id'");
        if (isset($results) && count($results) > 0) {
            return count($results);
        }
        return count($results);
    }

    /**
     * Function for the listener shortcode showing on public end.
     *
     * @since   1.0.0
     */

    public function listener_shortcode($atts = [])
    {
        global $wpdb, $current_user;

        $update_limit_flag = true;

        //$report_view_id = 0;

        /* Check if call from webhook */
        if (isset($atts['webhook']) && $atts['webhook'] == true) {
            $getLink     = sanitize_text_field($atts['link_id']);
            $getPassword = sanitize_text_field($atts['password']);
        } else {
            $getLink     = sanitize_text_field($_GET['link']);
            $getPassword = sanitize_text_field($_GET['password']);
            $this->error_log->put_error_log('*********************************** Starts Listener Page ********************************************');
            $this->error_log->put_error_log('Redirecting to listener page...');
        }

        if (
            is_user_logged_in() ||
            (isset($atts['webhook']) &&
                $atts['webhook'] == true
            )
        ) {

            wp_get_current_user();
            //$current_user_id = $current_user->ID;


            /* if call from webhook then set user id by function parameter */
            if (isset($atts['webhook']) && $atts['webhook'] == true) {
                $current_user_id = $atts['user_id'];
            } else {
                $current_user_id = $current_user->ID;
            }

            $asseListRetakeAsseStatus = get_transient('assessmentListenerRetakeAsseStatus' . $current_user_id);

            if ($asseListRetakeAsseStatus == 'true') {
                $getLink = get_transient('assessmentListenerRetakeAsseLink' . $current_user_id);
                //$report_view_id = get_transient('assessmentListenerReportViewID'.$current_user->ID);
            }

            /* Get assessment version */
            $asses_version = $this->get_current_user_assess_version($current_user_id, $getLink);
            //var_dump$asses_version);
            /*
             * Check if password exist
             */
            $assessment_table_name = $wpdb->prefix . 'assessments';
            $results               = $wpdb->get_row("SELECT * FROM $assessment_table_name WHERE user_id ='$current_user_id' AND password='$getPassword' AND version = $asses_version");

            //echo '<pre>results ';print_r($results);'</pre>';

            $api_token     = $results->api_token;
            $respondent_id = $results->password;
            $account_id    = $results->account_id;
            $api_service   = $results->service_location;

            $status_of_the_user_ass = $results->status;

            /* Check if that assessment is already completed or not */
            if ($status_of_the_user_ass != 1) {

                /* API v3.0 url */
                $url = $api_service . '/api/v3/reports?account_login=' . $account_id . '&respondent_passwd=' . $getPassword;
                //var_dump$url);
                $headers = array(
                    'Authorization' => $api_token,
                    'Accept'        => 'application/json',
                    'Content-Type'  => 'application/json',
                );
                $args = array(
                    'method'  => 'GET',
                    'headers' => $headers,
                    'timeout' => 15000,
                );

                $response = wp_remote_request($url, $args);

                if (is_wp_error($response)) {
                    $error_message = $response->get_error_message();
                    echo $error_message;
                } else {

                    $api_response = json_decode(wp_remote_retrieve_body($response));
                    //echo '<pre>first api response ';print_r($api_response);'</pre>';
                    if (isset($api_response) && count($api_response) <= 0) {
                        /* If user exists assessment or not completed because of some reason */
                        $first_name        = $results->first_name;
                        $last_name         = $results->last_name;
                        $email             = $results->email;
                        $update_limit_flag = false;
                        $this->error_log->put_error_log('User exit the assessment');
                    } else {
                        $first_name = $api_response[0]->respondent->first_name;
                        $last_name  = $api_response[0]->respondent->last_name;
                        $email      = $api_response[0]->respondent->email;
                    }

                    /* if retake assessment is true and there are more than 1 responses */
                    // if($asseListRetakeAsseStatus == 'true' && count($api_response) > 1) {
                    //     foreach ($api_response as $key => $value) {
                    //        if(isset($value->reportview->id) && $value->reportview->id == $report_view_id) {
                    //             $report_id = $api_response[$key]->id;
                    //             $gender = $api_response[$key]->respondent->gender;
                    //             $company = $api_response[$key]->respondent->company;
                    //             $position_job = $api_response[$key]->respondent->position_job;
                    //             break;
                    //        }
                    //     }
                    // } else {
                    $report_id    = $api_response[0]->id;
                    $gender       = $api_response[0]->respondent->gender;
                    $company      = $api_response[0]->respondent->company;
                    $position_job = $api_response[0]->respondent->position_job;
                    // }

                    //var_dump'report_id : '.$report_id);
                    //var_dump'gender : '.$gender);
                    //var_dump'company : '.$company);
                    //var_dump'position_job : '.$position_job);

                    /* User data for email template */
                    $user_email_data = array(
                        'first_name'   => $api_response[0]->respondent->first_name,
                        'last_name'    => $api_response[0]->respondent->last_name,
                        'email'        => $api_response[0]->respondent->email,
                        'company'      => $api_response[0]->respondent->company,
                        'position_job' => $api_response[0]->respondent->position_job,
                        'link_id'      => $getLink,
                    );
                    /* **************************** */

                    $updateQuery = $wpdb->update(
                        $assessment_table_name,
                        array(
                            'first_name'   => $first_name,
                            'last_name'    => $last_name,
                            'email'        => $email,
                            'report_id'    => $report_id,
                            'gender'       => $gender,
                            'company'      => $company,
                            'position_job' => $position_job,
                            'updated_at'   => date("Y-m-d H:i:s"),
                        ),
                        array(
                            'user_id'  => $current_user_id,
                            'password' => $getPassword,
                        )
                    );

                    if (false === $updateQuery) {
                        $message = __('There is somthing wrong.', 'tti-platform');
                    } else {

                        /* API v3.0 url */
                        $url = $api_service . '/api/v3/reports/' . $report_id;
                        //var_dump$url);
                        $headers = array(
                            'Authorization' => $api_token,
                            'Accept'        => 'application/json',
                            'Content-Type'  => 'application/json',
                        );
                        $args = array(
                            'method'  => 'GET',
                            'headers' => $headers,
                            'timeout' => 15000,
                        );
                        $response = wp_remote_request($url, $args);

                        if (is_wp_error($response)) {
                            $this->error_log->put_error_log('Error in response in report data request');
                            $error_message = $response->get_error_message();
                            echo $error_message;
                        } else {
                            $current_usesssr = $current_user_id;
                            $this->error_log->put_error_log('Report ID: ' . $report_id);
                            $this->error_log->put_error_log('Successfully Saved Report Data');
                            $api_response = json_decode(wp_remote_retrieve_body($response));
                            //echo '<pre>second report api response ';print_r($api_response);'</pre>';
                            $report_id = $api_response->report->info->reportid;

                            /* Initialize the Group Leaders of current assessment */

                            //$link_id = $api_response->report->info->linkid;
                            $link_id               = $getLink;
                            $assessment_table_name = $wpdb->prefix . 'assessments';

                            $results = $wpdb->get_row("SELECT * FROM $assessment_table_name WHERE user_id ='$current_usesssr' AND password='$getPassword' AND report_id='$report_id' AND link_id='$link_id' AND version = $asses_version");

                            /* if webhook call then check status field in DB */
                            if (
                                isset($atts['webhook']) && 
                                $atts['webhook'] == true && 
                                isset($results->status) && 
                                $results->status == 1
                            ) {
                                 return '*********************************** Ends Webhook *******************************************';
                            }   

                            if ($wpdb->num_rows > 0) {
                                $updateQuery = $wpdb->update($assessment_table_name, array(
                                    'status'            => 1,
                                    'assessment_result' => serialize($api_response),
                                ), array(
                                    'user_id'  => $current_usesssr,
                                    'password' => $getPassword,
                                )
                                );
                            }
                            $args = array(
                                'ID'           => $current_usesssr,
                                'first_name'   => $first_name,
                                'last_name'    => $last_name,
                                'display_name' => $first_name . ' ' . $last_name,
                                'user_email'   => esc_attr($email),
                            );

                            wp_update_user($args);

                            if ($update_limit_flag) {
                                /* Updating user limit for current assessment completed */
                                $this->update_user_limit_after_comp_assess($current_usesssr, $getLink);
                            }

                            /* Get assessment id */
                            $assessment_id = $this->get_post_id_by_meta_key_and_value('link_id', $getLink);

                            if ($assessment_id && !empty($report_id)) {
                                $send_rep_group_lead = (!empty(get_post_meta($assessment_id, 'send_rep_group_lead', true))) ? get_post_meta($assessment_id, 'send_rep_group_lead', true) : '';
                                if ($send_rep_group_lead == 'Yes') {
                                    $this->error_log->put_error_log('User Details');
                                    $this->error_log->put_error_log($user_email_data, 'array');
                                    $this->error_log->put_error_log('Sending mail to group leaders');

                                    //$this->error_log->put_error_log('Sending email to group leader');

                                    /* Intiate process of sending reports to group leaders */
                                    $this->initiate_group_leader_email_process(
                                        $ass_id,
                                        $report_id,
                                        $api_token,
                                        $api_service,
                                        $current_usesssr,
                                        $user_email_data,
                                        $assessment_id,
                                        false
                                    );
                                } else {
                                    $this->error_log->put_error_log('Email Sent Option Not Checked');
                                }
                            }

                            /* ***************************** */

                        }
                        /* Check if call from webhook */
                        if (isset($atts['webhook']) && $atts['webhook'] == true) {
                            return '*********************************** Ends Webhook *******************************************';
                        } else {
                            /* Loading bar after completing assessment */
                            $this->loading_bar_completing_assessment($current_usesssr);
                            $message = __('Your assessment has been successfully completed.', 'tti-platform');
                        }
                        /**
                         * Filter to take successful assessment message
                         *
                         * @since  1.2
                         */
                        $message = apply_filters('ttisi_platform_success_take_assessments_msg', $message);
                    }
                }
            } else {
                $message = __('Assessment Completed.', 'tti-platform');
            }
        }
        $o = '';
        /**
         * Fires before take assessment successful message block.
         *
         * @since   1.2
         */
        do_action('ttisi_platform_before_success_take_assessments_msg_block');
        $o .= '<div class="assessment_button">';
        $o .= '<h2>' . esc_html($message) . '<h2>';
        $o .= '</div>';
        /**
         * Fires after take assessment successful message block.
         *
         * @since   1.2
         */
        do_action('ttisi_platform_after_success_take_assessments_msg_block');

        return $o;
    }

    /**
     * Function to return assessments by user id
     * @since   1.7.0
     */
    public function tti_return_assessments_curr_user($group_leader_id)
    {
        $details = get_user_meta($group_leader_id, 'user_assessment_data', true);
        $details = reset(unserialize($details));
        if (
            isset($details['send_rep_group_lead']) &&
            $details['send_rep_group_lead'] == 'Yes'
        ) {
            return true;
        }
        return false;
    }

    /**
     * Function to reduce user limit for taking assessment.
     *
     * @since   1.6
     *
     * @param integer $current_user contains current user id
     * @param string $link_id contains link id
     */
    public function update_user_limit_after_comp_assess($current_user, $link_id)
    {
        global $wpdb;
        $users_limit = $wpdb->prefix . 'tti_users_limit';
        $results     = $wpdb->get_row("SELECT * FROM $users_limit WHERE user_id ='$current_user' AND data_link = '$link_id'");

        if (isset($results) && count($results) > 0) {
            $limit = 0;
            if (isset($results->limits) && $results->limits > 0) {
                $limit = $results->limits - 1;
            }
            $updateQuery = $wpdb->update(
                $users_limit,
                array(
                    'limits' => $limit,
                ),
                array(
                    'user_id'   => $current_user,
                    'data_link' => $link_id,
                )
            );
        }
    }

    /**
     * Function to send mail to group leaders.
     *
     * @since    1.2.1
     *
     * @param integer $ass_id contains assessment id
     * @param integer $report_id contains report id
     * @param string $api_key contains assessment API key
     * @param string $api_service contains service location link
     * @param integer $current_user contains current user id
     * @param integer $assessment_id contains assessment id
     * @param boolean $report_view_status contains report view status
     * @return boolean contains result for download PDF status
     */
    public function initiate_group_leader_email_process(
        $ass_id,
        $report_id,
        $api_key,
        $api_service,
        $current_user_id,
        $user_email_data,
        $assessment_id,
        $report_view_status
    ) {
        $result_download_pdf = $this->group_leader_email($report_id, $api_key, $api_service, $current_user_id, $user_email_data, $assessment_id, $report_view_status);
    }

    /**
     * Function to send mail to group leaders.
     *
     * @since    1.2.1
     *
     * @param integer $ass_id contains assessment id
     * @param integer $report_id contains report id
     * @param string $api_key contains assessment API key
     * @param string $api_service contains service location link
     * @param integer $current_user contains current user id
     * @param boolean $report_view_status contains report view status
     * @param integer $assessment_id contains assessment id
     */
    public function group_leader_email(
        $report_id,
        $api_key,
        $api_service,
        $current_user_id,
        $user_email_data,
        $assessment_id,
        $report_view_status
    ) {
        // if($report_view_status) {
        //     $url = $api_service . '/api/v3/reportviews/' .$report_id. '.pdf';
        // } else {
        $url = $api_service . '/api/v3/reports/' . $report_id . '.pdf';
        // }

        $headers = array(
            'Authorization'             => $api_key,
            'Accept'                    => 'application/pdf',
            'Content-Type'              => 'application/pdf',
            'Content-Transfer-Encoding' => 'binary',
        );
        $args = array(
            'method'  => 'GET',
            'headers' => $headers,
            'timeout' => 10000,
        );

        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            echo json_encode($error_message);
        } else {

            /* Get group leaders mail associated to by assessment id */
            $mails_to_sent = $this->get_groupleader_mails_by_asses_id($current_user_id);
            $mails_to_sent = array_filter($mails_to_sent);

            if (count($mails_to_sent) > 0) {
                $downloadPath = $this->save_pdf_file($response, $user_email_data, $assessment_id);

                /* Send email to group leaders */
                $this->send_mail_to_group_leaders(
                    $mails_to_sent,
                    $downloadPath,
                    $current_user_id,
                    $user_email_data,
                    $assessment_id
                );

            } else {
                $this->error_log->put_error_log('No group leaders found for this user');
            }
        }
    }

    /**
     * Function to save file in uploads directory.
     *
     * @since   1.2
     *
     * @param integer $user_id contains user id
     * @return array contains group leader emails
     */
    public function get_groupleader_mails_by_asses_id($user_id)
    {
        global $current_user;
        //$gp_mails = array();

        // total_group_count
        //$group_ids = $this->get_group_ids_by_userid($user_id);
        //$user_data = get_user_by('id', $user_id);

        //$group_leaders_arr = array();
        // foreach ($group_ids as $k => $v) {
        //     $key = 'learndash_group_leaders_'.$v;
        //     $users = get_users(array(
        //         'meta_key'     => $key,
        //     ));

        //     if(!empty($users)) {
        //         $asseListRetakeAsseStatus = get_transient('assessmentListenerRetakeAsseStatus'.$user_id);
        //         foreach ($users as $key => $userss) {
        //             $result = true;
        //             if($asseListRetakeAsseStatus == 'true') {
        //                 $result = $this->tti_return_assessments_curr_user($userss->ID);
        //             }
        //             if(!$result) {
        //                 continue;
        //             } else {
        //                 $user_detail = get_userdata( $userss->ID );
        //                 $user_email = $user_detail->user_email;
        //                 $group_leaders_emails[] = $user_email;
        //             }
        //         }
        //     }
        // }
        $asseListGroupLeaders   = get_transient('assessmentListenerGroupLeaders' . $user_id);
        $user_detail            = get_userdata($asseListGroupLeaders);
        $group_leaders_emails[] = $user_detail->user_email;
        //$group_leaders_emails = array_unique($group_leaders_emails);

        return $group_leaders_emails;
    }

    /**
     * Function to get users groups ids.
     *
     * @since    1.2
     * @param integer $user_id contains user id
     * @return array return group ids
     */
    public function get_group_ids_by_userid($user_id)
    {
        global $wpdb;

        $group_ids = array();

        if (function_exists('learndash_is_admin_user') && function_exists('learndash_get_groups')) {

            if (!empty($user_id)) {
                if ((learndash_is_admin_user($user_id))) {

                    $args = array('post_type' => 'groups', 'posts_per_page' => -1);

                    $loop = new WP_Query($args);
                    while ($loop->have_posts()): $loop->the_post();
                        $id           = get_the_id();
                        $group_exists = get_user_meta($user_id, 'learndash_group_users_' . $id, true);
                        if ($group_exists && !empty($group_exists)) {
                            $group_ids[] = $group_exists;
                        }
                    endwhile;

                } else {

                    $args = array('post_type' => 'groups', 'posts_per_page' => -1);

                    $loop = new WP_Query($args);
                    while ($loop->have_posts()): $loop->the_post();
                        $id           = get_the_id();
                        $group_exists = get_user_meta($user_id, 'learndash_group_users_' . $id, true);
                        if ($group_exists && !empty($group_exists)) {
                            $group_ids[] = $group_exists;
                        }
                    endwhile;
                }
            }
        }
        return $group_ids;
    }

    /**
     * Function to get all groups.
     *
     * @since 1.2
     *
     * @param  bool     $id_only    return id's only
     * @return array                groups
     */
    public function learndash_get_groups_cust($id_only = false, $current_user_id = 0)
    {

        if (learndash_is_group_leader_user($current_user_id)) {
            return learndash_get_administrators_group_ids($current_user_id);
        } else if (learndash_is_admin_user($current_user_id)) {

            $groups_query_args = array(
                'post_type'      => 'groups',
                'nopaging'       => true,
                'posts_per_page' => -1,
            );

            if ($id_only) {
                $groups_query_args['fields'] = 'ids';
            }

            $groups_query = new WP_Query($groups_query_args);
            return $groups_query->posts;
        }
    }

    /**
     * Function to save file in uploads directory.
     *
     * @since   1.0.0
     *
     * @param array $response contains api response data
     * @param string $user_email_data contains user email address
     * @param integer $assessment_id contains assessment id
     * @return array contains downloaded PDF report file path
     */

    public function save_pdf_file($response, $user_email_data, $assessment_id)
    {
        $first_name = isset($user_email_data['first_name']) ? $user_email_data['first_name'] : 'ttisi';
        $last_name  = isset($user_email_data['last_name']) ? $user_email_data['last_name'] : 'platform-report';

        $title     = get_the_title($assessment_id);
        $titles    = str_replace(' ', '_', $title);
        $site_name = str_replace(' ', '_', get_bloginfo('name'));

        $file_name = $first_name . '_' . $last_name . '_' . $titles . '_' . $site_name;

        $date = date('d-m-Y', time());
        if (!file_exists(WP_CONTENT_DIR . '/uploads/tti_assessments/' . $date . '/')) {
            mkdir(WP_CONTENT_DIR . '/uploads/tti_assessments/' . $date . '/', 0777, true);
        }
        $downloadPath = WP_CONTENT_DIR . '/uploads/tti_assessments/' . $date . '/' . $file_name . '.pdf';

        $file = fopen($downloadPath, "w+");
        $body = wp_remote_retrieve_body($response);
        fputs($file, $body);
        fclose($file);
        return $downloadPath;
    }

    /**
     * Function to delete file in uploads directory.
     *
     * @since   1.2
     *
     * @param string $downloadPath contains file path
     */
    protected function delete_pdf_file($downloadPath)
    {
        //unlink($downloadPath);
    }

    /**
     * Function to generate random string.
     *
     * @since   1.2
     *
     * @param integer $length contains length of user
     * @return string return generated key
     */
    public function generate_random_string($length = 10)
    {
        $characters       = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString     = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * Function to send mail to group leaders using WordPress mail function.
     *
     * @since   1.2
     *
     * @param array $mails_to_sent contains array of emails
     * @param string $downloadPath contains download PDF file attachment
     * @param string $current_user contains user id
     * @param string $user_email_data contains user email address
     * @param integer $assessment_id contains assessment id
     */

    protected function send_mail_to_group_leaders(
        $mails_to_sent,
        $downloadPath,
        $current_user,
        $user_email_data,
        $assessment_id
    ) {

        $asseListGroupLeaders = get_transient('assessmentListenerGroupLeaders' . (string) $current_user);

        $user       = get_user_by('id', $current_user);
        $LeaderUser = get_user_by('id', $asseListGroupLeaders);

        //echo '<pre>assessment_id ';print_r($assessment_id);'</pre>';
        //echo '<pre>email ';print_r($user_email_data);'</pre>';exit();

        $first_name = isset($user_email_data['first_name']) ? $user_email_data['first_name'] : 'ttisi';
        $last_name  = isset($user_email_data['last_name']) ? $user_email_data['last_name'] : 'platform';

        $title     = get_the_title($assessment_id);
        $titles    = str_replace(' ', '_', $title);
        $site_name = str_replace(' ', '_', get_bloginfo('name'));

        $subject         = 'Report (' . $first_name . '_' . $last_name . '_' . $titles . '_' . $site_name . ')';
        $attachment_name = $first_name . ' ' . $last_name . ' ' . $title;
        $email           = $user->user_email;
        $display_name    = $user->display_name;
        $site_name       = get_bloginfo('name');
        $to              = $mails_to_sent;
        $from            = 'TTISI Platform';
        $admin_email     = get_option('admin_email');
        $headers         = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
        $headers .= 'From: ' . $site_name . '  <' . $admin_email . '>' . "\r\n";
        $headers .= 'Reply-To: ' . $LeaderUser->display_name . ' <' . $LeaderUser->user_email . '>' . "\r\n";

        //$subject = 'Group User ('.$display_name.') Report  (TTI Platform)';
        $msg = '
        <!DOCTYPE html>
<html>
<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<style>
table {
    font-size: 17px;
  font-family: arial, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

td, th {

  text-align: left;
  padding: 8px;
}


</style>
</head>
<body>


<table>
  <tr  style="background-color: #dddddd;">
    <td>Link Description:</td>
    <td>' . $title . ' (' . $user_email_data['link_id'] . ')</td>

  </tr>
  <tr>
    <td>Respondent Name: </td>
    <td>' . $user_email_data['first_name'] . ' ' . $user_email_data['last_name'] . '</td>

  </tr>
  <tr style="background-color: #dddddd;">
    <td>Respondent E-mail: </td>
    <td>' . $user_email_data['email'] . '</td>

  </tr>
  <tr>
    <td>Respondent Company: </td>
    <td>' . $user_email_data['company'] . '</td>

  </tr>
  <tr style="background-color: #dddddd;">
    <td>Respondent Position: </td>
    <td>' . $user_email_data['position_job'] . '</td>
  </tr>
</table>
<br /><br />
<div style="font-family: Arial, Helvetica, sans-serif;font-size: 19px;">
<strong>ATTACHMENT : ' . $attachment_name . '</strong>
</div>

</body>
</html>
        ';

        $mail_attachment = $downloadPath;

        /* WordPress mail function */
        //$this->error_log->put_error_log($user_email_data, 'array');
        $this->error_log->put_error_log('Group Leader Emails Sent To : ' . json_encode($to));
        $this->error_log->put_error_log('Emails Subject : ' . $subject);
        $re = wp_mail($to, $subject, $msg, $headers, $mail_attachment);

        if ($re) {
            $this->error_log->put_error_log('Email Sent Successfully');
            /* Delete pdf file */
            $this->delete_pdf_file($mail_attachment);
        } else {
            $this->error_log->put_error_log('Email Sent Failed');
        }

    }

    /**
     * Function to assessment shortcode for Frontend.
     *
     * @since   1.0.0
     *
     * @param integer $current_user contains current user id
     * @param string $link_id contains assessment link id
     * @return boolean return true or false
     */
    public function check_user_limit($current_user, $link_id)
    {
        global $wpdb;
        $users_limit = $wpdb->prefix . 'tti_users_limit';
        $results     = $wpdb->get_row("SELECT * FROM $users_limit WHERE user_id ='$current_user' AND data_link = '$link_id'");

        if (isset($results) && !empty($results)) {
            if (isset($results->limits) && $results->limits > 0) {
                return true;
            } elseif (isset($results->limits) && $results->limits <= 0) {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Function to assessment shortcode for Frontend.
     *
     * @since   1.0.0
     *
     * @param array $atts contains shortcode attributes
     * @param string $content contains shortcode content
     * @param string $tag contains shortcode tags
     * @return string returns final assessment shortcode output
     */

    public function assessment_shortcode($atts = [], $content = null, $tag = '')
    {
        global $wpdb, $current_user_info;

        /* Enqueue style and script */
        $this->ttisi_enqueue_styles();

        $atts            = array_change_key_case((array) $atts, CASE_LOWER);
        $assessment_atts = shortcode_atts([
            'assess_id'   => '',
            'button_text' => '',
        ], $atts, $tag);

        if (is_user_logged_in()) {
            $current_user_info = wp_get_current_user();
            $current_user      = $current_user_info->ID;
            $assess_id         = sanitize_text_field($assessment_atts['assess_id']);

            /* Get the current assessment locked status */
            $status_locked = get_post_meta($assess_id, 'status_locked', true);
            /*************************************/

            /* Get the current assessment status */
            $asses_status = get_post_meta($assess_id, 'status_assessment', true);
            /*************************************/

            /**
             * Fires before assessment shortcode called
             *
             * @since   1.2
             */
            do_action('ttisi_platform_before_assessments_shortcode');
            //if($asses_status != 'Suspended' && $status_locked == 'true') {
            if ($asses_status != 'Suspended') {

                $link_id = get_post_meta($assess_id, 'link_id', true);

                /* Get assessment version */
                $asses_version = $this->get_current_user_assess_version($current_user, $link_id);

                $assessment_table_name = $wpdb->prefix . 'assessments';
                $results               = $wpdb->get_row("SELECT * FROM $assessment_table_name WHERE user_id ='$current_user' AND link_id ='$link_id' AND status = 1 AND version = $asses_version");

                /* Chcek user limit */
                $user_limit = $this->check_user_limit($current_user, $link_id);

                if ((isset($results->password) && !empty($results->password)) && (!$user_limit)) {
                    $msg = __('Assessment Completed.', 'tti-platform');
                    $o   = '';
                    $o .= '<div class="assessment_button">';
                    $o .= '<h2>' . esc_html($msg) . '</h2>';
                    $o .= '</div>';
                    /* Show assessment history */
                    $o .= do_shortcode('[tti_assessment_show_user_assessment_history show_as_link="yes" assess_id="' . $assessment_atts['assess_id'] . '"]');
                } else {
                    $retake_ass_att = '';
                    if ($asses_version >= 1 && $user_limit) {
                        $assessment_atts['button_text'] = 'Retake Assessment';
                        $retake_ass_att                 = 'data-retake = "true"';
                    }
                    $o = '';
                    $o .= '<div class="assessment_button">';
                    $o .= '<button id="assessment_button" ' . $retake_ass_att . ' class="closed-assessment" assessment-locked="' . $status_locked . '"  assessment-id="' . esc_attr($assessment_atts['assess_id']) . '" assessment-permalink="' . get_the_permalink() . '">' . esc_html($assessment_atts['button_text']) . '</button><img id="take_loader_front" src="' . plugin_dir_url(__FILE__) . 'images/loader.gif' . '" alt="" />';
                    $o .= '</div>';
                    $o .= '<div class="tti-platform-user-level-loading">
                                <div class="preloader-wrap">
                                    <div id="precent" class="percentage"></div>
                                    <div class="loader">
                                        <div class="trackbar">
                                            <div class="loadbar"></div>
                                        </div>
                                        <p>Scoring Assessment Please Wait</p>
                                    </div>
                                </div>
                            </div>';
                    /* Show assessment history */
                    $o .= do_shortcode('[tti_assessment_show_user_assessment_history show_as_link="yes" assess_id="' . $assessment_atts['assess_id'] . '"]');
                }

            } elseif ($asses_status != 'Suspended' && $status_locked == 'false') {

                if (in_array('tti-platform-application-screening/tti-platform-application-screening.php', apply_filters('active_plugins', get_option('active_plugins')))) {
                    /**
                     * Fires after assessment shortcode called
                     *
                     * @since   1.4.2
                     */
                    do_action('tti_assessment_open_link_take_assessment_btn', $assess_id, $assessment_atts, $status_locked);
                } else {
                    $ass_title = get_the_title($assess_id);

                    $o .= '<div class="assessment_message">';
                    $o .= '<h3>Please install and activate <u>TTI Assessment Application Screening</u> addon to take this assessment (' . esc_html($ass_title) . ').</h3>';
                    $o .= '</div>';
                }

            } else {
                $msg = __('This assessment has been suspended.', 'tti-platform');
                $o   = '';
                $o .= '<div class="assessment_disabled">';
                $o .= '<h2>' . esc_html($msg) . '</h2>';
                $o .= '</div>';
            }
            /**
             * Fires after assessment shortcode called
             *
             * @since   1.2
             */
            do_action('ttisi_platform_after_assessments_shortcode');

        } else {
            $msg = __('You must logged in to take this assessment.', 'tti-platform');
            $o   = '';
            $o .= '<div class="assessment_button">';
            $o .= '<h2>' . esc_html($msg) . '</h2>';
            $o .= '</div>';
        }

        return $o;
    }

    /**
     * Function to init assessment shortcode.
     *
     * @since   1.0.0
     */

    public function assessment_shortcode_init()
    {
        add_shortcode('take_assessment', array($this, 'assessment_shortcode'));
    }

    /**
     * Function to add Function to assessments history shortcode initialization.
     *
     * @since   1.6
     */

    public function assessment_assessment_history()
    {
        add_shortcode('tti_assessment_show_user_assessment_history', array($this, 'assessment_assessment_history_function'));
    }

    /**
     * Function to output assessments history shortcode function.
     *
     * @since   1.6
     * @param array $atts contains shortcode attributes
     * @param string $content contains shortcode content
     * @param string $tag contains shortcode tags
     */

    public function assessment_assessment_history_function($atts = [], $content = null, $tag = '')
    {
        global $current_user;
        wp_get_current_user();

        /**
         * Fires after completed assessment history
         *
         * @since   1.2
         */
        do_action('ttisi_platform_before_assessment_ah_shortcode');

        /* include completed profile class */
        require_once plugin_dir_path(__FILE__) . 'partials/assessment-history/class-tti-platform-assessment-history.php';

        $atts            = array_change_key_case((array) $atts, CASE_LOWER);
        $assessment_atts = shortcode_atts([
            'assess_id'    => '',
            'show_as_link' => 'no',
        ], $atts, $tag);
        $assess_id = $assessment_atts['assess_id'];
        $show_link = $assessment_atts['show_as_link'];
        $link_id   = get_post_meta($assess_id, 'link_id', true);

        /* Initialize assessment history class functionality */
        $assess_history = new TTI_Platform_Assessment_History($current_user->ID, $link_id, $assess_id, $show_link);

        /**
         * Fires after completed assessment history list
         *
         * @since   1.2
         */
        do_action('ttisi_platform_after_assessment_ah_shortcode');

        // start capturing output
        ob_start();
        /* include completed profile class */
        echo $assess_history->show_assessment_history();
        $content = ob_get_contents(); // get the contents from the buffer
        ob_end_clean();

        return $content;
    }

    /**
     * Function to init assessment completed profiles shortcode.
     *
     * @since   1.0.0
     */

    public function assessment_cp_shortcode_init()
    {
        add_shortcode('tti_assessment_show_group_users', array($this, 'assessment_cp_shortcode'));
    }

    /**
     * Function to add completed profile PHP file.
     *
     * @since   1.0.0
     */

    public function assessment_cp_shortcode()
    {

        /**
         * Fires after completed file functionality
         *
         * @since   1.2
         */
        do_action('ttisi_platform_before_assessment_cp_shortcode');

        ob_start(); // start capturing output
        /* include completed profile class */
        require_once plugin_dir_path(__FILE__) . 'partials/completed-profiles/class-tti-platform-completed-profiles.php';
        $content = ob_get_contents(); // get the contents from the buffer
        ob_end_clean(); // stop buffering and discard contents

        /**
         * Fires after completed file functionality
         *
         * @since   1.2
         */
        do_action('ttisi_platform_after_assessment_cp_shortcode');

        return $content;
    }

    /**
     * Function to handle assessment text shortcode for Frontend.
     *
     * @since   1.0.0
     * @param array $atts contains shortcode attributes
     * @param string $content contains shortcode content
     * @param string $tag contains shortcode tag
     * @return string
     */

    public function assessment_text_feedback_shortcode($atts = [], $content = null, $tag = '')
    {
        global $wpdb, $current_usr;

        /* Enqueue style and script */
        $this->ttisi_enqueue_styles();

        /**
         * Fires before assessment feedback shortcode called
         *
         * @since   1.2
         */
        do_action('ttisi_platform_before_assessments_feedback_shortcode');

        $o               = '<div class="ttisi-content-block">';
        $atts            = array_change_key_case((array) $atts, CASE_LOWER);
        $assessment_atts = shortcode_atts([
            'assess_id'   => '',
            'type'        => '',
            'intro'       => '',
            'datalisting' => '',
            'feedback'    => '',
        ], $atts, $tag);
        if (is_user_logged_in()) {
            $current_usr  = wp_get_current_user();
            $current_user = $current_usr->ID;
            $assess_id    = $assessment_atts['assess_id'];

            /* Get the current assessment status */
            $asses_status = get_post_meta($assess_id, 'status_assessment', true);
            /*************************************/

            if ($asses_status != 'Suspended') {

                $type        = $assessment_atts['type'];
                $page_indent = 1;

                /* check for EQTables type */
                if (strpos($type, 'EQTABLES2') !== false) {
                    $eqtype           = $type;
                    $eqtables_section = explode('-', $type);
                    $page_indent      = $eqtables_section[1];
                    $type             = 'EQTABLES2';
                }

                $gen_char_intro        = $assessment_atts['intro'];
                $gen_char_par          = $assessment_atts['datalisting'];
                $gen_char_feedback     = $assessment_atts['feedback'];
                $link_id               = get_post_meta($assess_id, 'link_id', true);
                $assessment_table_name = $wpdb->prefix . 'assessments';
                /* Get assessment version */
                $asses_version = $this->get_current_user_assess_version($current_user, $link_id);

                $results = $wpdb->get_row("SELECT * FROM $assessment_table_name WHERE user_id ='$current_user' AND link_id ='$link_id' AND status = 1 AND version = $asses_version");
                if ($results) {

                    $report_sections         = unserialize($results->assessment_result);
                    $selected_all_that_apply = unserialize($results->selected_all_that_apply);
                    $sections                = $report_sections->report->sections;
                    $assessmenrArr           = array();

                    /**
                     * Filter to update feedback sections of assessments data
                     *
                     * @since  1.2
                     */
                    $sections = apply_filters('ttisi_platform_assessments_feedback_sections', $sections);

                    foreach ($sections as $arrayResponseData) {
                        if ($arrayResponseData->type == $type) {
                            if ($type == 'DOS' || $type == 'DONTS') {
                                $intro      = $arrayResponseData->header->text;
                                $prefix     = $arrayResponseData->prefix;
                                $statements = $arrayResponseData->statements;

                                $assessmenrArr[] = array(
                                    'intro'      => $intro,
                                    'prefix'     => $prefix,
                                    'statements' => $statements,
                                );
                            } else if ($type == 'EQGENCHAR') {
                                $intro      = $arrayResponseData->header->text;
                                $titles     = $arrayResponseData->header->titles;
                                $statements = $arrayResponseData->statement_blocks;

                                $assessmenrArr[] = array(
                                    'intro'      => $intro,
                                    'titles'     => $titles,
                                    'statements' => $statements,
                                );
                            } else if ($type == 'COMMTIPS') {
                                $intro           = $arrayResponseData->header->text;
                                $factors         = $arrayResponseData->factors;
                                $assessmenrArr[] = array(
                                    'intro'   => $intro,
                                    'factors' => $factors,
                                );
                            } else if ($type == 'PERCEPT') {
                                $intro           = $arrayResponseData->header->text;
                                $title           = $arrayResponseData->title;
                                $wordlists       = $arrayResponseData->wordlists;
                                $assessmenrArr[] = array(
                                    'intro'     => $intro,
                                    'title'     => $title,
                                    'wordlists' => $wordlists,
                                );
                            } else if ($type == 'NASTYLE') {
                                $intro           = $arrayResponseData->header->text;
                                $styles          = $arrayResponseData->styles;
                                $assessmenrArr[] = array(
                                    'intro'  => $intro,
                                    'styles' => $styles,
                                );
                            } else if ($type == 'PIAVBARS12HIGH') {
                                $intro           = $arrayResponseData->header->text;
                                $driving_forces  = $arrayResponseData->driving_forces;
                                $assessmenrArr[] = array(
                                    'intro'          => $intro,
                                    'driving_forces' => $driving_forces,
                                );
                            } else if ($type == 'TWASTERS') {
                                $intro           = $arrayResponseData->header->text;
                                $wasters         = $arrayResponseData->wasters;
                                $assessmenrArr[] = array(
                                    'intro'   => $intro,
                                    'wasters' => $wasters,
                                );
                            } else if ($type == 'EQTABLES2') {

                                $pages[]         = $arrayResponseData->$page_indent;
                                $ident_of_eq     = strtolower($pages[0]->title);
                                $intro           = $pages[0]->description;
                                $leading_text    = $pages[0]->leadin;
                                $assessmenrArr[] = array(
                                    'intro'     => $intro,
                                    'lead_text' => $leading_text,
                                    'pages'     => $pages,
                                );

                            } else if ($type == 'EQ_INTRO') {
                                $titles          = $arrayResponseData->header->titles[0] . ' - ' . $arrayResponseData->header->titles[1];
                                $pages[]         = $arrayResponseData->page1;
                                $pages[]         = $arrayResponseData->page2;
                                $assessmenrArr[] = array(
                                    'titles' => $titles,
                                    'pages'  => $pages,
                                );
                            } else if ($type == 'DFSTRWEAK' || $type == 'DFENGSTRESS') {
                                $intro      = $arrayResponseData->header->text;
                                $left_side  = $arrayResponseData->left_side;
                                $right_side = $arrayResponseData->right_side;

                                $assessmenrArr[] = array(
                                    'intro'      => $intro,
                                    'left_side'  => $left_side,
                                    'right_side' => $right_side,
                                );
                            } else if ($type == 'INTEGRATIONINTRO_DF' || $type == 'POTENTIALSTR_DR' || $type == 'POTENTIALCONFLIT_DR' || $type == 'IDEALENVDR' || $type == 'BLENDING_DF_INTRO') {
                                $intro           = $arrayResponseData->header->text;
                                $statements      = $arrayResponseData->statements;
                                $assessmenrArr[] = array(
                                    'intro'      => $intro,
                                    'statements' => $statements,
                                );
                            } else if ($type == 'MOTIVATINGDR' || $type == 'MANAGINGDR') {
                                $intro           = $arrayResponseData->header->text;
                                $prefix          = $arrayResponseData->prefix;
                                $statements      = $arrayResponseData->statements;
                                $assessmenrArr[] = array(
                                    'intro'      => $intro,
                                    'prefix'     => $prefix,
                                    'statements' => $statements,
                                );
                            } else if ($type == 'BLENDINGSADFEQ') {
                                $intro           = $arrayResponseData->header->text;
                                $paragraphs      = $arrayResponseData->paragraphs;
                                $titles          = $arrayResponseData->header->titles[0];
                                $assessmenrArr[] = array(
                                    'intro'      => $intro,
                                    'title'      => $titles,
                                    'paragraphs' => $paragraphs,
                                );
                            } else {
                                $intro           = $arrayResponseData->header->text;
                                $prefix          = $arrayResponseData->prefix;
                                $statements      = $arrayResponseData->statements;
                                $assessmenrArr[] = array(
                                    'intro'      => $intro,
                                    'prefix'     => $prefix,
                                    'statements' => $statements,
                                );
                            }
                        }
                    }
                    if ($gen_char_intro == 'yes') {
                        $o .= '<p>' . esc_html($assessmenrArr[0]['intro']) . '</p>';
                    }

                    if (isset($gen_char_par) && $gen_char_par != '') {
                        $gen_char_parArr = explode(',', $gen_char_par);
                    } else {
                        $gen_char_parArr = array();
                    }

                    $inSelect = false;

                    if ($gen_char_feedback == 'feedback') {
                        if ($type == 'TITLE') {

                        } else if ($type == 'INTRO' || $type == 'TRICOACHINTRO2' || $type == 'ACTION2' || $type == 'INTRO12') {

                            if (is_array($gen_char_parArr) && !empty($gen_char_parArr)) {
                                foreach ($assessmenrArr[0]['statements'] as $key => $value) {
                                    $format = $assessmenrArr[0]['statements'][$key]->format;
                                    $style  = $assessmenrArr[0]['statements'][$key]->style;
                                    $text   = $assessmenrArr[0]['statements'][$key]->text;
                                    if ($format == 'para' && $style == 'left') {
                                        $o .= '<p style="margin-top: 10px;">' . esc_html($text) . '</p>';
                                    } else if ($format == 'list' && $style == 'bullets') {
                                        $o .= '<ul>';
                                        $o .= '<li>' . esc_html($text) . '</li>';
                                        $o .= '</ul>';
                                    }
                                }
                            }
                        } else if ($type == 'COMMTIPS') {
                            if (is_array($gen_char_parArr) && !empty($gen_char_parArr)) {
                                $count = 0;
                                foreach ($gen_char_parArr as $key => $value) {
                                    if (array_key_exists($value, $assessmenrArr[0]['factors'])) {
                                        $factorStatements = $assessmenrArr[0]['factors'][$value]->statements;
                                        $o .= '<div>';
                                        foreach ($factorStatements as $key => $value) {
                                            $format  = $value->format;
                                            $style   = $value->style;
                                            $subhead = $value->subhead;
                                            $stmts   = $value->stmts;
                                            if ($format == 'list' && $style == 'bullets') {
                                                $o .= '<h4>' . esc_html($subhead) . '</h4>';
                                                $o .= '<ul>';
                                                foreach ($stmts as $key => $value) {
                                                    $o .= '<li>' . esc_html($value) . '</li>';
                                                }
                                                $o .= '</ul>';
                                            }
                                        }
                                        $o .= '</div>';
                                    }
                                    $count++;
                                    if ($count % 2 == 0) {
                                        $o .= '';
                                    }
                                }
                            }
                        } else if ($type == 'EQGENCHAR') {

                            if (is_array($gen_char_parArr) && !empty($gen_char_parArr)) {
                                foreach ($gen_char_parArr as $key => $value) {
                                    if (array_key_exists($value, $assessmenrArr[0]['statements'])) {

                                        $o .= '<div>';
                                        $o .= '<p>' . esc_html($checklist);
                                        foreach ($assessmenrArr[0]['statements'][$value]->statements as $index => $checklist) {
                                            $o .= esc_html($checklist);
                                        }
                                        $o .= '</p>';

                                        $o .= '</div>';
                                    }
                                }
                            } else {

                            }
                        } else if ($type == 'BLENDINGSADFEQ') {

                            if (is_array($gen_char_parArr) && !empty($gen_char_parArr)) {

                                foreach ($gen_char_parArr as $key => $value) {
                                    if (array_key_exists($value, $assessmenrArr[0]['paragraphs'])) {

                                        $o .= '<div>';
                                        foreach ($assessmenrArr[0]['paragraphs'] as $index => $para) {
                                            $o .= '<p>';
                                            $o .= esc_html($para);
                                            $o .= '</p>';
                                        }
                                        $o .= '</div>';
                                    }
                                }
                            }
                        } else if ($type == 'PERCEPT') {
                            if (is_array($gen_char_parArr) && !empty($gen_char_parArr)) {
                                foreach ($gen_char_parArr as $key => $value) {
                                    if (array_key_exists($value, $assessmenrArr[0]['wordlists'])) {
                                        $title  = $assessmenrArr[0]['wordlists'][$value]->title;
                                        $prefix = $assessmenrArr[0]['wordlists'][$value]->prefix;
                                        $words  = $assessmenrArr[0]['wordlists'][$value]->words;
                                        $o .= '<h3 style="margin-bottom:0;">' . esc_html($title) . '</h3><strong><em>' . esc_html($prefix) . '</em></strong><br><br>';
                                        $o .= '<ul>';
                                        foreach ($words as $key => $value) {
                                            $o .= '<li>' . esc_html($value) . '</li>';
                                        }
                                        $o .= '</ul>';
                                    }
                                }
                            }
                        } else if ($type == 'NASTYLE') {
                            if (is_array($gen_char_parArr) && !empty($gen_char_parArr)) {
                                foreach ($gen_char_parArr as $key => $value) {
                                    if (array_key_exists($value, $assessmenrArr[0]['styles'])) {
                                        $stylesTitle            = $assessmenrArr[0]['styles'][$value]->title;
                                        $naturalStatements      = $assessmenrArr[0]['styles'][$value]->natural->statements;
                                        $naturalStatementsTitle = $assessmenrArr[0]['styles'][$value]->natural->ident;
                                        $adaptedStatements      = $assessmenrArr[0]['styles'][$value]->adapted->statements;
                                        $adaptedStatementsTitle = $assessmenrArr[0]['styles'][$value]->adapted->ident;
                                        $o .= '<div style="width: 100%; float: left; margin: 0;padding: 0; box-sizing: border-box;">'
                                        . '<h3 style="margin: 0">' . esc_html($stylesTitle) . '</h3>'
                                        . '<div style="width: 48%;float: left; border-right: 1px solid #ccc;padding: 0 15px;box-sizing: border-box;">'
                                        . '<h4 style="margin:0;">' . esc_html(ucfirst($naturalStatementsTitle)) . '</h4>'
                                            . '<p>';
                                        foreach ($naturalStatements as $key => $value) {
                                            $o .= $value;
                                        }

                                        $o .= '</p>'
                                        . '</div>'
                                        . '<div style="width: 48%;float: left;padding: 0 15px;box-sizing: border-box;">'
                                        . '<h4 style="margin:0;">' . esc_html(ucfirst($adaptedStatementsTitle)) . '</h4>'
                                            . '<p>';
                                        foreach ($adaptedStatements as $key => $value) {
                                            $o .= $value;
                                        }
                                        $o .= '</p>'
                                            . '</div>'
                                            . '</div>';
                                        $o .= '';
                                    }
                                }
                            }
                        } else if ($type == 'PIAVBARS12HIGH') {

                        } else if ($type == 'EQ_INTRO') {

                            if (is_array($gen_char_parArr) && !empty($gen_char_parArr)) {
                                $pages = $assessmenrArr[0]['pages'];
                                $o .= '<h3>' . esc_html($assessmenrArr[0]['titles']) . '</h3>';
                                foreach ($gen_char_parArr as $key => $value0) {

                                    foreach ($pages as $key => $value) {
                                        if ($value0 == $key) {
                                            foreach ($value as $key => $value1) {
                                                foreach ($value1 as $key => $value2) {
                                                    $format = $value2->format;
                                                    $style  = $value2->style;
                                                    $text   = $value2->text;
                                                    if ($format == 'para' && $style == 'left' && $text != '$space') {
                                                        $o .= '<p><strong><em>' . esc_html($text) . '</em></strong></p>';
                                                    }
                                                }
                                            }
                                        }
                                    }

                                }
                            }
                        } else if ($type == 'BEHAVIOR_AVOIDANCE') {
                            if (is_array($gen_char_parArr) && !empty($gen_char_parArr)) {
                                foreach ($gen_char_parArr as $key => $value) {
                                    if (array_key_exists($value, $assessmenrArr[0]['statements'])) {
                                        foreach ($assessmenrArr[0]['statements'] as $key => $value) {
                                            $format = $value->format;
                                            $style  = $value->style;
                                            $text   = $value->text;
                                            if ($format == 'para' && $style == 'left') {
                                                $o .= '<p><strong><em>' . esc_html($text) . '</em></strong></p>';
                                            } else if ($format == 'list' && $style == 'bullets') {
                                                $o .= '<ul>';
                                                $o .= '<li>' . esc_html($text) . '</li>';
                                                $o .= '</ul>';
                                            }
                                        }
                                    }
                                }
                            }
                        } else if ($type == 'TWASTERS') {
                            if (is_array($gen_char_parArr) && !empty($gen_char_parArr)) {
                                foreach ($gen_char_parArr as $key => $value) {
                                    if (array_key_exists($value, $assessmenrArr[0]['wasters'])) {
                                        foreach ($assessmenrArr[0]['wasters'] as $key => $value) {
                                            $title       = $value->title;
                                            $description = $value->description;
                                            $statements  = $value->statements;
                                            $o .= '<h3>' . esc_html($title) . '</h3>';
                                            $o .= '<p>' . esc_html($description) . '</p>';
                                            foreach ($statements as $key => $value) {
                                                $format  = $value->format;
                                                $style   = $value->style;
                                                $ident   = $value->ident;
                                                $subhead = $value->subhead;
                                                $stmts   = $value->stmts;
                                                $o .= '<h5>' . esc_html($subhead) . '</h5>';
                                                $o .= '<ul>';
                                                foreach ($stmts as $key => $value) {
                                                    $o .= '<li>' . esc_html($value) . '</li>';
                                                }
                                                $o .= '</ul>';
                                            }

                                        }
                                    }
                                }
                            }
                        } else if ($type == 'DFSTRWEAK' || $type == 'DFENGSTRESS') {
                            if (is_array($gen_char_parArr) && !empty($gen_char_parArr)) {
                                foreach ($gen_char_parArr as $key => $value) {
                                    if ($value == 0) {
                                        $title = $assessmenrArr[0]['left_side']->title;
                                        $o .= '<h5>' . esc_html($title) . '</h5>';
                                        $o .= '<ul>';
                                        foreach ($assessmenrArr[0]['left_side']->statements as $key => $value) {
                                            $o .= '<li>' . esc_html($value) . '</li>';
                                        }
                                        $o .= '</ul>';
                                    }
                                    if ($value == 1) {
                                        $title = $assessmenrArr[0]['right_side']->title;
                                        $o .= '<h5>' . esc_html($title) . '</h5>';
                                        $o .= '<ul>';
                                        foreach ($assessmenrArr[0]['right_side']->statements as $key => $value) {
                                            $o .= '<li>' . esc_html($value) . '</li>';
                                        }
                                        $o .= '</ul>';
                                    }
                                }
                            }
                        } else if ($type == 'INTEGRATIONINTRO_DF' || $type == 'BLENDING_DF_INTRO') {
                            if (is_array($gen_char_parArr) && !empty($gen_char_parArr)) {
                                foreach ($gen_char_parArr as $key => $value) {
                                    if (array_key_exists($value, $assessmenrArr[0]['statements'])) {
                                        foreach ($assessmenrArr[0]['statements'] as $key => $value) {
                                            $format = $value->format;
                                            $style  = $value->style;
                                            $text   = $value->text;
                                            if ($format == 'para' && $style == 'left') {
                                                $o .= '<p><strong><em>' . esc_html($text) . '</em></strong></p>';
                                            } else if ($format == 'list' && $style == 'bullets') {
                                                $o .= '<ul>';
                                                $o .= '<li>' . esc_html($text) . '</li>';
                                                $o .= '</ul>';
                                            }
                                        }
                                    }
                                }
                            }
                        } else if ($type == 'POTENTIALSTR_DR' || $type == 'POTENTIALCONFLIT_DR' || $type == 'IDEALENVDR') {
                            if (is_array($gen_char_parArr) && !empty($gen_char_parArr)) {
                                foreach ($gen_char_parArr as $key => $value) {
                                    if (array_key_exists($value, $assessmenrArr[0]['statements'])) {
                                        foreach ($assessmenrArr[0]['statements'] as $key => $value) {
                                            $stmts = $value->stmts;
                                            $o .= '<ul>';
                                            foreach ($stmts as $key => $value) {
                                                $o .= '<li>' . esc_html($value) . '</li>';
                                            }
                                            $o .= '</ul>';
                                        }
                                    }
                                }
                            }
                        } else if ($type == 'MOTIVATINGDR' || $type == 'MANAGINGDR') {
                            if (is_array($gen_char_parArr) && !empty($gen_char_parArr)) {
                                foreach ($gen_char_parArr as $key => $value) {
                                    if (array_key_exists($value, $assessmenrArr[0]['statements'])) {
                                        $prefix = $assessmenrArr[0]['prefix'];
                                        if (isset($prefix) && !empty($prefix)) {
                                            $o .= '<h3>' . esc_html($prefix) . '</h3>';
                                        }
                                        foreach ($assessmenrArr[0]['statements'] as $key => $value) {
                                            $stmts = $value->stmts;
                                            $o .= '<ul>';
                                            foreach ($stmts as $key => $value) {
                                                $o .= '<li>' . esc_html($value) . '</li>';
                                            }
                                            $o .= '</ul>';
                                        }
                                    }
                                }
                            }
                        } elseif ($type == 'EQTABLES2') {

                            if (is_array($gen_char_parArr) && count($gen_char_parArr) > 0) {
                                $pages = $assessmenrArr[0]['pages'];
                                foreach ($pages as $key => $value) {
                                    $title      = $value->title;
                                    $lead_text  = $value->leadin;
                                    $statements = $value->bullets;
                                    $o .= '<h4>' . $lead_text . '</h4>';
                                    foreach ($statements as $key => $value) {
                                        $o .= '<li>' . esc_html($value) . '</li>';
                                    }
                                    $o .= '</ul>';
                                }

                            }
                        } else {
                            /* $type == 'VAL' */
                            if (is_array($gen_char_parArr) && !empty($gen_char_parArr)) {
                                foreach ($gen_char_parArr as $key => $value) {
                                    if (array_key_exists($value, $assessmenrArr[0]['statements'])) {
                                        $format = $assessmenrArr[0]['statements'][$value]->format;
                                        $style  = $assessmenrArr[0]['statements'][$value]->style;
                                        if ($format == 'para' && $style == 'indent') {
                                            $o .= '<p>' . implode(" ", $assessmenrArr[0]['statements'][$value]->stmts) . '</p>';
                                        } else if ($format == 'list' && $style == 'bullets') {
                                            $prefix = $assessmenrArr[0]['prefix'];
                                            if (isset($prefix) && !empty($prefix)) {
                                                $o .= '<h3>' . esc_html($prefix) . '</h3>';
                                            }
                                            $o .= '<ul>';
                                            foreach ($assessmenrArr[0]['statements'][$value]->stmts as $index => $checklist) {
                                                $o .= '<li>' . esc_html($checklist) . '</li>';
                                            }
                                            $o .= '</ul>';
                                        } else if ($format == 'list') {
                                            $o .= '<ul>';
                                            foreach ($assessmenrArr[0]['statements'][$value]->stmts as $index => $checklist) {
                                                $o .= '<li>' . esc_html($checklist) . '</li>';
                                            }
                                            $o .= '</ul>';
                                        }
                                    }
                                }
                            } else {

                            }
                        }
                    } elseif ($gen_char_feedback == 'select') {
                        if ($type == 'GENCHAR' || $type == 'MOTGENCHAR') {
                            if (!empty($gen_char_parArr)) {
                                $getBool = false;
                                $o .= '<div class="selectFeedbackData" id="' . $type . '">';
                                if (is_array($selected_all_that_apply) || is_object($selected_all_that_apply)) {
                                    foreach ($selected_all_that_apply as $key => $value) {
                                        if ($value['type'] == $type) {
                                            foreach ($value['statements'] as $key => $value) {
                                                $ident = $value['ident'];
                                                if (!in_array($key, $gen_char_parArr)) {
                                                    $o .= '<ul id="' . esc_attr($ident) . '" count="' . esc_attr($key) . '"></ul>';
                                                    continue;
                                                }
                                                $o .= '<ul id="' . esc_attr($ident) . '" count="' . esc_attr($key) . '">';
                                                foreach ($value['stmts'] as $key => $value) {
                                                    $text  = $value['text'];
                                                    $value = $value['value'];
                                                    if ($value == 1) {
                                                        $checked = 'checked= checked';
                                                    } else {
                                                        $checked = '';
                                                    }
                                                    $randstr = $this->generateRandomString();
                                                    $o .= '<input type="checkbox" name="' . esc_attr($ident) . '[]" id="isSelected-' . esc_attr($randstr) . '" value="" text="' . str_replace('"', '&quot;', stripslashes($text)) . '" ident="' . esc_attr($ident) . '" ' . esc_attr($checked) . ' /> <label for="isSelected-' . esc_attr($randstr) . '"><span>' . stripslashes($text) . '</span></label>';
                                                }
                                                $o .= '</ul>';
                                            }
                                            $getBool  = true;
                                            $inSelect = true;
                                        }
                                    }}
                                if (!$getBool) {
                                    if (isset($assessmenrArr[0]['statements'])) {
                                        foreach ($assessmenrArr[0]['statements'] as $key => $value) {
                                            $format = $value->format;
                                            $style  = $value->style;
                                            $ident  = $value->ident;
                                            if (!in_array($key, $gen_char_parArr)) {
                                                $o .= '<ul id="' . esc_attr($ident) . '" count="' . esc_attr($key) . '"></ul>';
                                                continue;
                                            }
                                            $o .= '<ul id="' . esc_attr($ident) . '" count="' . esc_attr($key) . '">';
                                            if ($format == 'para' && $style == 'indent') {
                                                foreach ($value->stmts as $index => $checklist) {
                                                    $randstr = $this->generateRandomString();
                                                    $o .= '<input type="checkbox" name="' . esc_attr($ident) . '[]" id="isSelected-' . esc_attr($randstr) . '" value="" text="' . str_replace('"', '&quot;', stripslashes($checklist)) . '" ident="' . esc_attr($ident) . '" /> <label for="isSelected-' . esc_attr($randstr) . '"><span>' . stripslashes($checklist) . '</span></label>';
                                                }
                                                $inSelect = true;
                                            }
                                            $o .= '</ul>';
                                        }
                                    }
                                }
                                $o .= '</div>';
                                if ($inSelect) {
                                    //$o .= '<br><button id="isSelected" link_id="'.esc_attr($link_id).'">Submit</button><br><div id="responseIsSelected"></div>';
                                    $o .= '<br><button id="isSelected" link_id="' . esc_attr($link_id) . '" data-type="' . esc_attr($type) . '" class="isSelected ' . esc_attr($type) . '-subbtn" >Submit</button><br><div id="responseIsSelected"></div>';
                                }
                            }
                        } else if ($type == 'POTENTIALSTR_DR' || $type == 'POTENTIALCONFLIT_DR' || $type == 'IDEALENVDR' || $type == 'MOTIVATINGDR' || $type == 'MANAGINGDR') {
                            if (!empty($gen_char_parArr)) {
                                $getBool = false;
                                $o .= '<div class="selectFeedbackData" id="' . $type . '">';
                                if (is_array($selected_all_that_apply) || is_object($selected_all_that_apply)) {
                                    foreach ($selected_all_that_apply as $key => $value) {
                                        if ($value['type'] == $type) {
                                            foreach ($value['statements'] as $key => $value) {
                                                $ident = $value['ident'];
                                                $o .= '<ul id="' . esc_attr($ident) . '" count="' . esc_attr($key) . '">';
                                                foreach ($value['stmts'] as $key => $value) {
                                                    $text  = $value['text'];
                                                    $value = $value['value'];
                                                    if ($value == 1) {
                                                        $checked = 'checked= checked';
                                                    } else {
                                                        $checked = '';
                                                    }
                                                    $randstr = $this->generateRandomString();
                                                    $o .= '<input type="checkbox" name="' . esc_attr($ident) . '[]" id="isSelected-' . esc_attr($randstr) . '" value="" text="' . str_replace('"', '&quot;', stripslashes($text)) . '" ident="' . esc_attr($ident) . '" ' . esc_attr($checked) . ' /> <label for="isSelected-' . esc_attr($randstr) . '"><span>' . stripslashes($text) . '</span></label>';
                                                }
                                                $o .= '</ul>';
                                            }
                                            $getBool  = true;
                                            $inSelect = true;
                                        }
                                    }}
                                if (!$getBool) {
                                    if (isset($assessmenrArr[0]['statements'])) {
                                        foreach ($assessmenrArr[0]['statements'] as $key => $value) {
                                            $format = $value->format;
                                            $style  = $value->style;
                                            $ident  = $value->ident;
                                            $o .= '<ul id="' . esc_attr($ident) . '" count="' . esc_attr($key) . '">';
                                            foreach ($value->stmts as $index => $checklist) {
                                                $randstr = $this->generateRandomString();
                                                $o .= '<input type="checkbox" name="' . esc_attr($ident) . '[]" id="isSelected-' . esc_attr($randstr) . '" value="" text="' . str_replace('"', '&quot;', stripslashes($checklist)) . '" ident="' . esc_attr($ident) . '" /> <label for="isSelected-' . esc_attr($randstr) . '"><span>' . stripslashes($checklist) . '</span></label>';
                                            }
                                            $inSelect = true;
                                            $o .= '</ul>';
                                        }
                                    }
                                }
                                $o .= '</div>';
                                if ($inSelect) {
                                    // $o .= '<br><button id="isSelected" link_id="'.esc_attr($link_id).'">Submit</button><br><div id="responseIsSelected"></div>';
                                    $o .= '<br><button id="isSelected" link_id="' . esc_attr($link_id) . '" data-type="' . esc_attr($type) . '" class="isSelected ' . esc_attr($type) . '-subbtn" >Submit</button><br><div id="responseIsSelected"></div>';
                                }
                            }
                        } else if ($type == 'EQTABLES2') {
                            /* EQTABLES2 Select */

                            if (!empty($gen_char_parArr)) {
                                $getBool   = false;
                                $getBoolEQ = false;
                                $inSelect  = false;
                                $o .= '<div class="selectFeedbackData" id="' . $eqtype . '">';

                                if (is_array($selected_all_that_apply) || is_object($selected_all_that_apply)) {

                                    foreach ($selected_all_that_apply as $key => $value) {

                                        if ($value['type'] == $eqtype) {

                                            foreach ($value['statements'] as $key => $value) {
                                                $ident = $value['ident'];

                                                $o .= '<ul id="' . esc_attr($ident) . '" count="' . esc_attr($key) . '">';

                                                if (isset($value['stmts'])) {
                                                    foreach ($value['stmts'] as $key => $value) {

                                                        $text  = $value['text'];
                                                        $value = $value['value'];
                                                        if ($value == 1) {
                                                            $checked = 'checked= checked';
                                                        } else {
                                                            $checked = '';
                                                        }
                                                        $randstr = $this->generateRandomString();
                                                        $o .= '<input type="checkbox" name="' . esc_attr($ident) . '[]" id="isSelected-' . esc_attr($randstr) . '" value="" text="' . str_replace('"', '&quot;', stripslashes($text)) . '" ident="' . esc_attr($ident) . '" ' . esc_attr($checked) . ' /> <label for="isSelected-' . esc_attr($randstr) . '"><span>' . stripslashes($text) . '</span></label>';

                                                    }
                                                }
                                                $o .= '</ul>';

                                            }
                                            $getBool  = true;
                                            $inSelect = true;
                                        }
                                    }
                                }

                                if (!$getBool) {
                                    $count = 0;
                                    foreach ($gen_char_parArr as $key => $value) {

                                        $ident = str_replace(' ', '-', strtolower($assessmenrArr[0]['pages'][0]->title));

                                        $o .= '<ul id="' . esc_attr($eqtype) . '" count="' . esc_attr($value) . '">';

                                        foreach ($assessmenrArr[0]['pages'][0]->bullets as $index => $checklist) {
                                            $ident   = str_replace(' ', '-', strtolower($assessmenrArr[0]['pages'][0]->title));
                                            $randstr = $this->generateRandomString();

                                            $o .= '<input type="checkbox" name="' . esc_attr($ident) . '[]" id="isSelected-' . esc_attr($randstr) . '" value="" text="' . str_replace('"', '&quot;', esc_attr($checklist)) . '" ident="' . esc_attr($ident) . '" /> <label for="isSelected-' . esc_attr($randstr) . '"><span>' . esc_html($checklist) . '</span></label>';

                                            $inSelect = true;
                                        }
                                        $o .= '</ul>';
                                        $count++;
                                    }

                                }
                                $o .= '</div>';
                                if ($inSelect) {
                                    $o .= '<br><button id="isSelected" link_id="' . esc_attr($link_id) . '" data-type="' . esc_attr($type) . '" class="isSelected ' . esc_attr($type) . '-subbtn" >Submit</button><br><div id="responseIsSelected"></div>';
                                    //$o .= '<br><button id="isSelected" link_id="'.esc_attr($link_id).'">Submit</button><br><div id="responseIsSelected"></div>';
                                }
                            }
                        } else if ($type == 'EQGENCHAR') {
                            /* EQGENCHAR Select */
                            if (!empty($gen_char_parArr)) {
                                $getBool  = false;
                                $inSelect = false;
                                $o .= '<div class="selectFeedbackData" id="' . $type . '">';

                                if (is_array($selected_all_that_apply) || is_object($selected_all_that_apply)) {
                                    foreach ($selected_all_that_apply as $key => $value) {
                                        if ($value['type'] == $type) {
                                            foreach ($value['statements'] as $key => $value) {
                                                $ident = $value['ident'];
                                                $o .= '<ul id="' . esc_attr($ident) . '" count="' . esc_attr($key) . '">';
                                                if (isset($value['stmts'])) {
                                                    foreach ($value['stmts'] as $key => $value) {

                                                        $text  = $value['text'];
                                                        $value = $value['value'];
                                                        if ($value == 1) {
                                                            $checked = 'checked= checked';
                                                        } else {
                                                            $checked = '';
                                                        }
                                                        $randstr = $this->generateRandomString();
                                                        $o .= '<input type="checkbox" name="' . esc_attr($ident) . '[]" id="isSelected-' . esc_attr($randstr) . '" value="" text="' . str_replace('"', '&quot;', stripslashes($text)) . '" ident="' . esc_attr($ident) . '" ' . esc_attr($checked) . ' /> <label for="isSelected-' . esc_attr($randstr) . '"><span>' . stripslashes($text) . '</span></label>';
                                                    }
                                                }
                                                $o .= '</ul>';
                                            }
                                            $getBool  = true;
                                            $inSelect = true;
                                        }
                                    }
                                }
                                if (!$getBool) {

                                    foreach ($gen_char_parArr as $key => $value) {
                                        if (array_key_exists($value, $assessmenrArr[0]['statements'])) {

                                            $format = $value->format;
                                            $style  = $value->style;
                                            $ident  = $value->id;

                                            $o .= '<ul id="' . esc_attr($ident) . '" count="' . esc_attr($value) . '">';

                                            $ident = $assessmenrArr[0]['statements'][$value]->title;
                                            foreach ($assessmenrArr[0]['statements'][$value]->statements as $index => $checklist) {
                                                $randstr = $this->generateRandomString();
                                                $o .= '<input type="checkbox" name="' . esc_attr($ident) . '[]" id="isSelected-' . esc_attr($randstr) . '" value="" text="' . str_replace('"', '&quot;', esc_attr($checklist)) . '" ident="' . esc_attr($ident) . '" /> <label for="isSelected-' . esc_attr($randstr) . '"><span>' . esc_html($checklist) . '</span></label>';

                                                $inSelect = true;
                                            }
                                            $o .= '</ul>';

                                        }
                                    }
                                }
                                $o .= '</div>';
                                if ($inSelect) {
                                    //$o .= '<br><button id="isSelected" link_id="'.esc_attr($link_id).'">Submit</button><br><div id="responseIsSelected"></div>';
                                    $o .= '<br><button id="isSelected" link_id="' . esc_attr($link_id) . '" data-type="' . esc_attr($type) . '" class="isSelected ' . esc_attr($type) . '-subbtn" >Submit</button><br><div id="responseIsSelected"></div>';
                                }
                            }
                        } else {
                            if (!empty($gen_char_parArr)) {

                                $getBool = false;

                                $o .= '<div class="selectFeedbackData" id="' . $type . '">';
                                if (is_array($selected_all_that_apply) || is_object($selected_all_that_apply)) {
                                    foreach ($selected_all_that_apply as $key => $value) {
                                        if ($value['type'] == $type) {
                                            foreach ($value['statements'] as $key => $value) {
                                                $ident = $value['ident'];
                                                if (!in_array($key, $gen_char_parArr)) {
                                                    $o .= '<ul id="' . esc_attr($ident) . '" count="' . esc_attr($key) . '"></ul>';
                                                    continue;
                                                }
                                                $o .= '<ul id="' . esc_attr($ident) . '" count="' . esc_attr($key) . '">';
                                                foreach ($value['stmts'] as $key => $value) {
                                                    $text  = $value['text'];
                                                    $value = $value['value'];
                                                    if ($value == 1) {
                                                        $checked = 'checked= checked';
                                                    } else {
                                                        $checked = '';
                                                    }
                                                    $randstr = $this->generateRandomString();
                                                    $o .= '<input type="checkbox" name="' . esc_attr($ident) . '[]" id="isSelected-' . esc_attr($randstr) . '" value="" text="' . str_replace('"', '&quot;', stripslashes($text)) . '" ident="' . esc_attr($ident) . '" ' . esc_attr($checked) . ' /> <label for="isSelected-' . esc_attr($randstr) . '"><span>' . stripslashes($text) . '</span></label>';
                                                }
                                                $o .= '</ul>';
                                            }
                                            $getBool  = true;
                                            $inSelect = true;
                                        }
                                    }
                                }
                                if (!$getBool) {
                                    foreach ($gen_char_parArr as $key => $value) {
                                        if (array_key_exists($value, $assessmenrArr[0]['statements'])) {
                                            $format = $assessmenrArr[0]['statements'][$value]->format;
                                            $style  = $assessmenrArr[0]['statements'][$value]->style;
                                            $ident  = $assessmenrArr[0]['statements'][$value]->ident;
                                            $o .= '<ul id="' . esc_attr($ident) . '" count="' . esc_attr($value) . '">';
                                            if ($format == 'para' && $style == 'indent') {
                                                foreach ($assessmenrArr[0]['statements'][$value]->stmts as $index => $checklist) {
                                                    $randstr = $this->generateRandomString();
                                                    $o .= '<input type="checkbox" name="' . esc_attr($ident) . '[]" id="isSelected-' . esc_attr($randstr) . '" value="" text="' .str_replace('"', '&quot;',  esc_attr($checklist)) . '" ident="' . esc_attr($ident) . '" /> <label for="isSelected-' . esc_attr($randstr) . '"><span>' . esc_html($checklist) . '</span></label>';
                                                }
                                                $inSelect = true;
                                            } else if ($format == 'list' && $style == 'bullets') {
                                                foreach ($assessmenrArr[0]['statements'][$value]->stmts as $index => $checklist) {
                                                    $randstr = $this->generateRandomString();
                                                    $o .= '<input type="checkbox" name="' . esc_attr($ident) . '[]" id="isSelected-' . esc_attr($randstr) . '" value="" text="' . str_replace('"', '&quot;', esc_attr($checklist)) . '" ident="' . esc_attr($ident) . '" /> <label for="isSelected-' . esc_attr($randstr) . '"><span>' . esc_html($checklist) . '</span></label>';
                                                }
                                                $inSelect = true;
                                            } else if ($format == 'list') {
                                                foreach ($assessmenrArr[0]['statements'][$value]->stmts as $index => $checklist) {
                                                    $randstr = $this->generateRandomString();
                                                    $o .= '<input type="checkbox" name="' . esc_attr($ident) . '[]" id="isSelected-' . esc_attr($randstr) . '" value="" text="' . str_replace('"', '&quot;', esc_attr($checklist)) . '" ident="' . esc_attr($ident) . '" /> <label for="isSelected-' . esc_attr($randstr) . '"><span>' . esc_html($checklist) . '</span></label>';
                                                }
                                                $inSelect = true;
                                            }
                                            $o .= '</ul>';
                                        }
                                    }
                                }

                                $o .= '</div>';
                                if ($inSelect) {
                                    // $o .= '<br><button id="isSelected" link_id="'.esc_attr($link_id).'">Submit</button><br><div id="responseIsSelected"></div>';
                                    $o .= '<br><button id="isSelected" link_id="' . esc_attr($link_id) . '" data-type="' . esc_attr($type) . '" class="isSelected ' . esc_attr($type) . '-subbtn" >Submit</button><br><div id="responseIsSelected"></div>';
                                }
                            }
                        }
                        $o .= '<div class="getSelectArr">';
                        foreach ($gen_char_parArr as $key => $value) {
                            $o .= '<input type="hidden" value="' . esc_attr($value) . '" />';
                        }
                        $o .= '</div>';
                    } else if ($gen_char_feedback == 'display') {

                        if ($type == 'POTENTIALSTR_DR' || $type == 'POTENTIALCONFLIT_DR' || $type == 'IDEALENVDR' || $type == 'MOTIVATINGDR' || $type == 'MANAGINGDR') {
                            $o .= '<div class="selectFeedbackData">';
                            if (is_array($selected_all_that_apply) || is_object($selected_all_that_apply)) {
                                foreach ($selected_all_that_apply as $key => $value) {
                                    if ($value['type'] == $type) {
                                        foreach ($value['statements'] as $key => $value) {
                                            $o .= '<ul>';
                                            if (isset($value['stmts'])) {
                                                foreach ($value['stmts'] as $key => $value) {
                                                    $text  = $value['text'];
                                                    $value = $value['value'];
                                                    if ($value == 1) {
                                                        $o .= '<li>' . stripslashes($text) . '</li>';
                                                    }
                                                }
                                            }
                                            $o .= '</ul>';
                                        }
                                    }
                                }
                            }
                            $o .= '</div>';
                        } elseif ($type == 'EQGENCHAR') {

                            $o .= '<div class="selectFeedbackData">';
                            if (is_array($selected_all_that_apply) || is_object($selected_all_that_apply)) {
                                foreach ($selected_all_that_apply as $key => $value) {
                                    if ($value['type'] == $type) {
                                        foreach ($value['statements'] as $key => $value) {
                                            if (!in_array($key, $gen_char_parArr)) {
                                                $o .= '<ul></ul>';
                                                continue;
                                            }

                                            $o .= '<ul>';

                                            if (isset($value['stmts'])) {
                                                foreach ($value['stmts'] as $key => $value) {
                                                    $text  = $value['text'];
                                                    $value = $value['value'];
                                                    if ($value == 1) {

                                                        $o .= '<li>' . stripslashes($text) . '</li>';
                                                    }

                                                }
                                            }
                                            $o .= '</ul>';
                                        }
                                    }
                                }
                            }
                            $o .= '</div>';
                        } else {

                            $o .= '<div class="selectFeedbackData">';
                            if (is_array($selected_all_that_apply) || is_object($selected_all_that_apply)) {
                                foreach ($selected_all_that_apply as $key => $value) {
                                    if ($value['type'] == $type || $eqtype == $value['type']) {
                                        foreach ($value['statements'] as $key => $value) {

                                            if (!in_array($key, $gen_char_parArr) && $type != 'EQTABLES2') {
                                                $o .= '<ul></ul>';
                                                continue;
                                            }

                                            $o .= '<ul>';
                                            if (isset($value['stmts'])) {

                                                foreach ($value['stmts'] as $key => $value) {
                                                    $text  = $value['text'];
                                                    $value = $value['value'];
                                                    if ($value == 1) {
                                                        $o .= '<li>' . stripslashes($text) . '</li>';
                                                    }
                                                }
                                            }
                                            $o .= '</ul>';
                                        }
                                    }
                                }
                            }
                            $o .= '</div>';
                        }
                    }
                } else {
                    $msg = __('No feedback found. Please contact to the administrator of this site.', 'tti-platform');
                    $o .= '<h2>' . esc_html($msg) . '</h2>';
                }
            } else {
                $msg = __('This assessment has been suspended.', 'tti-platform');
                $o .= '<h2>' . esc_html($msg) . '</h2>';
            }
        } else {

        }

        /**
         * Filter to update feedback assessment array
         *
         * @since  1.2
         */
        $assessmenrArr = apply_filters('ttisi_platform_assessments_feedback_final_array', $assessmenrArr);

        /**
         * Filter to update feedback final string
         *
         * @since  1.2
         */
        $o = apply_filters('ttisi_platform_assessments_feedback_final_string', $o);

        /**
         * Fires after assessment feedback shortcode called
         *
         * @since   1.2
         */
        do_action('ttisi_platform_after_assessments_feedback_shortcode', $assessmenrArr, $o);
        $o .= '</div>';
        return $o;
    }

    /**
     * Function to add init feedback shortcode.
     */
    public function assessment_text_feedback_init()
    {
        add_shortcode('assessment_text_feedback', array($this, 'assessment_text_feedback_shortcode'));
    }

    /**
     * Function to save selected feedback.
     * @since   1.0.0
     * @access  public
     */

    public function insertIsSelectedData()
    {
        global $current_user, $wpdb;
        $isSelected = $_POST['isSelected'];

        $link_id = sanitize_text_field($_POST['link_id']);
        wp_get_current_user();
        $usrID = $current_user->ID;

        $assessment_table_name = $wpdb->prefix . 'assessments';

        /* Get assessment version */
        $asses_version = $this->get_current_user_assess_version($usrID, $link_id);

        // $results = $wpdb->get_row("SELECT * FROM $assessment_table_name WHERE user_id ='$usrID' AND version = $asses_version");

        $results = $wpdb->get_row("SELECT * FROM $assessment_table_name WHERE user_id ='$usrID' AND version = $asses_version AND link_id = '$link_id'");

        $findVal = $isSelected['type'];
        $array   = unserialize($results->selected_all_that_apply);
        foreach ($array as $key => $value) {
            if ($value['type'] == $findVal) {
                unset($array[$key]);
                $array = array_values($array);
            } else {
                $array = $array;
            }
        }
        $array[] = $isSelected;
        if (isset($isSelected) && !empty($isSelected)) {
            $updateQuery = $wpdb->update($assessment_table_name, array(
                'selected_all_that_apply' => serialize($array),
            ), array(
                'user_id' => $usrID,
                'link_id' => $link_id,
                'version' => $asses_version,
            )
            );
            if (false === $updateQuery) {
                $err     = __('There is somthing wrong.', 'tti-platform');
                $message = '<p class="error">' . esc_html($err) . '</p>';
                echo json_encode([
                    'message' => $message,
                    'status'  => '0',
                ]);
            } else {
                $err     = __('Your selections have been saved.', 'tti-platform');
                $message = '<p class="success">' . esc_html($err) . '</p>';
                echo json_encode([
                    'message' => $message,
                    'status'  => '1',
                    'user'    => $usrID,
                ]);
            }
        }
        exit;
    }

    /**
     * Function to generate random string.
     * @since   1.0.0
     * @access  public
     * @param string $length contains length of string which is to be generated
     * @return array returns randon generated string
     */

    public function generateRandomString($length = 20)
    {
        $characters       = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString     = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * Function to handle assessment graphic feedback shortcode.
     * @since   1.0.0
     * @access  public
     * @param array $atts contains shortcode attributes
     * @param string $content contains shortcode content
     * @param string $tag contains shortcode tags
     */
    public function assessment_graphic_feedback_shortcode($atts = [], $content = null, $tag = '')
    {
        global $wpdb, $current_usr;

        /* Enqueue style and script */
        $this->ttisi_enqueue_styles();

        /**
         * Fires before graphic feedback
         *
         * @since   1.2
         */
        do_action('ttisi_platform_before_graphic_feedback');

        $o               = '';
        $atts            = array_change_key_case((array) $atts, CASE_LOWER);
        $assessment_atts = shortcode_atts([
            'assess_id'        => '',
            'type'             => '',
            'intro'            => '',
            'count'            => '',
            'is_graph_adapted' => '',
            'is_graph_natural' => '',
            'both'             => '',
            'width'            => '',
            'para1'            => '',
            'para2'            => '',
        ], $atts, $tag);
        if (is_user_logged_in()) {
            $current_usr  = wp_get_current_user();
            $current_user = $current_usr->ID;
            $assess_id    = $assessment_atts['assess_id'];

            /* Get the current assessment status */
            $asses_status = get_post_meta($assess_id, 'status_assessment', true);
            /*************************************/

            if ($asses_status != 'Suspended') {

                $type        = $assessment_atts['type'];
                $page_indent = 1;

                /* check for EQTables type */
                if (strpos($type, 'EQTABLES2') !== false) {
                    $eqtype           = $type;
                    $eqtables_section = explode('-', $type);
                    $page_indent      = $eqtables_section[1];
                    $type             = 'EQTABLES2';
                }

                $intro                 = $assessment_atts['intro'];
                $count                 = $assessment_atts['count'];
                $is_graph_adapted      = $assessment_atts['is_graph_adapted'];
                $is_graph_natural      = $assessment_atts['is_graph_natural'];
                $both                  = $assessment_atts['both'];
                $width                 = $assessment_atts['width'];
                $para1                 = $assessment_atts['para1'];
                $para2                 = $assessment_atts['para2'];
                $link_id               = get_post_meta($assess_id, 'link_id', true);
                $assessment_table_name = $wpdb->prefix . 'assessments';
                /* Get assessment version */
                $asses_version = $this->get_current_user_assess_version($current_user, $link_id);

                $results = $wpdb->get_row("SELECT * FROM $assessment_table_name WHERE user_id ='$current_user' AND link_id ='$link_id' AND status = 1 AND version = $asses_version");

                if ($results) {

                    if ($type == 'PIAVBARS12HIGH' || $type == 'PIAVBARS12MED' || $type == 'PIAVBARS12LOW') {
                        $report_sections = unserialize($results->assessment_result);
                        $sections        = $report_sections->report->sections;
                        $assessmenrArr   = array();
                        foreach ($sections as $arrayResponseData) {
                            if ($arrayResponseData->type == $type) {
                                $title           = $arrayResponseData->header->titles;
                                $intro_header    = $arrayResponseData->header->text;
                                $behaviors       = $arrayResponseData->driving_forces;
                                $assessmenrArr[] = array(
                                    'title'     => $title,
                                    'intro'     => $intro_header,
                                    'behaviors' => $behaviors,
                                );
                            }
                        }
                        if ($intro == 'yes') {
                            $o .= '<p><em>' . esc_html($assessmenrArr[0]['intro']) . '</em></p>';
                        }
                        if ($count == 'all') {
                            if (isset($assessmenrArr[0]['behaviors']) && !empty($assessmenrArr[0]['behaviors']) && array_filter($assessmenrArr[0]['behaviors'])) {
                                foreach ($assessmenrArr[0]['behaviors'] as $key => $value) {
                                    $key   = $key + 1;
                                    $name  = $value->name;
                                    $text  = $value->text;
                                    $order = $value->order;
                                    $o .= '<p><strong>' . esc_html($order) . '. ' . esc_html($name) . ' - </strong> ' . esc_html($text) . '</p>';
                                    $o .= '<img src="' . esc_url($value->url) . '" alt="' . esc_attr($value->text) . '" />';
                                }
                            } else {}
                        } else {
                            $gen_char_parArr = explode(',', $count);
                            $behaviorsArr    = array();
                            foreach ($assessmenrArr[0]['behaviors'] as $key => $value) {
                                $behaviorsArr[] = array(
                                    'order' => $value->order,
                                    'url'   => $value->url,
                                    'text'  => $value->text,
                                    'name'  => $value->name,
                                );
                            }
                            foreach ($behaviorsArr as $key => $value) {
                                $key = $key + 1;
                                if (in_array($value['order'], $gen_char_parArr)) {
                                    $order = $value['order'];
                                    $url   = $value['url'];
                                    $text  = $value['text'];
                                    $name  = $value['name'];
                                    $o .= '<p><strong>' . esc_html($order) . '. ' . esc_html($name) . ' - </strong> ' . esc_html($text) . '</p>';
                                    $o .= '<img src="' . esc_url($url) . '" alt="' . esc_attr($text) . '" />';
                                } else {

                                }
                            }
                        }
                    } else {}
                    if ($type == 'EQTABLES2') {
                        /* EQTABLES2 */

                        $report_sections = unserialize($results->assessment_result);
                        $sections        = $report_sections->report->sections;
                        $assessmenrArr   = array();

                        foreach ($sections as $arrayResponseData) {
                            if ($arrayResponseData->type == $type) {
                                $pages[]         = $arrayResponseData->$page_indent;
                                $ident_of_eq     = strtolower($pages[0]->title);
                                $intro           = $pages[0]->description;
                                $leading_text    = $pages[0]->leadin;
                                $assessmenrArr[] = array(
                                    'intro'     => $intro,
                                    'lead_text' => $leading_text,
                                    'pages'     => $pages,
                                );
                            }
                        }

                        if ($count == 'all' || $count == 1) {
                            if (isset($assessmenrArr[0]['pages']) && !empty($assessmenrArr[0]['pages']) && array_filter($assessmenrArr[0]['pages'])) {
                                foreach ($assessmenrArr[0]['pages'] as $key => $value) {
                                    $key   = $key + 1;
                                    $text  = $value->description;
                                    $order = $value->score;
                                    $o .= '<img src="' . esc_url($value->url) . '" alt="' . esc_attr($value->text) . '" width="' . esc_attr($width) . '" />';
                                }
                            } else {}
                        }
                    }

                    if ($type == 'EQWHEEL') {
                        $report_sections = unserialize($results->assessment_result);
                        $sections        = $report_sections->report->sections;
                        $assessmenrArr   = array();

                        foreach ($sections as $arrayResponseData) {
                            if ($arrayResponseData->type == $type) {
                                $title           = $arrayResponseData->header->titles;
                                $intro_header    = $arrayResponseData->header->text;
                                $graph_url       = $arrayResponseData->graph_url;
                                $assessmenrArr[] = array(
                                    'title'     => $title,
                                    'intro'     => $intro_header,
                                    'graph_url' => $graph_url,
                                );
                            }
                        }

                        if ($intro == 'yes') {
                            $o .= '<p><em>' . esc_html($assessmenrArr[0]['intro']) . '</em></p>';
                        }
                        if ($count == 1) {
                            $o .= '<img src="' . esc_url($assessmenrArr[0]['graph_url']) . '" alt="' . esc_attr($assessmenrArr[0]['title']) . '" width="' . esc_attr($width) . '" />';
                        }

                    }
                    if ($type == 'EQRESULTS2' || $type == 'EQSCOREINFO2') {
                        $report_sections = unserialize($results->assessment_result);
                        $sections        = $report_sections->report->sections;
                        $assessmenrArr   = array();
                        foreach ($sections as $arrayResponseData) {
                            if ($arrayResponseData->type == $type) {
                                $title           = $arrayResponseData->header->titles;
                                $intro_header    = $arrayResponseData->lead_text;
                                $scores          = $arrayResponseData->scores;
                                $assessmenrArr[] = array(
                                    'title'  => $title,
                                    'intro'  => $intro_header,
                                    'scores' => $scores,
                                );
                            }
                        }
                        if ($intro == 'yes') {
                            $o .= '<p>' . esc_html($assessmenrArr[0]['intro']) . '</p>';
                        }
                        if ($count == 'all') {
                            if (isset($assessmenrArr[0]['scores']) && !empty($assessmenrArr[0]['scores']) && array_filter($assessmenrArr[0]['scores'])) {
                                foreach ($assessmenrArr[0]['scores'] as $key => $value) {
                                    $key  = $key + 1;
                                    $name = $value->name;
                                    $desc = $value->description;

                                    $o .= '<p><strong>' . esc_html($name) . '  </strong> : ' . esc_html($desc) . ' </p>';

                                    $o .= '<img src="' . esc_url($value->url) . '" alt="' . esc_attr($name) . '" width="' . esc_attr($width) . '" />';
                                }
                            } else {}
                        } else {
                            $gen_char_parArr = explode(',', $count);
                            $behaviorsArr    = array();
                            foreach ($assessmenrArr[0]['scores'] as $key => $value) {
                                $behaviorsArr[] = array(
                                    'order' => $value->id,
                                    'url'   => $value->url,
                                    'text'  => $value->description,
                                    'name'  => $value->name,
                                );
                            }
                            foreach ($behaviorsArr as $key => $value) {
                                $key = $key + 1;
                                if (in_array($value['order'], $gen_char_parArr)) {
                                    $order = $value['order'];
                                    $url   = $value['url'];
                                    $text  = $value['text'];
                                    $name  = $value['name'];
                                    $o .= '<p><strong>' . esc_html($name) . '  </strong> :' . esc_html($text) . '</p>';
                                    $o .= '<img src="' . esc_url($url) . '" alt="' . esc_attr($text) . '" width="' . esc_attr($width) . '" />';
                                } else {

                                }
                            }
                        }
                    }

                    if ($type == 'PGRAPH12') {
                        $report_sections = unserialize($results->assessment_result);
                        $sections        = $report_sections->report->sections;
                        $assessmenrArr   = array();
                        foreach ($sections as $arrayResponseData) {
                            if ($arrayResponseData->type == $type) {
                                $title           = $arrayResponseData->header->titles;
                                $intro_header    = $arrayResponseData->header->text;
                                $graphs          = $arrayResponseData->graphs;
                                $assessmenrArr[] = array(
                                    'graph' => $graphs,
                                );
                            }
                        }
                        if ($count == 'all') {
                            $full_url = $assessmenrArr[0]['graph']->full_url;
                            $o .= '<img src="' . esc_attr($full_url) . '" alt="" />';
                        } else {
                            $gen_char_parArr = explode(',', $count);
                            foreach ($gen_char_parArr as $graph) {
                                $knowledge     = $assessmenrArr[0]['graph']->row1_url;
                                $utility       = $assessmenrArr[0]['graph']->row2_url;
                                $surroundings  = $assessmenrArr[0]['graph']->row3_url;
                                $others        = $assessmenrArr[0]['graph']->row4_url;
                                $power         = $assessmenrArr[0]['graph']->row5_url;
                                $methodologies = $assessmenrArr[0]['graph']->row6_url;
                                if ($graph == 'Knowledge') {
                                    $o .= '<img src="' . esc_attr($knowledge) . '" alt="" />';
                                }
                                if ($graph == 'Utility') {
                                    $o .= '<img src="' . esc_attr($utility) . '" alt="" />';
                                }
                                if ($graph == 'Surroundings') {
                                    $o .= '<img src="' . esc_attr($surroundings) . '" alt="" />';
                                }
                                if ($graph == 'Others') {
                                    $o .= '<img src="' . esc_attr($others) . '" alt="" />';
                                }
                                if ($graph == 'Power') {
                                    $o .= '<img src="' . esc_attr($power) . '" alt="" />';
                                }
                                if ($graph == 'Methodologies') {
                                    $o .= '<img src="' . esc_attr($methodologies) . '" alt="" />';
                                }
                            }
                        }
                    } else {}

                    if ($type == 'SABARS') {
                        $report_sections = unserialize($results->assessment_result);
                        $sections        = $report_sections->report->sections;
                        $assessmenrArr   = array();
                        foreach ($sections as $arrayResponseData) {
                            if ($arrayResponseData->type == $type) {
                                $title           = $arrayResponseData->header->titles;
                                $intro_header    = $arrayResponseData->header->text;
                                $behaviors       = $arrayResponseData->behaviors;
                                $assessmenrArr[] = array(
                                    'title'     => $title,
                                    'intro'     => $intro_header,
                                    'behaviors' => $behaviors,
                                );
                            }
                        }
                        if ($intro == 'yes') {
                            $o .= '<p><em>' . esc_html($assessmenrArr[0]['intro']) . '</em></p>';
                        }
                        if ($count == 'all') {
                            if (isset($assessmenrArr[0]['behaviors']) && !empty($assessmenrArr[0]['behaviors']) && array_filter($assessmenrArr[0]['behaviors'])) {
                                foreach ($assessmenrArr[0]['behaviors'] as $key => $value) {
                                    $key          = $key + 1;
                                    $arrBoldAfter = $value->text;
                                    $arrBold      = explode(":", $value->text, 2);
                                    $titlearrBold = $arrBold[0];
                                    $text         = substr($arrBoldAfter, (strpos($arrBoldAfter, ':') ?: -1) + 1);
                                    $o .= '<p><strong>' . esc_html($key) . '. ' . esc_html($titlearrBold) . ' - </strong> ' . esc_html($text) . '</p>';
                                    $o .= '<img src="' . esc_attr($value->url) . '" alt="' . esc_attr($value->text) . '" />';
                                }
                            } else {

                            }
                        } else {

                            $gen_char_parArr = explode(',', $count);
                            $behaviorsArr    = array();
                            foreach ($assessmenrArr[0]['behaviors'] as $key => $value) {
                                $behaviorsArr[] = array(
                                    'order' => $value->order,
                                    'url'   => $value->url,
                                    'text'  => $value->text,
                                );
                            }
                            foreach ($behaviorsArr as $key => $value) {
                                $key = $key + 1;
                                if (in_array($value['order'], $gen_char_parArr)) {
                                    $order = $value['order'];
                                    $url   = $value['url'];
                                    $text  = $value['text'];

                                    $arrBoldAfter = $text;
                                    $arrBold      = explode(":", $text, 2);
                                    $titlearrBold = $arrBold[0];
                                    $text         = substr($arrBoldAfter, (strpos($arrBoldAfter, ':') ?: -1) + 1);
                                    $o .= '<p><strong>' . esc_html($key) . '. ' . esc_html($titlearrBold) . ' - </strong> ' . esc_html($text) . '</p>';
                                    $o .= '<img src="' . esc_attr($url) . '" alt="' . esc_attr($text) . '" />';
                                } else {

                                }
                            }
                        }
                    } else {}

                    if ($type == 'NORMS12') {
                        $report_sections = unserialize($results->assessment_result);
                        $sections        = $report_sections->report->sections;
                        $assessmenrArr   = array();
                        foreach ($sections as $arrayResponseData) {
                            if ($arrayResponseData->type == $type) {
                                $title           = $arrayResponseData->header->titles;
                                $intro_header    = $arrayResponseData->header->text;
                                $par1            = $arrayResponseData->par1;
                                $par2            = $arrayResponseData->par2;
                                $driving_forces  = $arrayResponseData->driving_forces;
                                $assessmenrArr[] = array(
                                    'title'          => $title,
                                    'intro'          => $intro_header,
                                    'par1'           => $par1,
                                    'par2'           => $par2,
                                    'driving_forces' => $driving_forces,
                                );
                            }
                        }
                        if ($intro == 'yes') {
                            $o .= '<p><em>' . esc_html($assessmenrArr[0]['intro']) . '</em></p>';
                        }
                        if ($para1 == 'yes') {
                            $o .= '<p>' . esc_html($assessmenrArr[0]['par1']) . '</p>';
                        }
                        if ($para2 == 'yes') {
                            $o .= '<p>' . esc_html($assessmenrArr[0]['par2']) . '</p>';
                        }
                        if ($count == 'all') {
                            if (isset($assessmenrArr[0]['driving_forces']) && !empty($assessmenrArr[0]['driving_forces']) && array_filter($assessmenrArr[0]['driving_forces'])) {
                                foreach ($assessmenrArr[0]['driving_forces'] as $key => $value) {
                                    $key  = $key + 1;
                                    $text = $value->text;
                                    $name = $value->name;

                                    $o .= '<p><strong>' . esc_html($key) . '. ' . esc_html($name) . ' - </strong> ' . esc_html($text) . '</p>';
                                    $o .= '<img src="' . esc_attr($value->url) . '" alt="' . esc_html($value->name) . '" />';
                                }
                            } else {

                            }
                        } else {

                            $gen_char_parArr = explode(',', $count);
                            $behaviorsArr    = array();

                            foreach ($assessmenrArr[0]['driving_forces'] as $key => $value) {
                                $behaviorsArr[] = array(
                                    'url'  => $value->url,
                                    'text' => $value->text,
                                    'name' => $value->name,
                                );
                            }

                            foreach ($behaviorsArr as $key => $value) {
                                $key = $key + 1;
                                if (in_array($key, $gen_char_parArr)) {
                                    $url  = $value['url'];
                                    $text = $value['text'];
                                    $name = $value['name'];

                                    $o .= '<p><strong>' . esc_html($key) . '. ' . esc_html($name) . ' - </strong> ' . esc_html($text) . '</p>';
                                    $o .= '<img src="' . esc_attr($url) . '" alt="' . esc_attr($name) . '" />';
                                } else {

                                }
                            }
                        }
                    } else {}

                    if ($type == 'MICHART1') {
                        $report_sections = unserialize($results->assessment_result);
                        $sections        = $report_sections->report->sections;
                        $assessmenrArr   = array();
                        foreach ($sections as $arrayResponseData) {
                            if ($arrayResponseData->type == $type) {
                                if ($is_graph_natural == 'yes') {
                                    $natural = $arrayResponseData->graph_url;
                                    $o .= '<img src="' . esc_url($natural) . '" alt="" width="' . esc_attr($width) . '" />';
                                }
                            }
                        }
                    } else {}

                    if ($type == 'MICHART2') {
                        $report_sections = unserialize($results->assessment_result);
                        $sections        = $report_sections->report->sections;
                        $assessmenrArr   = array();
                        foreach ($sections as $arrayResponseData) {
                            if ($arrayResponseData->type == $type) {
                                if ($is_graph_adapted == 'yes') {
                                    $adapted = $arrayResponseData->graph_url;
                                    $o .= '<img src="' . esc_url($adapted) . '" alt="" width="' . esc_attr($width) . '" />';
                                }
                            }
                        }
                    } else {}

                    if ($type == 'SAGRAPH') {
                        $report_sections = unserialize($results->assessment_result);
                        $sections        = $report_sections->report->sections;
                        $assessmenrArr   = array();

                        foreach ($sections as $arrayResponseData) {
                            if ($arrayResponseData->type == $type) {
                                if ($is_graph_adapted == 'yes' || $both == 'yes') {
                                    $adapted = $arrayResponseData->adapted;
                                    $o .= '<img src="' . esc_url($adapted) . '" alt="" width="' . esc_attr($width) . '" />';
                                }
                                if ($is_graph_natural == 'yes' || $both == 'yes') {
                                    $natural = $arrayResponseData->natural;
                                    $o .= '<img src="' . esc_url($natural) . '" alt="" width="' . esc_attr($width) . '" />';
                                }
                            }
                        }
                    } else {}

                    if ($type == 'WHEEL') {
                        $report_sections = unserialize($results->assessment_result);
                        $sections        = $report_sections->report->sections;
                        $assessmenrArr   = array();
                        foreach ($sections as $arrayResponseData) {
                            if ($arrayResponseData->type == $type) {
                                if ($both == 'yes') {
                                    $bothURL = $arrayResponseData->wheel->url;
                                    $o .= '<img src="' . esc_url($bothURL) . '" alt="" width="' . esc_attr($width) . '" />';
                                }
                                if ($is_graph_adapted == 'yes') {
                                    $adapted = $arrayResponseData->wheel->adapted->url;
                                    $o .= '<img src="' . esc_url($adapted) . '" alt="" width="' . esc_attr($width) . '" />';
                                }
                                if ($is_graph_natural == 'yes') {
                                    $natural = $arrayResponseData->wheel->natural->url;
                                    $o .= '<img src="' . esc_url($natural) . '" alt="" width="' . esc_attr($width) . '" />';
                                }
                            }
                        }
                    } else {

                    }
                } else {
                    $o .= '<h2>No feedback found. Please contact to the administrator of this site.</h2>';

                }
            } else {
                $msg = __('This assessment has been suspended.', 'tti-platform');
                $o   = '';
                $o .= '<div class="assessment_disabled">';
                $o .= '<h2>' . esc_html($msg) . '</h2>';
                $o .= '</div>';
            }
        } else {}

        /**
         * Filter to update feedback graphic assessment array
         *
         * @since  1.2
         */
        $assessmenrArr = apply_filters('ttisi_platform_assessments_feedback_graphic_final_array', $assessmenrArr);

        /**
         * Filter to update feedback final string
         *
         * @since  1.2
         */
        $o = apply_filters('ttisi_platform_assessments_feedback_graphic_final_string', $o);

        /**
         * Fires after graphic feedback
         *
         * @since   1.2
         */
        do_action('ttisi_platform_after_graphic_feedback', $assessmenrArr, $o);
        return $o;
    }

    /**
     * Function to init graphic feedback shortcode.
     *
     * @since   1.0.0
     */
    public function assessment_graphic_feedback_init()
    {
        add_shortcode('assessment_graphic_feedback', array($this, 'assessment_graphic_feedback_shortcode'));
    }

    /**
     * Function to init PDF download shortcode.
     *
     * @since   1.0.0
     */
    public function assessment_PDF_init()
    {
        add_shortcode('assessment_pdf_download', array($this, 'assessment_pdf_download_shortcode'));
    }

    /**
     * Function to handle assessment graphic feedback shortcode process.
     *
     * @since   1.0.0
     * @param array $atts contains shortcode attributes
     * @param string $content contains shortcode content
     * @param string $tag contains shortcode tags
     */
    public function assessment_pdf_download_shortcode($atts = [], $content = null, $tag = '')
    {

        /**
         * Fires before graphic feedback
         *
         * @since   1.2
         */
        do_action('ttisi_platform_before_assessment_pdf_download_shortcode');

        global $wpdb, $current_usr;

        /* Enqueue style and script */
        $this->ttisi_enqueue_styles();

        $o               = '';
        $atts            = array_change_key_case((array) $atts, CASE_LOWER);
        $assessment_atts = shortcode_atts([
            'assess_id' => '',
        ], $atts, $tag);
        $assess_id = $assessment_atts['assess_id'];

        /* Get the current assessment status */
        $asses_status = get_post_meta($assess_id, 'status_assessment', true);
        /*************************************/

        if ($asses_status != 'Suspended') {

            $post         = get_post($assess_id);
            $slug         = $post->post_name;
            $post_meta    = get_post_custom($assess_id);
            $link_id      = $post_meta['link_id']['0'];
            $print_report = $post_meta['print_report']['0'];
            if ($print_report == 'Yes') {

                if (is_user_logged_in()) {
                    $current_usr           = wp_get_current_user();
                    $current_user          = $current_usr->ID;
                    $assessment_table_name = $wpdb->prefix . 'assessments';
                    /* Get assessment version */
                    $asses_version = $this->get_current_user_assess_version($current_user, $link_id);
                    $results       = $wpdb->get_row("SELECT report_id, password FROM $assessment_table_name WHERE user_id ='$current_user' AND link_id ='$link_id' AND status = 1 AND version = $asses_version");

                    if (empty($results)) {
                        if ($asses_version > 1) {
                            $asses_version = $asses_version - 1;
                        }
                        $results = $wpdb->get_row("SELECT report_id, password FROM $assessment_table_name WHERE user_id ='$current_user' AND link_id ='$link_id' AND status = 1 AND version = $asses_version");
                    }

                    if ($results) {
                        //$pageURL = esc_url_raw($_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"]);
                        $pageURL = get_site_url();
                        $o .= '<a assessment-id="' . esc_attr($assess_id) . '" class="download_pdf" href="' . $pageURL . '?version=' . esc_attr($asses_version) . '&assessment_id=' . esc_attr($assess_id) . '" target="_blank"><img width="40" src="' . plugin_dir_url(__FILE__) . 'images/download.png" alt="" /> <span>Download Assessment Results</span></a>';
                    } else { $o .= '<h4>' . esc_html('No PDF Available') . '</h4>';}
                } else {}
            }
        } else {
            $msg = __('This assessment has been suspended.', 'tti-platform');
            $o   = '';
            $o .= '<div class="assessment_disabled">';
            $o .= '<h2>' . esc_html($msg) . '</h2>';
            $o .= '</div>';
        }

        /**
         * Filter to update pdf download final string
         *
         * @since  1.2
         */
        $o = apply_filters('ttisi_platform_assessment_pdf_download_shortcode_final_string', $o);

        /**
         * Fires after graphic feedback
         *
         * @since   1.2
         */
        do_action('ttisi_platform_after_assessment_pdf_download_shortcode', $o);

        return $o;
    }

    /**
     * Function to handle download PDF report.
     *
     * @since   1.0.0
     */
    public function assessment_pdf_download_button()
    {

        global $wpdb, $current_usr;

        /* Enqueue style and script */
        $this->ttisi_enqueue_styles();

        if (
            (isset($_GET['assessment_id']) && !empty($_GET['assessment_id']))
        ) {
            $assess_id = sanitize_text_field($_GET['assessment_id']);
            $post      = get_post($assess_id);
            $password == 'false';
            $slug                 = sanitize_title_with_dashes( $post->post_title );
            $post_meta            = get_post_custom($assess_id);
            $link_id              = $post_meta['link_id']['0'];
            $account_login        = $post_meta['account_login']['0'];
            $api_service_location = $post_meta['api_service_location']['0'];
            $api_key              = $post_meta['api_key']['0'];
            $host                 = sanitize_text_field($_SERVER['HTTP_HOST']);
            $domain               = preg_match("/[^\.\/]+\.[^\.\/]+$/", $host, $matches);
            $o                    = '';
            if (is_user_logged_in()) {
                $current_usr           = wp_get_current_user();
                $firstName             = $current_usr->user_firstname;
                $lastName              = $current_usr->user_lastname;
                $current_user          = $current_usr->ID;
                $assessment_table_name = $wpdb->prefix . 'assessments';

                /* if opened assessment respondent download request */
                if (
                    isset($_GET['opened_assessment']) &&
                    !empty($_GET['opened_assessment']) &&
                    $_GET['opened_assessment'] == 'true'
                ) {

                    $password  = sanitize_text_field($_GET['respondent_passwd']);
                    $firstName = sanitize_text_field($_GET['f_name']);
                    $lastName  = sanitize_text_field($_GET['l_name']);
                    $version   = sanitize_text_field($_GET['version']);

                    $tablename = $wpdb->prefix . "list_users_assessments";

                    $sql       = $wpdb->prepare('SELECT report_id FROM ' . $tablename . ' WHERE user_id = %d AND version = %d AND password = %s', $current_user, $version, $password);
                    $results   = $wpdb->get_results($sql, OBJECT);
                    $report_id = $results[0]->report_id;

                } elseif (
                    isset($_GET['cp_page']) &&
                    !empty($_GET['cp_page']) &&
                    $_GET['cp_page'] == 'true' &&
                    ($_GET['assessment_type'] == 'close' ||
                        !isset($_GET['assessment_type']))
                ) {
                    /* Completed profile if section */
                    $u_id    = sanitize_text_field($_GET['user_id']);
                    $version = sanitize_text_field($_GET['version']);
                    $results = $wpdb->get_row("SELECT report_id, password, first_name, last_name, service_location, api_token FROM $assessment_table_name WHERE user_id ='$u_id' AND link_id ='$link_id' AND status = 1 AND version = $version");

                    $report_id = $results->report_id;
                    $firstName = $results->first_name;
                    $lastName  = $results->last_name;
                } elseif (
                    isset($_GET['assessment_type']) &&
                    !empty($_GET['assessment_type']) &&
                    $_GET['assessment_type'] == 'open'
                ) {
                    $assessment_user_table = $wpdb->prefix . 'list_users_assessments';
                    /* Completed profile if section */
                    $u_id    = sanitize_text_field($_GET['user_id']);
                    $version = sanitize_text_field($_GET['version']);
                    $results = $wpdb->get_row("SELECT report_id, password, first_name, last_name, service_location, api_token FROM $assessment_user_table WHERE user_id ='$u_id' AND link_id ='$link_id' AND status = 1 AND version = $version");

                    $report_id = $results->report_id;
                    $firstName = $results->first_name;
                    $lastName  = $results->last_name;
                } elseif (!isset($_GET['tti_print_consolidation_report'])) {
                    $use_id = $current_user;
                    if (isset($_GET['user_id'])) {
                        $current_user = $_GET['user_id'];
                    }

                    $version = sanitize_text_field($_GET['version']);
                    $results = $wpdb->get_row("SELECT report_id, password, service_location, api_token, first_name, last_name FROM $assessment_table_name WHERE user_id ='$current_user' AND link_id ='$link_id' AND status = 1 AND version = $version");

                    $report_id = $results->report_id;
                    $password  = $results->password;
                    $firstName = $results->first_name;
                    $lastName  = $results->last_name;
                }

                // if password value is none then we have to use user level details
                //if($password == 'none') {
                $api_service_location = $results->service_location;
                $api_key              = $results->api_token;
                //}

                /* API v3.0 url */
                $url = $api_service_location . '/api/v3/reports/' . $report_id . '.pdf';

                $headers = array(
                    'Authorization'             => $api_key,
                    'Accept'                    => 'application/pdf',
                    'Content-Type'              => 'application/pdf',
                    'Content-Transfer-Encoding' => 'binary',
                );
                $args = array(
                    'method'  => 'GET',
                    'headers' => $headers,
                    'timeout' => 25000,
                );

                $response = wp_remote_request($url, $args);

                if (is_wp_error($response)) {
                    $error_message = $response->get_error_message();
                    echo json_encode($error_message);
                } else {
                    $data = wp_remote_retrieve_body($response);

                    $path = $firstName . '-' . $lastName . '-' . $slug . '.pdf';
                    file_put_contents($path, $data);
                    $content = file_get_contents($path);
                    header('Content-Type: application/pdf');
                    header('Content-Length: ' . strlen($content));
                    header('Content-Disposition: attachment; filename="' . $firstName . '-' . $lastName . '-' . $slug . '.pdf"');
                    header('Cache-Control: private, max-age=0, must-revalidate');
                    header('Pragma: public');
                    ini_set('zlib.output_compression', '0');
                    ob_get_clean();
                    unlink($path);
                    die($content);
                }
            }
        }
    }

    /**
     * Function to start take Assessment process.
     *
     * @since    1.0.0
     */
    public function take_assessment()
    {
        global $current_user, $wpdb;

        $version_assess   = 1;
        $report_api_check = 0;

        /* error-log */
        $this->error_log->put_error_log('*********************************** Starts Take Assessment ********************************************');

        $retake_status        = sanitize_text_field($_POST['retake_status']);
        $assessment_id        = sanitize_text_field($_POST['assessment_id']);
        $assessment_permalink = esc_url_raw($_POST['assessment_permalink']);
        $assessment_locked    = sanitize_text_field($_POST['assessment_locked']);

        wp_get_current_user();

        /* If assessment opens */
        if ($assessment_locked == 'false') {
            $link_id = get_post_meta($assessment_id, 'link_id', true);
            set_transient('assessmentListener' . $current_user->ID, $assessment_permalink, DAY_IN_SECONDS);
            $survay_location = get_post_meta($assessment_id, 'survay_location', true);
            echo json_encode([
                'survey_location' => $survay_location,
                'onsite_survey'   => get_site_url( ) . '/take-assessment-on-site/',
                'link_id'         => $link_id,
                'status'          => '0',
            ]);
            exit;
        }
        /* ***************** */

        $userArr[] = array(
            'user_ID'        => $current_user->ID,
            'user_firstname' => $current_user->user_firstname,
            'user_lastname'  => $current_user->user_lastname,
            'user_login'     => $current_user->user_login,
            'user_email'     => $current_user->user_email,
        );

        /* error-log */
        $this->error_log->put_error_log('User Details :');
        $this->error_log->put_error_log($userArr, 'array');
        $this->error_log->put_error_log('');

        set_transient('assessmentListener' . $current_user->ID, $assessment_permalink, DAY_IN_SECONDS);
        $result_retake    = false;
        $access_token     = get_post_meta($assessment_id, 'api_key', true);
        $account_id       = get_post_meta($assessment_id, 'account_login', true);
        $service_location = get_post_meta($assessment_id, 'api_service_location', true);
        $survay_location  = get_post_meta($assessment_id, 'survay_location', true);
        $link_id          = get_post_meta($assessment_id, 'link_id', true);

        /* error-log */
        $this->error_log->put_error_log('Link ID : ' . $link_id);

        $assessment_table_name = $wpdb->prefix . 'assessments';
        if (isset($userArr[0]['user_firstname']) && !empty($userArr[0]['user_firstname']) && isset($userArr[0]['user_lastname']) && !empty($userArr[0]['user_lastname'])) {
            /* Query to count version */
            $results_vers   = $wpdb->get_results("SELECT * FROM $assessment_table_name WHERE user_id ='$current_user->ID' AND link_id='$link_id' AND status = 1");
            $version_assess = (isset($results_vers) && !empty($results_vers)) ? count($results_vers) + 1 : $version_assess;

            /* Check user level assessment checks */
            $result_retake = $this->tti_handle_user_level_assessment($current_user->ID, $link_id, $assessment_id, $retake_status, $version_assess);
            /* NOTE:  Code will exit if user level assessment condition applies */
            set_transient('assessmentListenerRetakeAsseStatus' . $current_user->ID, 'false', DAY_IN_SECONDS);
            $orig_link_id = $link_id;
            if ($result_retake != false) {
                $access_token     = $result_retake['api_key'];
                $account_id       = $result_retake['account_login'];
                $service_location = $result_retake['api_service_location'];
                $survay_location  = $result_retake['survey_location'];
                $link_id          = $result_retake['link_id'];
                set_transient('assessmentListenerRetakeAsseStatus' . $current_user->ID, 'true', DAY_IN_SECONDS);
                $report_api_check = 2;
                //set_transient( 'assessmentListenerReportViewID'.$current_user->ID, $result_retake['reportview_id'], DAY_IN_SECONDS );
            }
            set_transient('assessmentListenerRetakeAsseLink' . $current_user->ID, $orig_link_id, DAY_IN_SECONDS);
            /* Query to get assessment detail */
            $results = $wpdb->get_results("SELECT * FROM $assessment_table_name WHERE user_id = '$current_user->ID' AND link_id = '$orig_link_id' AND status = 0");
            $results = reset($results);

            // $results = $this->get_latest_version($results);
            if (
                $wpdb->num_rows > 0 &&
                (
                    isset($results->email) &&
                    !empty($results->email) &&
                    isset($results->password) &&
                    !empty($results->password) &&
                    isset($results->first_name) &&
                    !empty($results->first_name) &&
                    isset($results->last_name) &&
                    !empty($results->last_name)
                )
            ) {
                /* error-log */
                $this->error_log->put_error_log('Password :' . $results->password);
                $this->error_log->put_error_log('Redirecting to assessment successfully');

                echo json_encode([
                    'survey_location' => $survay_location,
                    'onsite_survey'   => get_site_url( ) . '/take-assessment-on-site/',
                    'link_id'         => $link_id,
                    'password'        => $results->password,
                    'email'           => $results->email,
                    'user_id'         => $current_user->ID,
                    'status'          => '2',
                ]);
            } else {

                /* error-log */
                $this->error_log->put_error_log('Start creating assessment link...');

                /* API v3.0 url */
                if ($result_retake != false) {
                    $url = $service_location . '/api/v3/respondents?link_login=' . $link_id;
                } else {
                    $url = $service_location . '/api/v3/respondents?link_login=' . $orig_link_id;
                }

                /* error-log */
                $this->error_log->put_error_log('API URL hit to create assessment link : ' . $url);

                $payload = array(
                    'first_name'   => $userArr[0]['user_firstname'],
                    'last_name'    => $userArr[0]['user_lastname'],
                    'gender'       => 'M',
                    'email'        => $userArr[0]['user_email'],
                    'company'      => '',
                    'position_job' => '',
                );

                $data = wp_remote_post($url, array(
                    'headers'     => array(
                        'Content-Type'  => 'application/json; charset=utf-8',
                        'Authorization' => $access_token,
                        'Accept'        => 'application/json',
                    ),
                    'body'        => json_encode($payload),
                    'method'      => 'POST',
                    'data_format' => 'body',
                ));
                $response = json_decode(wp_remote_retrieve_body($data));

                /* error-log */
                $this->error_log->put_error_log($response, 'array');
                $this->error_log->put_error_log('');

                if (is_wp_error($response)) {
                    $error_message = $response->get_error_message();
                    echo json_encode([
                        'error'  => $error_message,
                        'status' => '1',
                    ]);
                } elseif (isset($response->passwd) && !empty($response->passwd) && $response->passwd != null) {

                    $responseUserID        = $userArr[0]['user_ID'];
                    $responseFirstName     = $response->first_name;
                    $responseLastName      = $response->last_name;
                    $responseEmail         = $response->email;
                    $responsePassword      = $response->passwd;
                    $responseCompany       = $response->company;
                    $responseGender        = $response->gender;
                    $responsePosition_job  = $response->position_job;
                    $responseCreated_at    = $response->created_at;
                    $responseUpdated_at    = $response->updated_at;
                    $responseStatus        = $response->resp_status;
                    $assessment_table_name = $wpdb->prefix . 'assessments';
                    $this->error_log->put_error_log('Password :' . $responsePassword);
                    if ($results) {
                        /* error-log */
                        $this->error_log->put_error_log('Query Type : Updating');
                        $insertQuery = $wpdb->update($assessment_table_name,
                            array(
                                'user_id'          => $responseUserID,
                                'first_name'       => $responseFirstName,
                                'last_name'        => $responseLastName,
                                'email'            => $responseEmail,
                                'service_location' => $service_location,
                                'account_id'       => $account_id,
                                'link_id'          => $orig_link_id,
                                'api_token'        => $access_token,
                                'gender'           => $responseGender,
                                'company'          => $responseCompany,
                                'status'           => $responseStatus,
                                'version'          => $version_assess,
                                'position_job'     => $responsePosition_job,
                                'password'         => $responsePassword,
                                'created_at'       => $responseCreated_at,
                                'assess_type'      => $report_api_check,
                                'updated_at'       => $responseUpdated_at,
                            ),
                            array(
                                'link_id' => $orig_link_id,
                                'user_id' => $current_user->ID,
                                'status'  => 0,
                            ),
                            array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
                        );
                    } else {
                        /* error-log */
                        $this->error_log->put_error_log('Query Type : Inserting');
                        $insertQuery = $wpdb->insert($assessment_table_name,
                            array(
                                'user_id'          => $responseUserID,
                                'first_name'       => $responseFirstName,
                                'last_name'        => $responseLastName,
                                'email'            => $responseEmail,
                                'service_location' => $service_location,
                                'account_id'       => $account_id,
                                'link_id'          => $orig_link_id,
                                'api_token'        => $access_token,
                                'gender'           => $responseGender,
                                'company'          => $responseCompany,
                                'status'           => $responseStatus,
                                'version'          => $version_assess,
                                'position_job'     => $responsePosition_job,
                                'password'         => $responsePassword,
                                'created_at'       => $responseCreated_at,
                                'assess_type'      => $report_api_check,
                                'updated_at'       => $responseUpdated_at,
                            ),
                            array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
                        );
                    }
                    /* error-log */
                    $this->error_log->put_error_log('Query Result : ' . $insertQuery);

                    if ($insertQuery) {
                        echo json_encode([
                            'survey_location' => $survay_location,
                            'onsite_survey'   => get_site_url( ) . '/take-assessment-on-site/',
                            'link_id'         => $link_id,
                            'password'        => $responsePassword,
                            'email'           => $responseEmail,
                            'user_id'         => $responseUserID,
                            'status'          => '3',
                        ]);
                    } else {
                        echo json_encode([
                            'status' => '4',
                            'url'    => $response,
                            'onsite_survey'   => get_site_url( ) . '/take-assessment-on-site/',
                        ]);
                    }

                    /* error-log */
                    $this->error_log->put_error_log('End creating assessment link');
                    /* error-log */
                    $this->error_log->put_error_log('Redirecting to assessment successfully');
                } else {
                    /* error-log */
                    $this->error_log->put_error_log('No Response From API');
                    echo json_encode(['status' => '6']);
                }
            }
        } else {
            /* error-log */
            $this->error_log->put_error_log('First Name and Last Name not exists');

            echo json_encode(['status' => '5', 'onsite_survey'   => get_site_url( ) . '/take-assessment-on-site/', ]);
        }

        /* error-log */
        //$this->error_log->put_error_log('*********************************** Ends Take Assessment **********************************************');
        $this->error_log->put_error_log('');

        wp_die();
    }

    /**
     * Handle user level assessment process
     *
     * @since    1.7.0
     */
    public function tti_handle_user_level_assessment(
        $user_id,
        $link_id,
        $assess_id,
        $retake_status,
        $version_assess
    ) {
        require_once plugin_dir_path(__FILE__) . 'partials/user-level-assessments/class-tti-platform-user-assessments.php';
        $user_assessment = new TTI_Platform_User_Assessments($user_id, $link_id, $assess_id, $this->error_log, $this, $retake_status, $version_assess);
        if ($retake_status == 'true') {
            // Retake assessment function
            return $user_assessment->start_user_level_retake_assess_process();
        } else {
            // Take assessment function
            $user_assessment->start_user_level_assess_process();
        }
    }

    /**
     * display onsite assessment.
     *
     * @return string
     */
    public function tti_take_assessment_on_site() {

        $link_id = '';
        $password = '';
        $email = '';
    
        if( isset( $_GET['link_id'] ) && ! empty( $_GET['link_id'] ) ) {
            $link_id =  $_GET['link_id'];
        }

        if( isset( $_GET['password'] ) && ! empty( $_GET['password'] ) ) {
            $password =  $_GET['password'];
        }

        if( isset( $_GET['user_id'] ) && ! empty( $_GET['user_id'] ) ) {
            $user_id =  $_GET['user_id'];
        }

        $user_info = get_userdata($user_id);
        $user_email = $user_info->user_email;
    
        
        if ( ! empty( $link_id ) ) {
            
            $html .= '
            <script>
                function configure_ttisi_survey(config) {
                    config.logoUrl = "https://adorable-narwhal-1a3181.netlify.app/1x1-00000000.png";
                    config.homeUrl = "https://communicationprofile.com/";
                    config.credentials.code =  "'. $link_id .'";
                    config.credentials.password = "'. $password .'";
                    config.credentials.email = "'. $user_email .'";
                }
            </script>
            <script src="https://justrespond.com/ttisi-survey-loader.js"></script>
            <style>
                #ttisi-survey .ttisi-logo {
                    height: inherit !important;
                    width: 10px !important;
                    margin-bottom: 0 !important;
                }
                #ttisi-assessment .btn-primary{
                    color: #fff !important;
                }
                #ttisi-survey > footer {
                    visibility: hidden;
                }
                #data-entry-mode {
                    display: none !important;
                }
                </style>
                <div id="ttisi-survey"></div>           
            ';

            return $html;
        }
    
    }

}

/* initialize pubic main class */
new TTI_Platform_Public_Main_Class();
