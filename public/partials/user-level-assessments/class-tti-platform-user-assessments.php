<?php

/**
 * Class to handle user level assessment functionality
 *
 * @since   1.7.0
 * @package     TTI_Platform
 * @subpackage  TTI_Platform/includes
 * @author  Presstigers
 */

class TTI_Platform_User_Assessments {

    /**
    * contains user id
    * @var integer
    */
    public $user_id;

    /**
    * contains group id
    * @var integer
    */
    public $group_id;

    /**
    * contains link id
    * @var string
    */
    protected $link_id;

    /**
    * contains assessment id
    * @var integer
    */
    protected $assess_id;

    /**
    * current assessment details
    * @var array
    */
    protected $current_assess;

    /**
    * current user all assessment details
    * @var array
    */
    protected $old_assess;

    /**
    * contains report view id
    * @var string
    */
    protected $reportview_id;

    /**
    * contains report view id
    * @var string
    */
    protected $group_leader_id;

    /**
    * contains matched response arrays
    * @var array
    */
    protected $matched_arr;

    /**
    * contains status if we should use user level assessment or not
    * @var boolean
    */
    protected $leader_level_assess_status;

    /**
    * contains status if we should use user level assessment or not
    * @var boolean
    */
    protected $main_public_class;

    /**
    * contains retake assessment status
    * @var boolean
    */
    public $retake_assess_status;

    /**
    * contains retake assessment status
    * @var boolean
    */
    public $assess_details;

    /**
    * contains assessment responses
    * @var array
    */
    public $assess_responses;

    /**
    * contains retake assessment status
    * @var string
    */
    public $retake_status;
    
    /**
    * contains content ids
    * @var array
    */
    public $content_ids;

    /**
    * contains assessment version
    * @var string
    */
    public $assess_version;

    /**
    * contains assessment report meta data
    * @var array
    */
    public $report_data;

    /**
    * contains current user leader id
    * @var number
    */
    public $user_leader_id;

    /**
    * contains error log class object
    * @var boolean
    */
    public $error_log;

    /**
    * contains assess ids match status
    * @var boolean
    */
    public $match_ass_ids;


     /**
    * contains user email data
    * @var array
    */
    public $user_email_data;

    /**
    * contains all current user assessments
    * @var array
    */
    public $all_db_assess;

    /**
    * flag to check if send request using general level assessment
    * @var boolean
    */
    public $user_general_ass;

    /**
    * array contains report metadata
    * @var array
    */
    public $reoprt_api_data;

    /**
    * int contains report id
    * @var integer
    */
    public $report_id;

    /**
    * int contains report api check
    * @var integer
    */
    public $report_api_check;


    /**
     * Define the core functionality of the plugin for frontend.
     *
     * @since       1.7.0
     */
    public function __construct (
        $user_id, 
        $link_id, 
        $assess_id, 
        $error_log, 
        $main_public_class, 
        $retake_status, 
        $version_assess
    ) {
        $this->report_api_check = 0;
        $this->assess_version = $version_assess;
        $this->retake_status = $retake_status;
        $this->main_public_class = $main_public_class;
        $this->leader_level_assess_status = false;
        $this->user_general_ass = false;
        $this->match_ass_id =  false;
        $this->content_ids = array();
        $this->reoprt_api_data['passwd'] = 'none';
        $this->reoprt_api_data['created_at'] = date('Y-m-d H:i:s');
        $this->reoprt_api_data['updated_at'] = date('Y-m-d H:i:s');
        $this->user_id = $user_id;
        $this->link_id = $link_id;
        $this->assess_id = $assess_id;
        $this->error_log = $error_log;
    }

    /**
    * Function to handle if current user has already assessment instrument before but no leader
    *
    * @since   1.7.0
    */
    public function has_assessment_instrument_no_leader() {
        //echo '<pre>current_assess ';print_r(12345);'</pre>';
        $this->user_general_ass = true;
        $this->report_api_check = 1; 
        $this->report_data = get_post_meta($this->assess_id, 'report_metadata', true);
        $this->report_data = unserialize($this->report_data);
        $this->reportview_id = $this->report_data->id;

        $res_reinal = $this->tti_hit_api_with_leader_details();
        //echo '<pre>'res_reinal ';print_r($res_reinal);'</pre>';  exit;
        if($res_reinal) {
            echo json_encode(['status' => '7']);
            exit;
        }
    }

