<?php

/**
 * Class to handle ajax handling class for group registration.
 *
 * This is used to define internationalization, dashboard-specific hooks, and
 * public-facing site hooks.
 *
 * @since   1.6
 * @package     TTI_Platform
 * @subpackage  TTI_Platform/includes
 * @author  Presstigers
 */


class TTI_Platform_Group_Leader_Ajax {

    /**
    * User id
    * @var integer
    */
    public $user_id;

    /**
    * Group id
    * @var integer
    */
    public $group_id;

    /**
    * User email
    * @var string
    */
    public $email;

    /**
    * Content array contains content ids
    * @var array
    */
    public $content_ids;

    /**
    * Contains links ids
    * @var array 
    */
    public $link_ids;

    /**
    * Contains group courses ids
    * @var array
    */
    public $group_courses;

    /**
    * Contains group limit
    * @var integer
    */
    public $group_limit;

     /**
    * Contains link id
    * @var string
    */
    public $link_id;

     /**
    * Contains group leader id
    * @var integer
    */
    public $group_leader_id;

    /**
     * Define the core functionality of the plugin for frontend.
     *
     * @since       1.6
     * @param $user_id integer contains user id
     * @param $email string contains email address
     * @param $group_id integer contains group id related to user
     * @param $link_id string contain assessment link id
     * @param $group_leader_id integer contains geoup leader id
     */
    public function __construct($user_id, $email, $group_id, $link_id, $group_leader_id) {
        $this->content_ids = array();
        $this->link_ids = array();
        $this->group_courses = array();
        $this->user_id = $user_id;
        $this->email = $email;
        $this->group_id = $group_id;
        $this->link_id = $link_id;
        $this->group_leader_id = $group_leader_id;
    }


     /**
     * Function to starts the retake assessment process.
     * @since   1.6
     * @access  public
     */

    public function retake_assessment_process(  )  { 
        global $wpdb;
        $status = 0;
        
        /* Check group limit first */
        $group_leader_limit = $this->check_group_limit();
        //var_dump($group_leader_limit); exit();
        /* Proceed if there is user registration limit left */
        if($group_leader_limit) {
            $this->process_users_limit();
            $message = __('User can now take assessment again.', 'tti-platform');
            $status = 1; 

            $this->send_email_to_user();
        } else {
            $message = __("This group don't have any user registration left. Please buy more registrations before allow user to retake assessment.", 'tti-platform');
        }
        
        $resp = array (
            'message' => $message,
            'status' => $status,
        );
        echo json_encode($resp);
       
       exit;
    }

    /**
    * Function to send email to user for retaking assessment.
    *
    * @since   1.6
    */
    public function send_email_to_user() {
        if(isset($this->user_id) && isset($this->email) && $this->link_id) {
            $ass_id = $this->get_post_id_by_link_id();
            $title = get_the_title($ass_id);

            /* update who assigned the assessment */
            update_user_meta ( 
                $this->user_id, 
                'assigned_group_'.$this->group_id.'_'.$this->group_leader_id.'_'.$ass_id, 
                time(), true
            );

            $user_data = get_user_by( 'id', $this->user_id );
            $user_leader_data = get_user_by( 'id', $this->group_leader_id );

            $user_leader_email = $user_leader_data->user_email;
            
            $m_subject = $this->get_mail_subject($title, $user_leader_data->first_name.' '.$user_leader_data->last_name, $user_data);
            
            $m_body = $this->get_mail_body($title, $user_leader_data->first_name.' '.$user_leader_data->last_name, $user_data);
            


            $this->send_retake_assess_mail($m_subject, $m_body, $user_data, $user_leader_data, $user_leader_email);
            
        }
    }  

