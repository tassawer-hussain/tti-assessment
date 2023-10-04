<?php
/**
 * Class to contain functionality of group leader plugin emails customizations.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since       1.6.3
 * @package     TTI_Platform
 * @subpackage  TTI_Platform/includes
 * @author      presstiger <support@presstigers.com>
 */
class TTI_Platform_Emails_Gl_Class
{

    public $subject_invite_cc;

    public $body_invite_cc;

    public $subject_new_user_cc;

    public $body_new_user_cc;

    public $leader_id;

    public $group_id;

    /**
     * Define the constructor
     *
     * @since  1.6.3
     */
    public function __construct()
    {
        $this->leader_id = get_current_user_id();

        /* Re-invite hooks group leader plugin filters */
       // add_filter('wdm_send_reinvite_email_status', array($this, 'wdm_send_reinvite_email_status_func'), 10, 2);
        add_filter('wdm_reinvite_email_subject', array(
            $this,
            'tti_email_reinvite_init_subject'
        ) , 10, 4);
        add_filter('wdm_reinvite_email_body', array(
            $this,
            'tti_email_reinvite_init_body'
        ) , 10, 4);

         /* New Group Registration hooks group leader plugin filters */
        // add_filter('wdm_group_email_subject', array(
        //     $this,
        //     'tti_email_new_user_init_subject'
        // ) , 10, 3);
        // add_filter('wdm_group_email_body', array(
        //     $this,
        //     'tti_email_new_user_init_body'
        // ) , 10, 3);

         add_filter('ldgr_filter_enroll_user_emails', array($this, 'ldgr_filter_enroll_user_emails_func'), 10, 2);

      
    }

    /** 
     * Function to get email subject.
     *
     * @since  1.6.5
     * @param string $status contains reinvite email status
     * @param integer $group_id contains group id
     * @return string returns email subject
     */
    public function wdm_send_reinvite_email_status_func($status, $group_id)
    {
        return true;
    }


    /** 
     * Function to get email subject.
     *
     * @since  1.6.3
     * @param string $subject contains subject content
     * @param integer $group_id contains group id
     * @param array $member_user_id contains member user id
     * @return string returns email subject
     */
    public function tti_email_new_user_init_subject($subject, $group_id, $member_user_id)
    {
        /* Email controlling class for admin */
        $this->group_id = $group_id;
        $this->subject_new_user_cc = $subject . ' [COPY]';
        return $subject;
    }

    /**
     * Function to get email body.
     *
     * @since  1.6.3
     * @param string $body contains email body
     * @param integer $group_id contains group id
     * @param array $member_data contains member data in array
     * @return string return final email body
     */
    public function tti_email_new_user_init_body($body, $group_id, $member_data)
    {
        /* Email controlling class for admin */
        $this->body_new_user_cc = '<h3>THIS IS A COPY OF THE ASSIGNMENT EMAIL YOU SENT</h3> <br> ' . $body;
        $this->cc_the_leader_new_user();
        return $body;
    }

    /**
     * Function to sent email to new user.
     *
     * @since  1.6.3
     */
    public function cc_the_leader_new_user()
    {
        $user_leader_data = get_user_by('id', $this->leader_id);
        if (isset($user_leader_data->user_email) && !empty($user_leader_data->user_email))
        {
            $send_to = $user_leader_data->user_email;

            $message = $this->body_new_user_cc;
            $subject = $this->subject_new_user_cc;
            $attachments = '';
            $headers = '';
            /* CC group leader */
            // if(!empty($leader_email)) {
            //     $headers[] = 'Cc: '.$leader_email;
            // }
            if (class_exists('WooCommerce'))
            { // If WooCommerce
                global $woocommerce;
                $mailer = $woocommerce->mailer();
                $message = $mailer->wrap_message($subject, $message);
                $mailer->send($send_to, $subject, $message, $headers, $attachments);
            }
            elseif (class_exists('EDD_Emails'))
            { //If EDD
                EDD()
                    ->emails
                    ->send($send_to, $subject, $message, $attachments);
            }
            else
            {
                $sent = wp_mail($send_to, $subject, $message, $headers, $attachments);
            }
        }
    }

    /**
     * Function to get email subject.
     *
     * @since  1.6.3
     * @param string $subject contains reinvite email subject
     * @param array $group_id contains group id
     * @param integer $current_id contains current course id
     * @param integer $user_id contains user id
     * @return string returns final subject
     */
    public function tti_email_reinvite_init_subject($subject, $group_id, $current_id, $user_id)
    {
        /* Email controlling class for admin */
        $this->subject_invite_cc = $subject .' [COPY]';
        return $subject;
    }