    /**
    * Function to handle user level assessments
    *
    * @since   1.7.0
    */
    public function start_user_level_assess_process() { 

        /** New Logic **/
        $curre_report_data = $this->check_current_report_meta();
        // echo '<pre>current_assess ';print_r($this->current_assess);'</pre>';
        $this->check_old_report_meta(); /* get old assessments and check which are less than 6 monthd old */
        // echo '<pre>old_assess ';print_r($this->old_assess);'</pre>';
        
        $mapping_status = $this->check_mapping_status(); 
        //var_dump( 'mapping_status : '. $mapping_status );
        
        if($curre_report_data) { // if mapping has the current assessment instrument
            $this->check_response_tag_current_assessment_data();
            //var_dump( 'leader_level_assess_status : '. $this->leader_level_assess_status );
            // if user has an assessment with same assessment in compare the current assessment instrument
            $user_leader_status = $this->if_user_has_group_leader();
            if($this->leader_level_assess_status && $mapping_status) { 
                //$user_leader_status = $this->if_user_has_group_leader();
                //var_dump( 'user_leader_status : '. $user_leader_status );
                 if($user_leader_status) { // if user has Group Leader with a capability to override
                    $this->assess_details = $this->tti_return_assessments_curr_user(); // get group leader assessment details
                    //var_dump( 'assess_details : '. $this->assess_details );
                    if($this->assess_details) { // if Group Leader has an override with current assessment
                        $res_reinal = $this->tti_hit_api_with_leader_details();
                        //echo '<pre>res_reinal ';print_r($res_reinal);'</pre>'; 
                        if($res_reinal) {
                           echo json_encode(['status' => '7']);
                            exit;
                        }
                    }  else {
                        /* if no leader details */
                        $this->has_assessment_instrument_no_leader();
                    }
                 } else {
                    /* if no leader details */
                    $this->has_assessment_instrument_no_leader();
                 }
            }
        }

       // exit();

        /***************/


        
        // //var_dump('user_leader_status : ' . $user_leader_status);
        // if($user_leader_status) { // if user has Group Leader with a capability to override
        //     $this->assess_details = $this->tti_return_assessments_curr_user(); // get group leader assessment details
        //     if($this->assess_details) { // if Group Leader has an override with current assessment
        //         $curre_report_data = $this->check_current_report_meta();
        //         //var_dump('curre_report_data : ' . $curre_report_data);
        //         if($curre_report_data) {
        //             $this->check_old_report_meta();
        //             $mapping_status = $this->check_mapping_status(); 
        //             //var_dump('mapping_status : ' . $mapping_status);
        //             //echo '<pre>'old_assess ';print_r($this->old_assess);'</pre>';
        //             //echo '<pre>'current_assess ';print_r($this->current_assess);'</pre>';
        //             if($mapping_status) {
        //                 $this->check_response_tag_current_assessment_data();
        //                 //echo '<pre>'old_assess ';print_r($this->old_assess);'</pre>';
        //                 //echo '<pre>'current_assess ';print_r($this->current_assess);'</pre>';
                       
        //                 //'leader_level_assess_status : ' . $this->leader_level_assess_status);
        //                 if($this->leader_level_assess_status) {
        //                     // use leader assessment details
        //                     $res_reinal = $this->tti_hit_api_with_leader_details();
        //                     //echo '<pre>'res_reinal ';print_r($res_reinal);'</pre>';  exit;
        //                     if($res_reinal) {
        //                         echo json_encode(['status' => '7']);
        //                         exit;
        //                     }
                            
        //                 }
        //             }
        //         }
        //     }
            
        // }
    }


