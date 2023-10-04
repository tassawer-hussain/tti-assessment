<?php

/**
 * Class to handle plugin activation functions.
 *
 *
 * @since   1.0.0
 * @package   TTI_Platform
 * @subpackage  TTI_Platform/includes
 * @author  Presstigers
 */

class TTI_Platform_Activator_Class {

    /**
     * Define the constructor
     *
     * @since  1.0.0
     */
    public function __construct() {
        
    }


     /**
     * Function to check the locked status for assessment.
     *
     * @since  1.4.1
     */
    public function check_locked_status() {
        /* Check locked index status */
       $this->update_locked_assessment_status();
       $this->ttisi_schedule_cron_three_days();
    }

    /**
     * Schdule the CRON job
     *
     * @since  1.0.0
     */
    public function schdule_cron_job() {
        /* CRON job hooks  */
        $this->ttisi_schedule_cron();
    }

    /**
     * Function to schedules the CRON for 15 minutes
     *
     * @since  1.0.0
     * @param array $schedules contains cron time schedules 
     * @return array returns updated schedules 
     */
    public function fifteen_min_custom_cron_schedule( $schedules ) {
        $schedules['fifteen_minutes_ttsi_cron'] = array(
            'interval' => 15 * 60,
            'display'  => esc_html__( 'Every Fifteen Minutes' ),
        );
        return $schedules;
    }
    
     /**
     * Schdule the CRON job 3 days
     *
     * @since       1.0.0
     */
    function ttisi_schedule_cron_three_days() {
      if ( !wp_next_scheduled( 'assessments_pdf_files_checker' ) )
        wp_schedule_event( time(), 'three_days_ttsi_cron', 'assessments_pdf_files_checker');
    }


 
    /**
     * Schdule the CRON job
     *
     * @since       1.0.0
     */
    function ttisi_schedule_cron() {
      if ( !wp_next_scheduled( 'assessments_status_checker' ) )
        wp_schedule_event( time(), 'fifteen_minutes_ttsi_cron', 'assessments_status_checker');
    }


    /**
     * Declare custom post types, taxonomies, and plugin settings
     * Flushes rewrite rules afterwards
     *
     * @since     1.0.0
     */
    public function createListenerPage() {
        if ( ! current_user_can( 'activate_plugins' ) ) return;
        global $wpdb;
        if ( null === $wpdb->get_row( "SELECT post_name FROM {$wpdb->prefix}posts WHERE post_name = 'listener'", 'ARRAY_A' ) ) {
            $current_user = wp_get_current_user();
            $page = array (
                'post_title'  => __( 'Listener' ),
                'post_content'  => '[assessment_listener]',
                'post_status' => 'publish',
                'post_author' => $current_user->ID,
                'post_type'   => 'page',
            );
            $post_id = wp_insert_post( $page );
            update_option('listener_page_id', $post_id);
        }
    }

    /**
    * Function to check which assessment status.
    *
    * @since  1.2
    */
    function update_locked_assessment_status() {
        /* Get all assessment post types */
        $disabled_links = array();
        $args = array( 'post_type' => 'tti_assessments', 'posts_per_page' => -1 );
            $loop = new WP_Query( $args );
            $assessmenrArr = array();
            while ( $loop->have_posts() ) : $loop->the_post();
              $assesment_id = get_the_ID();
              $status_assessment = get_post_meta($assesment_id, 'status_assessment', true);
              $status_locked = get_post_meta($assesment_id, 'status_locked', true);
              $api_service_location = get_post_meta($assesment_id, 'api_service_location', true);
              $api_key = get_post_meta($assesment_id, 'api_key', true);
              $assessment_link = get_post_meta($assesment_id, 'link_id', true);
              $ass_lock_status = $this->get_status_assessment_locked($api_service_location, $assessment_link, $api_key);

              /* check locked status */
              if($status_locked != $ass_lock_status) {
                update_post_meta( $assesment_id, 'status_locked', $ass_lock_status );
              }
             
            endwhile;
    }
    
    /**
    * Function to update the assessment locked status.
    *
    * @since  1.4.1
    *
    * @param string $api_service_location contains api service location link
    * @param string $link_id contains link id
    * @param string $access_token contains access token
    * @return string
    */
   function get_status_assessment_locked($api_service_location, $link_id, $access_token) {
        /* API v 3.0 url */  
        $newUrl =  $api_service_location . '/api/v3/links/'.$link_id;
              
        $headers = array(
          'Authorization' => $access_token,
          'Accept' => 'application/json',
          'Content-Type' => 'application/json'
        );
        
        $args = array(
          'method' => 'GET',
          'headers' => $headers,
        );

        $data = wp_remote_request($newUrl, $args);
        $getStatus = json_decode(wp_remote_retrieve_body($data));

        if(isset($getStatus->locked) && $getStatus->locked == 1) {
          return 'true';
        } elseif(isset($getStatus->locked) && $getStatus->locked == 0) {
          return 'false';
        } else {
          return 'true';
        }
    }


