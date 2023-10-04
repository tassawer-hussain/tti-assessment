<?php
/**
    File contains code to use LearnDash plugins hooks functionality
*/


add_action('learndash_course_completed', 'learndash_course_completed_function', 10, 1);

function learndash_course_completed_function ($course_data) {

    global $wpdb;
    $user_id = $course_data['user']->ID;
    // echo '<pre>course_data ';print_r($course_data);'</pre>';
    $last_less_id = $course_data['progress'][$course_data['course']->ID]['last_id'];
    // echo '<pre>last_less_id ';print_r($last_less_id);'</pre>';
    
    $assess_id = find_assess_print_shortcode_match_lms($last_less_id);

    // echo '<pre>assess_id ';print_r($assess_id);'</pre>';
    // echo '<pre>user_id ';print_r($user_id);'</pre>';
    if($assess_id) {
        $assess_id = (int)$assess_id;
        $group_ids = get_group_ids_lms_ver($user_id, $assess_id);
        // echo '<pre>group_ids ';print_r($group_ids);'</pre>';
        $group_id = 0;
        foreach ($group_ids as $key => $gID) { 
            $result = get_contents_post_id_by_group_id_lms_ver($gID->group_ids, $assess_id);
            // var_dump('result : ' . $result);
            if($result) {
                $group_id = $gID->group_ids;
                break;    
            }
        }
        // var_dump('group_id :'. $group_id);
        if($group_id && $group_id != 0) {
            $where = 'meta_key = "learndash_group_leaders_'.esc_sql($group_id).'"';
            $sql_str =
                    $wpdb->prepare("SELECT user_id FROM ". $wpdb->usermeta ." WHERE ".$where);
                $group_leader_id = $wpdb->get_col($sql_str);

            $time_ass_assign = 0;
            if(!empty($group_leader_id) && count($group_leader_id) == 1) {
                // if there is only one group leader
                $group_leader_id = $group_leader_id[0];
                // $group_leader_ids = array_unique($group_leader_id); // get current user group leader ids
            } elseif(!empty($group_leader_id) && count($group_leader_id) > 1) {
                // get the group leader with the latest assigned time
               foreach ($group_leader_id as $key => $leader_id) {
                   $time_ass_assign_val =
                    get_user_meta( $user_id, 'assigned_group_'.$group_id.'_'.$leader_id.'_'.$assess_id, true);
                    // var_dump('assigned_group_ : ' . 'assigned_group_'.$this->group_id.'_'.$leader_id.'_'.$this->assess_id);
                    // var_dump('time_ass_assign_val : ' . $time_ass_assign_val);
                    // var_dump('assigned_group_'.$this->group_id.'_'.$leader_id.'_'.$this->assess_id.' == '. $time_ass_assign_val);
                    if($time_ass_assign_val > $time_ass_assign) {
                        $time_ass_assign = $time_ass_assign_val;
                        $group_leader_id = $leader_id; // assign with the latest time
                    }
               }

               if($time_ass_assign == 0) {
                    $group_leader_id = $group_leader_id[0];
                    // $group_leader_ids = array_unique($group_leader_id); // get current user group leader ids
               }
               
               // var_dump($this->group_leader_id);exit();
            } 
            // echo '<pre>group_leader_id ';print_r($group_leader_id);'</pre>';
            $user_info = get_userdata($group_leader_id);
            $leaderMail = $user_info->user_email;
            // echo '<pre>assess_id ';print_r($assess_id);'</pre>';
        }

    } // if ends
   
    //echo '<pre>path ';print_r($leaderMail);'</pre>';
    if(isset($leaderMail) && !empty($leaderMail)) {
        $path = save_pdf_file_after_complete_course_lms($assess_id, $user_id);
        send_mail_to_group_leaders_lms($user_id, $leaderMail, $path, $assess_id);    
    }
    //echo '<pre>path ';print_r($path);'</pre>';
    //echo '<pre>group_leader_id ';print_r($group_leader_id);'</pre>';exit();
    //exit();
}