    /**
    * Function to send mail to user for retake assessment notification.
    *
    * @since   1.6
    */
    public function send_retake_assess_mail ($subject, $body, $user_data, $user_leader_data, $leader_email = '') {
        $email = get_option('admin_email');
        if(isset($user_data->user_email) && !empty($user_data->user_email)) {
            $to = $user_data->user_email;
           
            $send_to = $to;
            $message = $body;
            $attachments = '';
            $headers[]  = 'Reply-To: '.$user_leader_data->first_name.' '.$user_leader_data->last_name.' <'.$leader_email.'>';
            
            if($this->wdm_send_reinvite_email_check_status_func() != true) {
                 if (class_exists('WooCommerce')) {  // If WooCommerce
                    global $woocommerce;
                    $mailer = $woocommerce->mailer();
                    $message = $mailer->wrap_message($subject, $message);
                    $mailer->send($send_to, $subject, $message, $headers, $attachments);
                } elseif (class_exists('EDD_Emails')) { //If EDD
                    EDD()->emails->send($send_to, $subject, $message, $attachments);
                } else {
                    $sent = wp_mail($send_to, $subject, $message, $headers, $attachments);
                }
            }

            //$sent = wp_mail($to, $subject, $body, $headers);
            
            /* CC group leader */
            $headers = '';
            if(!empty($leader_email)) { 
                $subject = $subject. ' [COPY]';
                $message = '<h3>THIS IS A COPY OF THE ASSIGNMENT EMAIL YOU SENT</h3> <br><br> '.$body;
                //$headers .= 'Cc: '.$leader_email;
                 if (class_exists('WooCommerce')) {  // If WooCommerce
                    
                    global $woocommerce;
                    $mailer = $woocommerce->mailer();
                    $message = $mailer->wrap_message($subject, $message);
                    $mailer->send($leader_email, $subject, $message, $headers, $attachments);
                } elseif (class_exists('EDD_Emails')) { //If EDD
                    EDD()->emails->send($leader_email, $subject, $message, $attachments);
                } else {
                    $sent = wp_mail($leader_email, $subject, $message, $headers, $attachments);
                }
            }
            
            if($sent) {
                // if email sent successfully
            } else  {
                // if email sent failed
            }
        }
    }

    /**
    * Hook function to override re-invite emails.
    * @since   1.6.5
    * @access  public
    */
    public function wdm_send_reinvite_email_check_status_func()  { 
        $status = false;
        $sett_email_block = $this->pt_check_block_email_setting();
        if($sett_email_block == 'true') {
            $status = true;
        }
        return $status;
    }


    /**
    * Hook function to override re-invite emails.
    * @since   1.6.5
    * @access  public
    */
    public function pt_check_block_email_setting()  { 
        $keys = 'group_user_'.$this->group_leader_id.'_settings';
        $data = get_user_meta($this->group_leader_id, $keys, true);
        return $data;
    }

    /**
    * Function to get mail subject.
    *
    * @since   1.6
    * @param string $title contains title for subject
    * @param string $group_leader_name contains group leader name
    * @param array $user_data contains user data related to there assessments
    * @return string returns subject for email
    */
    public function get_mail_subject($title, $group_leader_name, $user_data) {
        $tsub = get_option( 'wdm-gr-retake-assessment' );
        $subject = stripslashes( $tsub );
        if(empty($subject)) {
            $subject = 'Retake Assessment';
        }
        $subject = str_replace( '{assessment_title}', $title, $subject );
        $subject = str_replace( '{site_name}', get_bloginfo(), $subject );
        $subject = str_replace( '{user_first_name}', '', $subject );
        $subject = str_replace( '{user_last_name}', '', $subject );
        $subject = str_replace( '{user_email}', '', $subject );
        $subject = str_replace( '{group_leader_name}', $group_leader_name, $subject );
        $subject = str_replace( '{login_url}', '', $subject );

        return $subject;
    }


    /**
    * Function to get mail body.
    *
    * @since   1.6
    * @param string $title  contains group title
    * @param string $group_leader_name contains group leader name
    * @param array $user_data contains user data
    * @return string returns email body
    */
    public function get_mail_body($title, $group_leader_name, $user_data) {
        $tbody = get_option( 'wdm-u-add-gr-body-retake-assess' );
        
        $key = get_password_reset_key($user_data);
        
        $user_login = $user_data->user_login;
        
        $reset_arg = array(
            'action'=>'rp',
            'key'=>$key,
            'login' => rawurlencode($user_login)
        );

        $reset_password_link = add_query_arg( $reset_arg, network_site_url( 'wp-login.php', 'login' ) );

        $enrolled_course = array();
        $courses = $this->get_current_group_coursess();
        $enrolled_course_output = '';
        if(count($courses) > 0) {
            $enrolled_course_output .= '<ul>';
        }
        
        foreach ($courses as $key => $value) {
            $enrolled_course[] = get_the_title($value);
            $url = get_permalink($value);
            $enrolled_course_output .= '<li>'.get_the_title($value).'</li>';
            unset($key);
        }

        if(count($courses) > 0) {
            $enrolled_course_output .= '</ul>';
        }
        
        $body = stripslashes( $tbody );
        $body = str_replace( '{assessment_title}', $title, $body );
        $body = str_replace("{reset_password}", $reset_password_link , $body);
        $body = str_replace( '{site_name}', get_bloginfo(), $body );
        $body = str_replace( '{user_first_name}', ucfirst( $user_data->first_name ), $body );
        $body = str_replace( '{user_last_name}', ucfirst( $user_data->last_name ), $body );
        $body = str_replace( '{user_email}', $user_data->user_email, $body );
        $body = str_replace( '{group_leader_name}', $group_leader_name, $body );
        $body = str_replace( '{login_url}', wp_login_url(), $body );
        $body = str_replace("{course_list}", $enrolled_course_output , $body);


        return $body;
    }


