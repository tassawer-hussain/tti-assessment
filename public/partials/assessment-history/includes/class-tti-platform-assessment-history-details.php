<?php

/**
 * Class contains current user assessment history functions.
 *
 * This is used to define internationalization, dashboard-specific hooks, and
 * public-facing site hooks.
 *
 * @since   1.6
 * @package     TTI_Platform
 * @subpackage  TTI_Platform/includes
 * @author  Presstigers
 */

class TTI_Platform_Assess_History_Details {

    /**
     * Define the core functionality of the plugin for frontend.
     *
     * @since       1.6
     */
    public function __construct() {
        
    }

    /**
     * Function to get user assessment details by user id.
     *
     *
     * @since   1.6
     * @param integer $user_id contains user id
     * @param string $link_id contains assessement link id
     * @access  public 
     * @return  array|boolean returns assessment history data
     */
    public function get_user_details($user_id, $link_id)  {
        global $wpdb;
        $version_assess = 1;
        $assessment_table_name = $wpdb->prefix.'assessments';
        /* Query to count version */
        $results = $wpdb->get_results( 
            "SELECT created_at, first_name, last_name, email, gender, company, position_job, version FROM $assessment_table_name WHERE user_id ='$user_id' AND link_id='$link_id' AND status = 1"
        );
        if(isset($results) && count($results) > 0) {
            return $results;
        }
        return false;
    }

     /**
     * Function to generate random string.
     *
     *
     * Random string generator
     * @since   1.6
     * @access  public
     * @param  integer $length contains length of string
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