function send_mail_to_group_leaders_lms($user_id, $leaderMail, $downloadPath, $assessment_id) {

        $user_email_data = get_userdata( $user_id );

        //echo '<pre>assessment_id ';print_r($assessment_id);'</pre>';

        $name = isset($user_email_data->data->display_name) ? $user_email_data->data->display_name : 'ttisi';
        $name = str_replace(' ', '_', $name);

        $title = get_the_title($assessment_id);
        $titles = str_replace(' ', '_', $title);
        $site_name = str_replace(' ', '_', get_bloginfo('name'));

        $subject = 'Report ('.$name.'_'.$titles.'_'.$site_name.')';
        $attachment_name = $name. ' '.$title;
        $site_name = get_bloginfo('name');
        $to = $leaderMail;
        $from = 'TTISI Platform';
        $admin_email = get_option('admin_email');
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
        $headers .= 'From: '.$site_name.'  <'.$admin_email.'>' . "\r\n";
    
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
    <td>'.$title.'</td>
    
  </tr>
  <tr>
    <td>Respondent Name: </td>
    <td>'.$user_email_data->data->display_name.'</td>
   
  </tr>
  <tr style="background-color: #dddddd;">
    <td>Respondent E-mail: </td>
    <td>'.$user_email_data->data->user_email.'</td>
    
  </tr>
</table>
<br /><br />
<div style="font-family: Arial, Helvetica, sans-serif;font-size: 19px;">
<strong>ATTACHMENT : '.$attachment_name.'</strong>
</div>

</body>
</html>
        ';
        
        $mail_attachment = $downloadPath;

        /* WordPress mail function */   
        //$this->error_log->put_error_log($user_email_data, 'array');
        $re = wp_mail($to, $subject, $msg, $headers, $mail_attachment);
        if($re) {
            /* Delete pdf file */
            //delete_pdf_file($mail_attachment);
        } 
       
    }

function save_pdf_file_after_complete_course_lms($assessment_id, $user_id) {

        $url = get_site_url().'/?report_type=quick_strength&assess_id='.$assessment_id.'&tti_print_consolidation_report=1&user_id='.$user_id; 
        // $url = 'https://www.ministryinsights.com?report_type=quick_strength&assess_id=38985&tti_print_consolidation_report=1'; 
        
        $user_info    = get_userdata( $user_id );
        $first_name   = $user_info->first_name;
        $last_name    = $user_info->last_name;
        // $first_name = 'ttisi';
        // $last_name = 'platform-report';
        
        $title = get_the_title($assessment_id);
        $titles = str_replace(' ', '_', $title);
        $site_name = str_replace(' ', '_', get_bloginfo('name'));

        $file_name = $first_name.'_'.$last_name.'_'.$titles;

        $date = date('d-m-Y', time());
        if (!file_exists(WP_CONTENT_DIR . '/uploads/tti_assessments/'.$date.'/')) {
            mkdir(WP_CONTENT_DIR . '/uploads/tti_assessments/'.$date.'/', 0777, true);
        }
        $downloadPath = WP_CONTENT_DIR . '/uploads/tti_assessments/'.$date.'/'.$file_name.'.pdf';

        //if(file_put_contents( $file_name, file_get_contents($url))) { 
            
        //} 

        $file = fopen($downloadPath, "w+");
        $body = file_get_contents($url);
        fputs($file, $body);
        //file_put_contents( $file_name, );
        fclose($file);
        //echo $downloadPath
        return $downloadPath;
}


function find_assess_print_shortcode_match_lms($content_id) {
    $content_post = get_post($content_id);
    // echo '<pre>content_post ';print_r($content_post);'</pre>';
    if(isset($content_post->post_content)) {
        $content = $content_post->post_content;
        $content = wpautop( $content_post->post_content );
        // var_dump('content : ' . $content);
       if (strpos($content, '[assessment_print_pdf_button_download_report') !== false) {
            preg_match_all("/\[[^\]]*\]/", $content, $matches);
            // echo '<pre>matches ';print_r($matches);'</pre>';
            if(isset($matches[0]) && !empty($matches[0])) {
                foreach ($matches[0] as $key => $value) {
                    if (strpos($value, '[assessment_print_pdf_button_download_report') !== false) {
                        $assess_id = preg_replace("/[^0-9]/", '', $value);
                        return $assess_id;
                    }
                }
            }
        }
    }
    return false;
}