    /**
    * Function to get group courses.
    *
    * @since   1.6
    */
    public function get_current_group_coursess() {
        global $wpdb;
        $course_ids = array();
        $key = 'learndash_group_enrolled_'.$this->group_id;
        $meta = $wpdb->get_results("SELECT * FROM `".$wpdb->postmeta."` WHERE meta_key='".$wpdb->escape($key)."'");
        foreach ($meta as $key => $value) {
            if(isset($value->post_id) && !empty($value->post_id)) {
                $course_ids[] = $value->post_id;
            }
        }
        return $course_ids;
    }

    /**
    * Function to Get link id by post id.
    *
    * @since   1.6
    * @return boolean returns link id
    */
    public function get_post_id_by_link_id() {
        global $wpdb;
        $tbl = $wpdb->prefix.'postmeta';
        $prepare_guery = $wpdb->prepare( "SELECT post_id FROM $tbl where meta_key ='link_id' and meta_value = '%s'", $this->link_id );
        $get_values = $wpdb->get_col( $prepare_guery );
        if(isset($get_values[0])) {
            return $get_values[0];
        }
        return false;
    }

    /**
    * Function to get current group courses.
    *
    * @since   1.6
    */
    public function process_users_limit() {
        global $wpdb;
        $exists_in_db = false;
        $limit = 0;
        $usrID = $this->user_id;
        $group_id = $this->group_id;
        $link_id = $this->link_id;
        $users_limit = $wpdb->prefix.'tti_users_limit';

        //foreach ($this->link_ids as $key => $value) {  // $value contains link id
               
                // data_link is link_id
                $results = $wpdb->get_row(
                    "SELECT * FROM $users_limit WHERE user_id ='$usrID' AND data_link = '$link_id'"
                );
                if($results->data_link == $link_id) {
                    $exists_in_db = true;
                    $limit = $results->limits;
                }else {
                    $limit = 1;
                    $assessment_table_name = $wpdb->prefix.'assessments';
                    $results = $wpdb->get_row("SELECT * FROM $assessment_table_name WHERE user_id ='$usrID' AND link_id='$link_id' AND status = 1");
                    
                    if(isset($results) && !empty($results)) {
                        
                    } else {
                       
                            $limit = 2;
                            
                        
                    }
                    
                }

               
        //}
        if($exists_in_db && !empty($link_id)) { 
            /* Update the limit */
            $group_ids = $results->group_id;
            if (strpos($results->group_id, $this->group_id) === false) {
               $group_ids .= ',' . $this->group_id;
            }
            $this->update_user_limit($limit, $link_id, $group_ids);
        } else {
            /* Insert data with limit */
            $this->add_user_limit($limit, $link_id);
        }
    }

    /**
    * Function to update user limit by one.
    *
    * @since   1.6
    * @param integer $limit contains user assessment limit
    * @param string $link_id contains assessment link id
    * @param array $group_ids contains group ids related to current selected course
    */
    public function update_user_limit($limit, $link_id, $group_ids) { 
        global $wpdb;
        $users_limit = $wpdb->prefix.'tti_users_limit';
        
        $updateQuery = $wpdb->update (
            $users_limit, 
            array (
                'limits' => $limit + 1,
                'group_id' => $group_ids
            ), 
            array (
                'user_id' => $this->user_id,
                'data_link' => $link_id
            )
         );

        if($updateQuery) {
            $this->group_limit--;
            $this->reduce_group_limit();
        }
    }