    /**
     * Function to get email body.
     *
     * @since  1.6.3
     * @param string $body contains reinvite email body
     * @param array $group_id contains group id
     * @param integer $current_id contains current course id
     * @param integer $user_id contains user id
     * @return string return final reinvite body
     */
    public function tti_email_reinvite_init_body($body, $group_id, $current_id, $user_id)
    {
        /* Email controlling class for admin */
        $this->body_invite_cc = '<h3>THIS IS A COPY OF THE ASSIGNMENT EMAIL YOU SENT</h3> <br> '.$body;
        $this->cc_the_leader($group_id);
        return $body;
    }

    /**
     * Function to show email options.
     *
     * @since  1.6.3
     */
    public function cc_the_leader($group_id)
    {

        $user_leader_data = get_user_by('id', $this->leader_id);
        if (isset($user_leader_data->user_email) && !empty($user_leader_data->user_email))
        {
            $send_to = $user_leader_data->user_email;

            $message = $this->body_invite_cc;
            $subject = $this->subject_invite_cc;
            $attachments = '';
            $headers = '';
            /* CC group leader */
            // if(!empty($leader_email)) {
            //     $headers[] = 'Cc: '.$leader_email;
            // }
            if (class_exists('WooCommerce'))
            { // If WooCommerce
                global $woocommerce;
                $mailer = $woocommerce->mailer();
                $message = $mailer->wrap_message($subject, $message);
                $mailer->send($send_to, $subject, $message, $headers, $attachments);
            }
            elseif (class_exists('EDD_Emails'))
            { //If EDD
                EDD()
                    ->emails
                    ->send($send_to, $subject, $message, $attachments);
            }
            else
            {
                $sent = wp_mail($send_to, $subject, $message, $headers, $attachments);
            }

            /* Check status of block email setting status */
            if($this->wdm_send_reinvite_email_check_status_func($group_id) == true) {
                echo json_encode( array( 'success' => __( 'Re Invitation mail has been sent successfully.', 'wdm_ld_group' )));
                exit;
            }
        }
    }

    /**
    * Hook function to override re-invite emails.
    * @since   1.6.5
    * @access  public
    */
    public function wdm_send_reinvite_email_check_status_func($group_id)  { 
        // echo '---------sadsadasd--------';
        $status = false;
        $sett_email_block = $this->pt_check_block_email_setting($group_id);
        if($sett_email_block == 'true') {
            $status = true;
        }
        // var_dump($status);
        return $status;
    }


    /**
    * Hook function to override re-invite emails.
    * @since   1.6.5
    * @access  public
    */
    public function pt_check_block_email_setting($group_id)  { 
        $keys = 'group_user_'.$this->leader_id.'_settings';
        // echo $keys.' ------- ';
        $data = get_user_meta($this->leader_id, $keys, true);
        // var_dump($data);
        return $data;
    }