    /**
     * Function to create table for assessment.
     *
     * @since   1.0.0
     */
    public function createTableForAssessment() {
        global $wpdb;
        
        $assessment_table_name = $wpdb->prefix.'assessments';
        $assessment_list_users_table_name = $wpdb->prefix.'list_users_assessments';

        $charset_collate = $wpdb->get_charset_collate();
        if ( $wpdb->get_var( "SHOW TABLES LIKE '{$assessment_table_name}'" ) != $assessment_table_name ) {
            $sql = "CREATE TABLE $assessment_table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                user_id int(20),
                first_name varchar(255),
                last_name varchar(255),
                email varchar(255),
                service_location varchar(255),
                account_id varchar(255),
                link_id varchar(255),
                report_id int(20),
                api_token varchar(255),
                gender varchar(255),
                company varchar(255),
                position_job varchar(255),
                password varchar(255),
                created_at varchar(255),
                updated_at varchar(255),
                status int(20),
                version int(11),
                assessment_result longtext,
                selected_all_that_apply longtext,
                assess_type mediumint(9),
                PRIMARY KEY  (id)
            ) $charset_collate;";
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );
        } else { 
            $this->tti_check_for_new_column_version();
        }

    }

    /**
     * Function to check the column for list users table.Function to 
     *
     * @since 1.0.0
    */
    public function tti_check_for_new_column_version() {
        global $wpdb;
        $result = array();
        $assessment_table_name = $wpdb->prefix.'assessments';

        $results = $wpdb->get_results( $wpdb->prepare(
          "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ",
          DB_NAME, $assessment_table_name, 'version'
        ) );

        $results_assess_type = $wpdb->get_results( $wpdb->prepare(
          "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ",
          DB_NAME, $assessment_table_name, 'assess_type'
        ) );

        if (empty( $column ) ) {
            $sql = $wpdb->get_results( "ALTER TABLE {$assessment_table_name} ADD version int(11) DEFAULT 1");
        }

        if (empty( $results_assess_type ) ) {
            $sql = $wpdb->get_results( "ALTER TABLE {$assessment_table_name} ADD assess_type int(11) DEFAULT 0");
        }

        // $resultssssss = $wpdb->get_results("SHOW INDEX FROM $assessment_table_name WHERE Key_name = 'user_id' OR 
        //   Key_name = 'email' OR Key_name = 'password' OR Key_name = 'link_id' OR Key_name = 'version' OR Key_name = 'assess_type'");

        $result_user = $wpdb->get_row("SHOW INDEX FROM $assessment_table_name WHERE Key_name = 'user_id'");
        $result_email = $wpdb->get_row("SHOW INDEX FROM $assessment_table_name WHERE Key_name = 'email'");
        $result_password = $wpdb->get_row("SHOW INDEX FROM $assessment_table_name WHERE Key_name = 'password'");
        $result_link_id = $wpdb->get_row("SHOW INDEX FROM $assessment_table_name WHERE Key_name = 'link_id'");
        $result_version = $wpdb->get_row("SHOW INDEX FROM $assessment_table_name WHERE Key_name = 'version'");
        

        if(!isset($result_user) && count($result_user) <= 0) {
            $wpdb->get_results( "ALTER TABLE {$assessment_table_name} ADD INDEX `user_id` (`user_id`)");
        }
        if(!isset($result_email) && count($result_email) <= 0) {
            $wpdb->get_results( "ALTER TABLE {$assessment_table_name} ADD INDEX `email` (`email`)");
        }
        if(!isset($result_password) && count($result_password) <= 0) {
            $wpdb->get_results( "ALTER TABLE {$assessment_table_name} ADD INDEX `password` (`password`)");
        }
        if(!isset($result_link_id) && count($result_link_id) <= 0) {
            $wpdb->get_results( "ALTER TABLE {$assessment_table_name} ADD INDEX `link_id` (`link_id`)");
        }
        if(!isset($result_version) && count($result_version) <= 0) {
            $wpdb->get_results( "ALTER TABLE {$assessment_table_name} ADD INDEX `version` (`version`)");
        }
        
    }


    /**
     * Function to create table for users limit for assessment functionality.
     *
     * @since   1.0.0
     */
    public function createTableForUsersLimit() {
        global $wpdb;
        $tti_users_limit = $wpdb->prefix.'tti_users_limit';
        $charset_collate = $wpdb->get_charset_collate();
        if ( $wpdb->get_var( "SHOW TABLES LIKE '{$tti_users_limit}'" ) != $tti_users_limit ) {
            $sql = "CREATE TABLE $tti_users_limit (
                ID int(11) NOT NULL AUTO_INCREMENT,
                `user_id` int(20),
                `email` varchar(200),
                `group_id` varchar(200),
                `limits` int(20),
                `data_link` varchar(255),
                PRIMARY KEY  (ID),
                KEY user_id (user_id),
                KEY email (email),
                KEY data_link (data_link)
            ) $charset_collate;";
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );
        }
    }
}