function get_contents_post_id_by_group_id_lms_ver($group_id, $assess_id) {
        global $wpdb;
        $final_results = array();
        $key = 'ld_course_'.$c_id;
        $content_ids = array();

        $courses = get_current_group_courses_lms($group_id);
        
        // echo '<pre>courses ';print_r($courses);'</pre>';

        if(count($courses) > 0) {
            foreach ($courses as $c_id) { 
                $content_ids = get_contents_post_id_by_cou_id_lms($c_id, $content_ids);
            }
            //$this->content_ids = array_merge($this->content_ids, $courses);
            
            if(count($content_ids) > 0) {
                $content_ids = array_unique($content_ids); 
                
                // echo '<pre>content_ids ';print_r($content_ids);'</pre>';
                foreach ($content_ids as $key => $content_id) { 
                    $result = get_links_id_by_content_id_lms($content_id, $assess_id);
                    // var_dump($result);
                    if($result) { break; }
                }

            }
        }

        return $result;
    }

    function get_contents_post_id_by_cou_id_lms($c_id, $content_ids) {
        global $wpdb;
        
        $course_content_posts = array();
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

        return $content_ids;

    }

     function get_links_id_by_content_id_lms($content_id, $assess_id) {
        $content_post = get_post($content_id);
        $match_ass_id = false;
        if(isset($content_post->post_content)) {
            $content = $content_post->post_content;
            $content = wpautop( $content_post->post_content );
            if($match_ass_id == false) {
                $match_ass_id = match_all_assessment_ids_lms($content, $assess_id);
            }
        }

        return $match_ass_id;
    }

    function match_all_assessment_ids_lms($content, $assess_id) {
        $results = array();    
        // var_dump($assess_id);
        $searc_string = '[take_assessment assess_id="'.(string)$assess_id.'"';
        // echo 'searc_string : ' . $searc_string.'------'. '<br />';
        
        // var_dump(strpos($content, $searc_string));
        if (strpos($content, $searc_string) !== false) { 
            $match_ass_id = true;
         }

         return $match_ass_id;
    }


      function get_current_group_courses_lms($group_id) {
        global $wpdb;
        $course_ids = array();
        $key = 'learndash_group_enrolled_'.$group_id;
        $meta = $wpdb->get_results("SELECT * FROM `".$wpdb->postmeta."` WHERE meta_key='".$wpdb->escape($key)."'");
        foreach ($meta as $key => $value) {
            if(isset($value->post_id) && !empty($value->post_id)) {
                $course_ids[] = $value->post_id;
            }
        }

        return $course_ids;
    }
      


/**
* Function to get group ID's related to current logged in user
*
 * @since   1.7.0
*/
function get_group_ids_lms_ver($user_id, $assess_id) {
    global $wpdb;

    $assess_title = get_the_title($assess_id);

    $group_ids = array();
        if (!empty($user_id)) {
           // $sql_str = $wpdb->prepare("SELECT usermeta.meta_value as group_ids FROM ". $wpdb->usermeta ." as usermeta INNER JOIN ". $wpdb->posts ." as posts ON posts.ID=usermeta.meta_value WHERE user_id = %d  AND meta_key LIKE %s AND posts.post_title LIKE '%s' AND (posts.post_status = 'publish' OR posts.post_status = 'draft') ORDER BY posts. post_date DESC LIMIT 1", $user_id, 'learndash_group_users_%', '%'.$wpdb->esc_like($assess_title).'%');

            $sql_str = $wpdb->prepare("SELECT usermeta.meta_value as group_ids FROM ". $wpdb->usermeta ." as usermeta INNER JOIN ". $wpdb->posts ." as posts ON posts.ID=usermeta.meta_value WHERE user_id = %d  AND meta_key LIKE 'learndash_group_users_%' AND (posts.post_status = 'publish' OR posts.post_status = 'draft') ORDER BY posts.post_date DESC", $user_id);
            $group_id = $wpdb->get_results($sql_str);

        } 
        
        if(!empty($group_id)) {
            return $group_id;
        }
        return false;
    }