        /**
         * Send bulk upload emails
         *
         * @param array $all_emails_list     List of all emails to send emails to.
         * @param int   $group_id            ID of the group.
         */
    public function ldgr_filter_enroll_user_emails_func($all_emails_list, $group_id) {
        foreach ( $all_emails_list as $user_id => $details ) {
            if ( $details['new'] ) {
                if ( apply_filters( 'is_ldgr_default_user_add_action', true ) ) {
                    $success_data = $this->pt_new_registration (
                            $user_id,
                            $details['user_data']['first_name'],
                            $details['user_data']['last_name'],
                            $details['user_data']['user_email'],
                            $details['user_data']['user_pass'],
                            $details['courses'],
                            $details['lead_user'],
                            $details['group_id']
                        );
                }
            } else {
                global $wpdb;
                $meta_key = 'learndash_group_leaders_'.$group_id;
                $sql_str = $wpdb->prepare(
                    "SELECT user_id FROM ". $wpdb->usermeta ." 
                    WHERE meta_key = '$meta_key'" );
                $group_leaders = $wpdb->get_results($sql_str);
                
                $send_to = array();
                if( $group_leaders ):
                    foreach ($group_leaders as $leader ):
                        $user_info  = get_userdata( $leader->user_id );
                        $send_to[] = $user_info->user_email;
                    endforeach;
                endif;
                $send_to = implode(",", $send_to);
                
                ldgr_send_group_mails(
                    $send_to,
                    $details['subject'] . ' [COPY]',
                    '<h3>THIS IS A COPY OF THE ASSIGNMENT EMAIL YOU SENT</h3> <br> ' . $details['body'],
                    array(),
                    array(),
                    array(
                        'email_type' => 'WDM_U_ADD_GR_BODY',
                        'group_id'   => $group_id,
                    )
                );
            }
        }

    return $all_emails_list;
}
    /**
         * Register new user and enroll in group
         *
         * @param int    $member_user_id   ID of the user to register and enroll.
         * @param string $f_name           First name of the user.
         * @param string $l_name           Last name of the user.
         * @param string $val              Email of the user.
         * @param string $password         Password of the new user.
         * @param array  $courses          List of courses to enroll in.
         * @param obj    $lead_user        Group leader.
         * @param int    $group_id         ID of the group.
         *
         * @return string               Status of the newly enrolled user.
         */
    public function pt_new_registration( $member_user_id, $f_name, $l_name, $val, $password, $courses, $lead_user, $group_id ) {
    if ( ! is_wp_error( $member_user_id ) ) {
                $subject = get_option( 'wdm-u-ac-crt-sub' );
                if ( empty( $subject ) ) {
                    $subject = WDM_U_AC_CRT_SUB;
                }
                $subject = stripslashes( $subject );
                $subject = str_replace( '{group_title}', get_the_title( $group_id ), $subject );
                $subject = str_replace( '{site_name}', get_bloginfo(), $subject );
                $subject = str_replace( '{user_first_name}', '', $subject );
                $subject = str_replace( '{user_last_name}', '', $subject );
                $subject = str_replace( '{user_email}', '', $subject );
                $subject = str_replace( '{user_password}', '', $subject );
                $subject = str_replace( '{course_list}', '', $subject );
                // $subject = str_replace( '{group_leader_name}', ucfirst( strtolower( $lead_user->first_name ) ) . ' ' . ucfirst( strtolower( $lead_user->last_name ) ), $subject );
                $subject = str_replace( '{group_leader_name}', $lead_user->first_name . ' ' . $lead_user->last_name , $subject );
                $subject = str_replace( '{login_url}', '', $subject );

                $enrolled_course = array();
                foreach ( $courses as $key => $value ) {
                    $enrolled_course[] = get_the_title( $value );
                    $url               = get_permalink( $value );
                    unset( $key );
                }

                $tbody = get_option( 'wdm-u-ac-crt-body' );
                if ( empty( $tbody ) ) {
                    $tbody = WDM_U_AC_CRT_BODY;
                }
                $body = stripslashes( $tbody );

                $body = str_replace( '{group_title}', get_the_title( $group_id ), $body );
                $body = str_replace( '{site_name}', get_bloginfo(), $body );
                $body = str_replace( '{user_first_name}', ucfirst( $f_name ), $body );
                $body = str_replace( '{user_last_name}', ucfirst( $l_name ), $body );
                $body = str_replace( '{user_email}', $val, $body );
                $body = str_replace( '{user_password}', $password, $body );
                $body = str_replace( '{course_list}', $this->pt_get_course_list_html( $enrolled_course ), $body );
                // $body = str_replace( '{group_leader_name}', ucfirst( strtolower( $lead_user->first_name ) ) . ' ' . ucfirst( strtolower( $lead_user->last_name ) ), $body );
                $body = str_replace( '{group_leader_name}', $lead_user->first_name . ' ' . $lead_user->last_name , $body );
                $body = str_replace( '{login_url}', wp_login_url(), $body );

                $this->body_new_user_cc = '<h3>THIS IS A COPY OF THE ASSIGNMENT EMAIL YOU SENT</h3> <br> ' . $body;
                $this->subject_new_user_cc = $subject . ' [COPY]';

                $this->cc_the_leader_new_user();
        }
    }

    /**
    * Get course list HTML
    *
    * @param array $course_list    List of courses to display.
    * @return string               HTML list of courses.
    */
     public function pt_get_course_list_html( $course_list ) {
            $return = '';
            if ( ! empty( $course_list ) ) {
                $return = '<ul>';
                foreach ( $course_list as $course ) {
                    $return .= '<li>' . $course . '</li>';
                }
                $return .= '</ul>';
            }
            return $return;
        }

}

