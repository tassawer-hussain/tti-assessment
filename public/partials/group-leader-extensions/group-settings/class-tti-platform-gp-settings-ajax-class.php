<?php

/**
 * Class to handle ajax handling class for group registration settings.
 *
 * This is used to define internationalization, dashboard-specific hooks, and
 * public-facing site hooks.
 *
 * @since   1.6.5
 * @package     TTI_Platform
 * @subpackage  TTI_Platform/includes
 * @author  Presstigers
 */


class TTI_Platform_Group_Leader_Settings_Ajax {

   
     /**
    * Contains group leader id
    * @var integer
    */
    public $group_leader_id;

     /**
    * Contains group id
    * @var integer
    */
    public $group_id;

    /**
     * Define the core functionality of the plugin for frontend.
     *
     * @since       1.6.5
     * @param $user_id integer contains user id
     * @param $group_leader_id integer contains geoup leader id
     */
    public function __construct($group_leader_id, $group_id) {
        $this->group_leader_id = $group_leader_id;
        $this->group_id = $group_id;
    }


     /**
     * Function to save the group dashboard settings.
     * @since   1.6.5
     * @access  public
     */
    public function tti_group_save_settings($block_email)  { 
        
        $key = 'group_user_'.$this->group_leader_id.'_settings';
        $settings = get_user_meta(get_current_user_id(), $key, true);
        
        update_user_meta( $this->group_leader_id, $key, $block_email);
        
        $resp = array (
            'status' => 1,
        );
        echo json_encode($resp);
        exit;   
    }

   

}
