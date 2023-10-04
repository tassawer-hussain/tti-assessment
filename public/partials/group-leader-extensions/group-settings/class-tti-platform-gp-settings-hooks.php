<?php

/**
 * Class to handle ajax handling class for group registration settings.
 *
 * This is used to define internationalization, dashboard-specific hooks, and
 * public-facing site hooks.
 *
 * @since   1.6
 * @package     TTI_Platform
 * @subpackage  TTI_Platform/includes
 * @author  Presstigers
 */


 // add_filter('new_user_admin_notification_mail_to', 'new_user_admin_notification_mail_to_func', 10, 1);
  add_filter('wdm_group_enrollment_email_status', 'wdm_group_enrollment_email_status_func', 10, 2);
 
 // ldgr_filter_enroll_user_emails


/**
* Hook function to override re-invite emails.
* @since   1.6.5
* @access  public
*/
function wdm_group_enrollment_email_status_func($status, $group_id)  {
    global $links_id, $assess_ids;
    $status = true;
    $group_leader_id = get_current_user_id();
    
    /*** update user assessment assigned status ***/
    $assess_id = get_transient('group_dashboard_assess_id_'.$group_id);
    if(isset($assess_id[0])) {
        foreach ($_POST['wdm_members_email'] as $key => $user_email) {
            $user = get_user_by( 'email', $user_email );
            $user_id = $user->ID;
            update_user_meta( $user_id, 'assigned_group_'.$group_id.'_'.$group_leader_id.'_'.$assess_id[0], time(), true);
        }
    }
    /*** update user assessment assigned status ends ***/

    $sett_email_block = pt_check_block_email_setting($group_id, $group_leader_id);
    if($sett_email_block == 'true') {
        $status = false;
    }
    //var_dump($status);
    return $status;
}

/**
* Hook function to override re-invite emails.
* @since   1.6.5
* @access  public
*/
function pt_check_block_email_setting($group_id, $group_leader_id)  { 
    $keys = 'group_user_'.$group_leader_id.'_settings';
    //echo $keys.' ------- ';
    $data = get_user_meta($group_leader_id, $keys, true);
    //var_dump($data);
    return $data;
}