    // /**
    // * Function to handle user level assessments
    // *
    // * @since   1.7.0
    // */
    // public function start_user_level_assess_process() { 
    //     $user_leader_status = $this->if_user_has_group_leader();
    //     //var_dump('user_leader_status : ' . $user_leader_status);
    //     if($user_leader_status) { // if user has Group Leader with a capability to override
    //         $this->assess_details = $this->tti_return_assessments_curr_user(); // get group leader assessment details
    //         if($this->assess_details) { // if Group Leader has an override with current assessment
    //             $curre_report_data = $this->check_current_report_meta();
    //             //var_dump('curre_report_data : ' . $curre_report_data);
    //             if($curre_report_data) {
    //                 $this->check_old_report_meta();
    //                 $mapping_status = $this->check_mapping_status(); 
    //                 //var_dump('mapping_status : ' . $mapping_status);
    //                 //echo '<pre>'old_assess ';print_r($this->old_assess);'</pre>';
    //                 //echo '<pre>'current_assess ';print_r($this->current_assess);'</pre>';
    //                 if($mapping_status) {
    //                     $this->check_response_tag_current_assessment_data();
    //                     //echo '<pre>'old_assess ';print_r($this->old_assess);'</pre>';
    //                     //echo '<pre>'current_assess ';print_r($this->current_assess);'</pre>';
                       
    //                     //'leader_level_assess_status : ' . $this->leader_level_assess_status);
    //                     if($this->leader_level_assess_status) {
    //                         // use leader assessment details
    //                         $res_reinal = $this->tti_hit_api_with_leader_details();
    //                         //echo '<pre>'res_reinal ';print_r($res_reinal);'</pre>';  exit;
    //                         if($res_reinal) {
    //                             echo json_encode(['status' => '7']);
    //                             exit;
    //                         }
                            
    //                     }
    //                 }
    //             }
    //         }
            
    //     }
    // }

    /**
    * Function to handle user level retake assessments
    *
    * @since   1.7.0
    */
    public function start_user_level_retake_assess_process() { 
        $user_leader_status = $this->if_user_has_group_leader();
        // //var_dump('user_leader_status :-- ' . $user_leader_status);
        if($user_leader_status) {   // if user has Group Leader with a capability to override
            //$curre_report_data = $this->check_current_report_meta();
            //if($curre_report_data) {
                //$this->check_old_report_meta();
                //$this->check_response_tag_current_assessment_data();
                //if($this->leader_level_assess_status) {
                // use leader assessment details
                //var_dump('leader_level_assess_status :-- ' . $this->leader_level_assess_status);
                //if($this->leader_level_assess_status) {
                    $assess_details = $this->tti_return_assessments_curr_user();
                    //echo '<pre>'assess_details ';print_r($assess_details);'</pre>';exit();
                    //$assess_details['reportview_id'] = $this->reportview_id;
                    return $assess_details;
                //}
                //}
           // }
        }
        return false;
    }

