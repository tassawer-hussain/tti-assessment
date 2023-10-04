<?php

/**
 * Class for complete profiles users functionality
 *
 * This is used to define internationalization, dashboard-specific hooks, and
 * public-facing site hooks.
 *
 * @since   1.5.1
 * @package     TTI_Platform
 * @subpackage  TTI_Platform/includes
 * @author  Presstigers
 */



class TTI_Platform_Cp_Users {

    /**
    * Array contains user data
    * @var array
    */
    public $users_data;

    /**
    * Array contains user details
    * @var array
    */
    public $users_details;

    /**
    * Current user id
    * @var integer
    */
    public $current_user_id;

    /**
     * Define the core functionality of the plugin for frontend.
     *
     * @since       1.5.1
     */
    public function __construct() {
         global $current_user;
         $this->users_data = array();
         $this->users_details = array();
         wp_get_current_user();
         $this->current_user_id = $current_user->ID;
    }

     /**
     * Function to get current leader users data.
     *
     * @since       1.5.1
     */
    public function init_get_users_data() { 
        $this->users_data = $this->get_group_user_details();
        if(count($this->users_data) > 0) {
            $this->get_assessments_users_data();
        }
    }

     /**
     * Function to get current leader users data.
     *
     * @since       1.5.1
     */
    public function get_assessments_users_data() {
         $this->get_all_user_details();
    }   

     /**
     * Function to get current leader users data.
     *
     * @since       1.5.1
     */
    public function get_all_user_details() {
        foreach ($this->users_data as $uid => $us_id) {
            $this->get_single_user_details($us_id);
            
        }
        // $this->get_single_user_details2();
        //echo '<pre>';print_r($this->users_details);'</pre>'; exit();
    } 

    /**
    *  Function to get user assessment latest version.
    *
    * @since   1.6
    * @param integer $c_usrid contains current user id
    * @param string $link_id contains assessment link id
    * @return integer returns count of total assessments of given user
    */
    public function get_current_user_assess_version($c_usrid, $link_id) {
        global $wpdb;
        $results = array('one');
        $assessment_table_name = $wpdb->prefix.'assessments';
        $results = $wpdb->get_results("SELECT * FROM $assessment_table_name WHERE user_id ='$c_usrid' AND link_id='$link_id'");
        if(isset($results) && count($results) > 0){
            return count($results);
        } 
        return count($results);
    }

     /**
     * Function to get user details from assessments table.
     *
     * @since       1.5.1
     * @param integer $us_id contains user id
     */
    public function get_single_user_details($us_id) {
        global $wpdb;

        $assessment_table_name = $wpdb->prefix.'assessments';
        $assessment_user_table = $wpdb->prefix.'list_users_assessments';
        
        $results = $wpdb->get_results("SELECT * FROM $assessment_table_name WHERE user_id = '".$us_id."'");


        foreach ($results as $key => $value) {
            if(
                isset($value->first_name) &&
                !empty($value->first_name) &&
                isset($value->last_name) &&
                !empty($value->last_name) &&
                isset($value->link_id) &&
                !empty($value->link_id) &&
                !empty($value->report_id) &&
                isset($value->api_token) &&
                !empty($value->api_token) &&
                isset($value->service_location) &&
                !empty($value->service_location) &&
                isset($value->created_at) &&
                !empty($value->created_at) &&
                isset($value->assessment_result) && 
                !empty($value->assessment_result)
            ) { 
                $this->users_details[] = $value;
            }
        }
    } 


     /**
     * Function to get user details from assessments table.
     *
     * @since       1.5.1
     */
    public function get_single_user_details2() {
        global $wpdb;
        $assessment_user_table = $wpdb->prefix.'list_users_assessments';
        $results = $wpdb->get_results("SELECT * FROM $assessment_user_table WHERE user_id = '".$this->current_user_id."'");

        foreach ($results as $key => $value) {
            if(
                isset($value->first_name) &&
                !empty($value->first_name) &&
                isset($value->last_name) &&
                !empty($value->last_name) &&
                isset($value->link_id) &&
                !empty($value->link_id) &&
                !empty($value->report_id) &&
                isset($value->created_at) &&
                !empty($value->created_at) &&
                isset($value->assess_user_save_data_assessment) && 
                !empty($value->assess_user_save_data_assessment)
            ) { 
                $this->users_details[] = $value;
            }
        }
    } 

     /**
     * Function to check if current user is group leader or not.
     *
     * @since       1.5.1
     * @return boolean|array return group emails
     */
    public function get_group_user_details() {
        $group_ids = array();
        $group_leaders_emails = array();
      
        $current_user_id = $this->current_user_id;
        $group_leader_status = learndash_is_group_leader_user($current_user_id);
        if($group_leader_status) {
            $group_ids = learndash_get_administrators_group_ids( $current_user_id );
            if(count($group_ids) > 0) {
                $group_leaders_emails = $this->get_user_details($group_ids);
                return $group_leaders_emails;
            }
        } 
        return false;
    }

    /**
     * Function to get emails of all users in a group.
     *
     * @since       1.5.1
     * @param array $group_ids contains array of group id
     * @return array return array of group emails
     */
    public function get_user_details($group_ids) {
        $group_leaders_emails = array();
        foreach ($group_ids as $k => $v) {
            $key = 'learndash_group_users_'.$v;
            $users = get_users(array(
                'meta_key'     => $key,
            ));
            if(!empty($users)) {
                foreach ($users as $key => $userss) {
                    $group_leaders_emails[] = $userss->ID;
                }
            }
        }
        $group_leaders_emails = array_unique($group_leaders_emails);
        return $group_leaders_emails;
    }

}