    /**
    * Function to add user limit by one.
    *
    * @since   1.6
    * @param integer $limit contains user assessment limit
    * @param sting $link_id contains assessment link id
    */
    public function add_user_limit($limit, $link_id) {
        global $wpdb;
        $users_limit = $wpdb->prefix.'tti_users_limit';
        $insertQuery = $wpdb->insert (
            $users_limit, 
            array (
               "user_id" => $this->user_id,
               "email" => $this->email,
               "group_id" => $this->group_id,
               "limits" => $limit,
               "data_link" => $link_id
            ),
            array ( '%d', '%s', '%s', '%d', '%s' )
         );
        if($insertQuery) {
            $this->group_limit--;
            $this->reduce_group_limit();
        }
    }    

    /**
    * Function to reduce group limit.
    *
    * @since   1.6
    */
    public function reduce_group_limit() {
        $key = 'wdm_group_users_limit_' . $this->group_id;
        $this->group_limit = isset($this->group_limit) && $this->group_limit < 0 ? 0 : $this->group_limit; 
        update_post_meta( $this->group_id, $key, $this->group_limit);
    }

    /**
    * Function to get current group courses.
    *
    * @since   1.6
    * @return array return current user course ids
    */
    public function get_current_group_courses() {
        global $wpdb;
        $course_ids = array();
        $key = 'learndash_group_enrolled_'.$this->group_id;
        $meta = $wpdb->get_results("SELECT * FROM `".$wpdb->postmeta."` WHERE meta_key='".$wpdb->escape($key)."'");
        foreach ($meta as $key => $value) {
            if(isset($value->post_id) && !empty($value->post_id)) {
                $course_ids[] = $value->post_id;
            }
        }
        return $course_ids;
    }

    /**
    * Function to get assessment links id by course id.
    *
    * @since   1.6
    * @param integer $c_id contains course id
    */
    public function get_contents_post_id_by_cou_id($c_id) {
        global $wpdb;
        $course_content_posts = array();
        $key = 'ld_course_'.$c_id;
        $meta = $wpdb->get_results("SELECT * FROM `".$wpdb->postmeta."` WHERE meta_key='".$wpdb->escape($key)."'");
        foreach ($meta as $key => $value) {
            if(isset($value->post_id) && !empty($value->post_id)) {
                $this->content_ids[] = $value->post_id;
            }
        }
    }

    /**
    * Function to get post content.
    *
    * @since   1.6
    * @param integer $content_id contains post content id
    */
    public function get_links_id_by_content_id($content_id) {
        $content_post = get_post($content_id);
        $link_ids = array();
        if(isset($content_post->post_content)) {
            $content = $content_post->post_content;
            $content = wpautop( $content_post->post_content );
            $this->match_all_assessment_ids($content);
        }
    }

    /**
    * Function to check if assessment id exists.
    *
    * @since   1.6
    * @param string $content contains shortcode content to match with post content
    */
    public function match_all_assessment_ids($content) {
        $args = array( 'post_type' => 'tti_assessments');
        $loop = new WP_Query( $args );
        while ( $loop->have_posts() ) : $loop->the_post();
            $searc_string = '[take_assessment assess_id="'.get_the_id().'"';
            if (strpos($content, $searc_string) !== false) {
                $this->link_ids[] = get_post_meta( get_the_id(), 'link_id', true );
            }
        endwhile;
    }

    /**
    * Function to check current group limit.
    *
    * @since   1.6
    * @return boolean contains true|false
    */
    public function check_group_limit() { 
        global $wpdb;
        /* Functionality still pending */
        $key = 'wdm_group_users_limit_'.$this->group_id;
        $meta = $wpdb->get_results("SELECT meta_value FROM `".$wpdb->postmeta."` WHERE meta_key='".$wpdb->escape($key)."'");

        if(isset($meta[0]->meta_value)) {
            $this->group_limit = $meta[0]->meta_value;
        }

        if(isset($meta[0]->meta_value) && $meta[0]->meta_value >= 1) {

            // Reduce the available seat by 1
            $group_limit = get_post_meta( $this->group_id, 'wdm_group_total_users_limit_' . $this->group_id, true );
            if ( $group_limit == '' ) {
                $group_limit = 0;
            } else {
                $group_limit = $group_limit - 1;
                update_post_meta( $this->group_id, 'wdm_group_total_users_limit_' . $this->group_id, $group_limit );
            }
            
            return true;  
        } else {
            return false;  
        }
    }

     /**
     * Function to generate random string.
     * @since   1.6
     * @access  public
     * @param integer $length contains length of string which is to be generated
     * @return string return generated string
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