    /**
    * Get current group courses.
    */
   public function get_current_group_courses($group_id) {
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
    * Get assessment links id by course id.
    *
    * @param integer $c_id course id
    */
     function get_contents_post_id_by_cou_id($c_id) {
        global $wpdb;
        
        $course_content_posts = array();
        $key = 'ld_course_'.$c_id;

        $this->content_ids[] = $c_id; /* assign course id */
        
        $meta = $wpdb->get_results("SELECT * FROM `".$wpdb->postmeta."` WHERE meta_key='".$wpdb->escape($key)."'");
        foreach ($meta as $key => $value) {
            if(isset($value->post_id) && !empty($value->post_id)) {
                $this->content_ids[] = $value->post_id;
            }
        }
        
        $meta = $wpdb->get_results("SELECT * FROM `".$wpdb->postmeta."` WHERE meta_key='course_id' AND meta_value=".$c_id);
        foreach ($meta as $key => $value) {
            if(isset($value->post_id) && !empty($value->post_id)) {
                $this->content_ids[] = $value->post_id;
            }
        }

    }

    /**
    * Get assessment links id by course id.
    *
    * @param integer $c_id course id
    * @since   1.7.0
    */
    public function get_contents_post_id_by_group_id($group_id) {
        global $wpdb;
        $final_results = array();
        $key = 'ld_course_'.$c_id;

        $courses = $this->get_current_group_courses($group_id);
        
        //echo '<pre>'courses ';print_r($courses);'</pre>';

        //echo '<pre>'assess_id ';print_r($this->assess_id);'</pre>';

        if(count($courses) > 0) {
            foreach ($courses as $c_id) { 
                $this->get_contents_post_id_by_cou_id($c_id);
            }
            //$this->content_ids = array_merge($this->content_ids, $courses);
            
            if(count($this->content_ids) > 0) {
                $this->content_ids = array_unique($this->content_ids); 
                //echo '<pre>'content_ids ';print_r($this->content_ids);'</pre>';
                foreach ($this->content_ids as $key => $content_id) { 
                    $this->get_links_id_by_content_id($content_id);
                }

            }
        }
    }

    /**
    * Get post content.
    */
     public function get_links_id_by_content_id($content_id) {
        $content_post = get_post($content_id);

        if(isset($content_post->post_content)) {
            $content = $content_post->post_content;
            $content = wpautop( $content_post->post_content );
            if($this->match_ass_id == false) {
                $this->match_all_assessment_ids($content);
            }
            
        }
    }

    /**
    * Check if assessment id exists.
    */
    public function match_all_assessment_ids($content) {
        $results = array();    
        $searc_string = '[take_assessment assess_id="'.$this->assess_id.'"';
        
        //var_dump(strpos($content, $searc_string));
        if (strpos($content, $searc_string) !== false) { 
            $this->match_ass_id = true;
         }
    }

    /**
    * Function to get current user leaders
    *
    * @since   1.7.0
    */
    public function if_user_has_group_leader() {
        global $wpdb, $current_user;
        $group_leader_id_final = array();
        $group_ids = $this->get_group_ids();

        /*********************************************/
        foreach ($group_ids as $key => $gID) { 
            $this->get_contents_post_id_by_group_id($gID->group_ids);
            //var_dump('match_ass_ids : ' . $this->match_ass_id);
            if($this->match_ass_id != false) {
                $this->group_id = $gID->group_ids;
                break;
            }
        }
        
        //echo '<pre>'group_id ';print_r($this->group_id);'</pre>';
        
        /*********************************************/
        
        // if group doesn't belongs to current assessment
        if(isset($this->group_id)) { // if current user has any group ids, group id must contain any group leader
            $where = '';
            //$count = 1;
            
            //foreach ($group_ids as $key => $gID) {
                //if($count == 1) {
                    $where = 'meta_key = "learndash_group_leaders_'.esc_sql($this->group_id).'"';
                //} else {
                   // $where .= ' OR meta_key = "learndash_group_leaders_'.esc_sql($gID).'"';
                //}
                //$count++;
            //} 
           
            if(!empty($where)) {
                $sql_str =
                    $wpdb->prepare("SELECT user_id FROM ". $wpdb->usermeta ." WHERE ".$where);
                $group_leader_id = $wpdb->get_col($sql_str);
               
                // $this->group_id = $group_leader_id;
            } 
            // $group_leader_id_final = $group_leader_ids[count($group_leader_id) - 1]; // Get latest group leader assigned
            //echo '<pre>'group_leader_id_final ';print_r($group_leader_id_final );'</pre>';
            //echo '<pre>'group_leader_id ';print_r($this->group_leader_id);'</pre>';
            //echo '<pre>'group_leader_ids ';print_r($group_leader_ids);'</pre>';
            // exit();

            $time_ass_assign = 0;
            if(!empty($group_leader_id) && count($group_leader_id) == 1) {
                // if there is only one group leader
                $this->group_leader_id = $group_leader_id[0];
                $group_leader_ids = array_unique($group_leader_id); // get current user group leader ids
            } elseif(!empty($group_leader_id) && count($group_leader_id) > 1) {
                // get the group leader with the latest assigned time
               foreach ($group_leader_id as $key => $leader_id) {
                   $time_ass_assign_val =
                    get_user_meta( $this->user_id, 'assigned_group_'.$this->group_id.'_'.$leader_id.'_'.$this->assess_id, true);
                    // //var_dump('assigned_group_ : ' . 'assigned_group_'.$this->group_id.'_'.$leader_id.'_'.$this->assess_id);
                    // //var_dump('time_ass_assign_val : ' . $time_ass_assign_val);
                    // //var_dump('assigned_group_'.$this->group_id.'_'.$leader_id.'_'.$this->assess_id.' == '. $time_ass_assign_val);
                    if($time_ass_assign_val > $time_ass_assign) {
                        $time_ass_assign = $time_ass_assign_val;
                        $this->group_leader_id = $leader_id; // assign with the latest time
                    }
               }

               if($time_ass_assign == 0) {
                    $this->group_leader_id = $group_leader_id[0];
                    $group_leader_ids = array_unique($group_leader_id); // get current user group leader ids
               }
               
               // var_dump($this->group_leader_id);exit();
            } else {
                // if no group leader found
                return false;
            }

            set_transient( 'assessmentListenerGroupLeaders'.$current_user->ID, $this->group_leader_id, DAY_IN_SECONDS );
            // var_dump($this->group_leader_id);exit();
            if(isset($this->group_leader_id)) {
                // $this->group_leader_id = $group_leader_id_final;
                $result = $this->check_leader_has_capability($this->group_leader_id); // if group leader user has the capability
                return $result;
            } else {
                // check all group leaders, return the first who has the capability
                $result = $this->check_all_leader_has_capability($group_leader_ids);
                return $result;
            }
            
        }
        return false;
    }

    /**
    * Function to get group ID's related to current logged in user
    *
    * @since   1.7.0
    */
    public function get_group_ids() {
         global $wpdb;

        // If empty get current user id
        $user_id = $this->user_id;

        $assess_title = get_the_title($this->assess_id);

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

    /**
    * Function to check if given group leader has the capability
    *
    * @since   1.7.0
    */
    public function check_leader_has_capability($leader_id) {
        //foreach ($group_leader_ids as $key => $leader_id) {
            $user_settings = get_user_meta( $leader_id, 'user_assessment_settings', true);
            if(!empty($user_settings)) {
                $user_settings = unserialize($user_settings);
                if(isset($user_settings['user_capa']) && $user_settings['user_capa'] == 'Yes') {
                    return true;
                }
            }
        //}
        return false;
    }

    /**
    * Function to check if group leaders has the capability
    *
    * @since   1.7.0
    */
    public function check_all_leader_has_capability($group_leader_ids) {
        foreach ($group_leader_ids as $key => $leader_id) {
            $user_settings = get_user_meta( $leader_id, 'user_assessment_settings', true);
            if(!empty($user_settings)) {
                $user_settings = unserialize($user_settings);
                if(isset($user_settings['user_capa']) && $user_settings['user_capa'] == 'Yes') {
                    $this->group_leader_id = $leader_id;
                    return true;
                }
            }
        }
        return false;
    }

    /**
    * Function to get current assessment metadata
    *
    * @since   1.7.0
    */
    public function check_current_report_meta() {
        $report_data = get_post_meta($this->assess_id, 'report_metadata', true);
        $report_data = unserialize($report_data);

        if(isset($report_data)) {
            //$this->reportview_id = $this->report_data->id; // set report view id
            foreach ($report_data->assessment as $key => $value)  {
                //$this->current_assess[$key] = $value;
                if($key == 'instruments') { 
                    foreach ($value as $innerkey => $innervalue) { // instruments loop
                        //$this->current_assess[$innervalue->id] = $this->get_word_first_charac($this->report_data->assessment->name);
                        $this->current_assess[$innervalue->id] = $this->get_word_first_charac($innervalue->name);
                    }
                }
            }
            return true;
        }
        return false;
    }

    /**
    * Function to get words first characters
    *
    * @since   1.7.0
    */
    public function get_word_first_charac($word) { 
        //var_dump('word : ' . $word);
        // if (strpos($word, '(') !== false) {
        //     preg_match('#\((.*?)\)#', $word, $word);
        //     return $word[1];
        // }

        // if ($word == 'Driving Forces Job') {
        //     $acronym = 'DFJOB';
        // } else if ($word == 'Style Insights SA2R4') {
        //     $acronym = 'SA2R4';
        // } else if ($word == 'Motivation Insights PIAV2') {
        //     $acronym = 'PIAV2';
        // } else {
            $words = explode(" ", $word);
            $acronym = "";
            //$counter = 0;
            // foreach ($words as $w) {
            //   if($counter == 2) {
            //     break;
            //   }
            //   $acronym .= $w[0];
            //   $counter++;
            // }
        // }
            if(count($words) == 2) {
                $counter = 0;
                foreach ($words as $w) {
                  if($counter == 2) {
                    break;
                  }
                  $acronym .= $w[0];
                  $counter++;
                }
            }elseif(isset($words[2])) {
                $acronym = trim($words[2]);    
            }
            
        
        //var_dump('acronym : ' . $acronym);
        return $acronym;
    }

    /**
    * Function to get old assessments metadata
    *
    * @since   1.7.0
    */
    public function check_old_report_meta() {
        global $wpdb;
        $assessment_table_name = $wpdb->prefix.'assessments';
        $sql_str = $wpdb->prepare("SELECT version, link_id, position_job, gender, company, created_at, assessment_result FROM ".$assessment_table_name." WHERE user_id = %d ORDER BY version DESC", $this->user_id);
        $assessments = $wpdb->get_results($sql_str);
        // echo '<pre>current_assess ';print_r($assessments);'</pre>';
        $this->all_db_assess = $assessments;
        if(!empty($assessments)) {
            foreach ($assessments as $key => $value) {
                $this->check_assessment_response(unserialize($value->assessment_result), $value->created_at);    
            } 
            krsort($this->old_assess); // descending order the array
        }
    }

    /**
    * Function to get old assessments metadata
    *
    * @since   1.7.0
    */
    public function check_assessment_response($data, $created_at) {
        if(isset($data->report->info->responses)) {
            $date_check = $this->check_less_than_six_month($created_at);
            //var_dump('created_at : ' . $created_at);
            //var_dump('date_check : ' . $date_check);
            if($date_check) {
                $this->old_assess[$created_at][] = $data->report->info->responses;
            }
        }
    }

    /**
    * Function to find the current assessment instrument in old assessment
    *
    * @since   1.7.0
    */
    public function check_response_tag_current_assessment_data() {
       
        $date_check = false;
        foreach ($this->old_assess as $key => $value) {
            // $date_check = $this->check_less_than_six_month($key);
            // //var_dump('date_check : ' . $date_check);
            // if($date_check) {
            $this->compare_reponse_tags($value);
            //}
        }
        // echo '<pre>matched_arr ';print_r($this->matched_arr);'</pre>';
        // echo '<pre>assess_responses ';print_r($this->assess_responses);'</pre>';exit();
    } 

    /**
    * Function to check if given date is less than 1 year
    *
    * @since   1.7.0
    */
    public function check_less_than_six_month($given_date) {
        $data_given = explode('T', $given_date);
        //var_dump(' given_date : '.$data_given[0]);
        //return true;
        //$my_date = '2020-01-23';
        // true if my_date is more than a month ago
        //var_dump(strtotime($my_date) < strtotime('6 month ago')); 
        if (strtotime($data_given[0]) < strtotime('6 month ago')) {
            return false;
        } else{
            return true;
        }
    }

    /**
    * Function to compare the response tag or instrumnet id
    *
    * @since   1.7.0
    */
    public function compare_reponse_tags($data) {
        foreach ($this->current_assess as $key => $value) {
            $yes_exists = $this->multi_key_exists($value);
            if($yes_exists) {
                $this->matched_arr[$key] = $value;
                $this->leader_level_assess_status = true;
            }
        }
        
    }

    /**
    * Function to check key in multidimensional array
    *
    * @since   1.7.0
    */
    public function multi_key_exists($key_search) { 
        foreach ($this->old_assess as $key => $value) {
            foreach ($value as $ikey => $ivalue) { 
                if(array_key_exists($key_search, $ivalue)) {
                    $this->assess_responses[$key_search] = $ivalue->{$key_search};
                    return true;
                }
            }
        }
    }

    /**
    * Function to check mapping limitations
    *
    * @since   1.7.0
    */
    public function check_mapping_status() { 
        $mapping_data = get_option('tti_platform_mapping_data');
        if(isset($this->current_assess) && !empty($this->current_assess)  &&
          isset($mapping_data) && !empty($mapping_data)) {
            $counter_loop = count($mapping_data['response_id']);
            for ($i=0;$i<$counter_loop;$i++) { 
                if (
                    array_key_exists($mapping_data['instrument_id'][$i], $this->current_assess) && 
                    in_array($mapping_data['response_id'][$i], $this->current_assess)
                ) {
                    return true;
                }   
                $counter++;
            }
        }
        return false;
    }

    /**
    * Function to check mapping limitations
    *
    * @since   1.7.0
    */
    public function tti_hit_api_with_leader_details() {
        global $current_user, $wpdb;

        if($this->user_general_ass) {
            $this->assess_details['api_key'] = get_post_meta($this->assess_id, 'api_key', true);
            $this->assess_details['api_service_location'] = get_post_meta($this->assess_id, 'api_service_location', true);
            $this->assess_details['link_id'] = get_post_meta($this->assess_id, 'link_id', true);
            $this->assess_details['account_login'] = get_post_meta($this->assess_id, 'account_login', true);
            $this->assess_details['survay_location'] = get_post_meta($this->assess_id, 'survay_location', true);
            $this->assess_details['send_rep_group_lead'] = (!empty(get_post_meta($this->assess_id, 'send_rep_group_lead', true))) ? get_post_meta($this->assess_id, 'send_rep_group_lead', true) : '';
        } else {
            $this->report_api_check = 3;
        }

            
        // echo '<pre>assess_details ';print_r($this->assess_details);'</pre>';
        $report_id = $this->get_report_ID($current_user); // create report with user assessment details and return report id
        $this->report_id = $report_id;
        //var_dump('reportview_id ' . $this->reportview_id);
        if($report_id > 0) {
            $url = $this->assess_details['api_service_location'] . '/api/v3/reports/'.$report_id;
            /* API v3.0 url */  
            //$url = $this->assess_details['api_service_location'].'/api/v3/reportviews/'.$this->reportview_id;
            // var_dump($url);
            $url = esc_url($url);
            // var_dump($url);
            $headers = array (
                'Authorization' => $this->assess_details['api_key'],
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            );

            $args = array (
               'method' => 'GET',
               'headers' => $headers,
            );
            //echo '<pre>'api_response ';print_r($headers);'</pre>';
            //echo '<pre>'api_response ';print_r($args);'</pre>';
            $response = wp_remote_request($url, $args);
            $api_response = json_decode(wp_remote_retrieve_body($response));
            // echo '<pre>api_response ';print_r($api_response);'</pre>';
            if (is_wp_error($response)) {
                $this->error_log->put_error_log('Error in response in report data request');
                $error_message = $response->get_error_message();
                //echo $error_message;
            } else {
                $this->insert_assessment_data($api_response, $report_id);
            }  
            return true;
        } else {
            return false;
        }

    }

    /**
     * Function to create report and return report ID
     *
     * @since   1.7.0
     */ 
    public function get_report_ID($current_user) {  

       $position_job = isset($this->all_db_assess[0]->position_job) && !empty($this->all_db_assess[0]->position_job) ? $this->all_db_assess[0]->position_job : 'none';
       $company = isset($this->all_db_assess[0]->company) && !empty($this->all_db_assess[0]->company) ? $this->all_db_assess[0]->company : '';
       $gender = isset($this->all_db_assess[0]->gender) && !empty($this->all_db_assess[0]->gender) ? $this->all_db_assess[0]->gender : '';

        /* API v3.0 url */   
        $url = $this->assess_details['api_service_location'].'/api/v3/reports?link_login='.$this->assess_details['link_id'];
        //var_dump($url);
        $payload['respondent'] = array (
            'first_name'    => $current_user->user_firstname,
            'last_name'     => $current_user->user_lastname,
            'gender'        => $gender,
            'email'         => $current_user->user_email,
            'company'       => $company,
            'position_job'  => $position_job
        );
        
        $payload['reportview_id'] = $this->reportview_id;
        $payload['responses'] = $this->assess_responses;
        // echo '<pre>payload ';print_r($payload);'</pre>';
        $data = wp_remote_post (
            $url, 
            array (
                'headers'     => array (
                'Content-Type' => 'application/json; charset=utf-8', 
                'Authorization' => $this->assess_details['api_key'], 
                'Accept' => 'application/json'
            ),
            'body'        => json_encode($payload),
            'method'      => 'POST',
            'data_format' => 'body',
         ));
        // echo '<pre>response ';print_r($data);'</pre>';
        $response = json_decode(wp_remote_retrieve_body($data));
        if($response && isset($response->respondent->passwd)) {
            $this->reoprt_api_data['passwd'] = $response->respondent->passwd;
        }
        if($response && isset($response->respondent->created_at)) {
            $this->reoprt_api_data['created_at'] = $response->respondent->created_at;
        }
        if($response && isset($response->respondent->updated_at)) {
            $this->reoprt_api_data['updated_at'] = $response->respondent->updated_at;
        }
        // echo '<pre>response ';print_r($response);'</pre>';

        /* User data for email template */
        $this->user_email_data =  array (
            'first_name' => $current_user->user_firstname,
            'last_name' => $current_user->user_lastname,
            'email' => $current_user->user_email,
            'company' => $company,
            'position_job' => $position_job,
            'link_id' => $this->link_id,
            'gender' => $gender
         );

        //echo '<pre>'create report response ';print_r($response);'</pre>';
        if (is_wp_error($response)) {
            return false;
        } 
        return $response->id;
    }

    /**
     * Function to insert assessments data
     *
     * @since   1.7.0
     */
    public function insert_assessment_data($api_response, $report_id) {
        global $current_user, $wpdb;
        $assessment_table_name = $wpdb->prefix.'assessments';
        
        $insertQuery = $wpdb->insert( $assessment_table_name, 
            array (
                'user_id' => $this->user_id,
                'first_name' => $current_user->user_firstname,
                'last_name' => $current_user->user_lastname,
                'email' => $current_user->user_email,
                'service_location' => $this->assess_details['api_service_location'],
                'account_id' => $this->assess_details['account_login'],
                'link_id' => $this->link_id,
                'api_token' => $this->assess_details['api_key'],
                'gender' => $this->user_email_data['gender'],
                'company' => $this->user_email_data['company'],
                'status' => 1,
                'version' => $this->assess_version,
                'position_job' => $this->user_email_data['position_job'],
                'password' => $this->reoprt_api_data['passwd'],
                'report_id' => (int)$report_id,
                'created_at' => $this->reoprt_api_data['created_at'],
                'updated_at' => $this->reoprt_api_data['updated_at'],
                'assess_type' => $this->report_api_check,
                'assessment_result' => serialize($api_response)
            ),
            array ( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
        );

        //var_dump( 'insertQuery  : ' . $insertQuery);
        // if assessment results successful inserted
        if($insertQuery) {
            $this->tti_send_report_to_leader();
        } 
    }

    /**
     * Function to map user and site level assessment
     * @since   1.7.0
     */
    public function tti_return_assessments_curr_user() {
        $details = get_user_meta( $this->group_leader_id, 'user_assessment_data', true);
        $data = unserialize($details); // user level assessment details
        ////echo '<pre>'';print_r($data);'</pre>';
        $this->report_data = get_post_meta($this->assess_id, 'report_metadata', true);
        $this->report_data = unserialize($this->report_data);


        ////var_dump('assessment_title : '. $assessment_title);
        if(count($data) >= 1) {
            foreach ($data as $key => $value) { 
                if($value['report_view_id'] == $this->report_data->id) {
                    $this->reportview_id = $this->report_data->id; // set report view id
                    return $value;
                }
            }
        }
        return false;
    }

    /**
     * Function to send report to leader
     * @since   1.7.0
     */
    public function tti_send_report_to_leader() {
        global $current_user;


        if(
            isset($this->assess_details['send_rep_group_lead']) && 
            $this->assess_details['send_rep_group_lead'] == 'Yes'
        ) {
            if(
                isset($this->user_email_data['position_job']) && 
                $this->user_email_data['position_job'] == 'none'
            ) {
                $this->user_email_data['position_job'] = '';
            }
            $this->main_public_class->initiate_group_leader_email_process (
                $this->assess_id, 
                $this->report_id, 
                $this->assess_details['api_key'], 
                $this->assess_details['api_service_location'], 
                $this->user_id, 
                $this->user_email_data,
                $this->assess_id,
                true
            );
        }
        ////echo '<pre>'';print_r($this->assess_details);'</pre>';exit();
    }

}
