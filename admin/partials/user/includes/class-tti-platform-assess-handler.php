<?php
/**
 * Class contains user assessment related functions
 *
 * This class is used to define main user related functionality in WordPress admin user's profile.
 *
 * @since   1.0.0
 * @package     TTI_Platform
 * @subpackage  TTI_Platform/includes
 * @author  Presstigers
 */


class TTI_Platform_Admin_User_Ass_Handler_Class {

    /**
    * String contains user id
    * @var string
    */
    public $user_id;
   

    /**
     * Constructor function to class initialize properties and hooks.
     *
     * @since       1.7.0
     */
    public function __construct() { 
      
    }
    
    /**
     * Function to show edit user assessment form
     *
     * @since   1.7.0
     */
    public function tti_show_edit_user_form( $user_id ) {
        $link_id = isset($_GET['link_id']) ? sanitize_text_field($_GET['link_id']) : false;
        if($link_id) {
            $list_data = $this->tti_return_assessments_by_link_id( $link_id, $user_id );
            require_once plugin_dir_path( __FILE__ ) . '../templates/tti-platform-template-ass-edit.php';
        }       
    }

    /**
     * Function to perform delete user action
     *
     * @since   1.7.0
     */
    public function tti_delete_user_assessment( $user_id ) {
        $link_id = isset($_GET['link_id']) ? sanitize_text_field($_GET['link_id']) : false;
        if($link_id) {
            $lists = get_user_meta( $user_id, 'user_assessment_data', true);
            if(count(unserialize($lists)) >= 1) {
               $lists = unserialize($lists);
               unset($lists[$link_id]);
               update_user_meta( $user_id, 'user_assessment_data', serialize($lists));
               return true;
            } 
        }
        return false;
    }

    /**
     * Function to return assessments by user id
     * @since   1.7.0
     */
    public function tti_return_assessments_curr_user( $user_id ) {
        $lists = get_user_meta( $user_id, 'user_assessment_data', true);
        if(isset($lists) && !empty($lists) && count(unserialize($lists)) >= 1) {
            return unserialize($lists);
        } 
        return false;
    }

     /**
     * Function to return assessments settings by user id
     * @since   1.7.0
     */
    public function tti_return_assessments_settings( $user_id ) {
        $settings_data = get_user_meta( $user_id, 'user_assessment_settings', true);
        if(isset($settings_data) && !empty($settings_data) && count(unserialize($settings_data)) >= 1) {
            return unserialize($settings_data);
        } 
        return false;
    }

    /**
     * Function to return assessments by user id
     * @since   1.7.0
     */
    public function tti_return_assessments_by_link_id ( $link_id, $user_id ) {
        $lists = get_user_meta( $user_id, 'user_assessment_data', true);
        if(isset($lists) && !empty($lists) && count(unserialize($lists)) >= 1) {
           $lists = unserialize($lists);
           return $lists[$link_id];
        } 
        return false;
    }

}
