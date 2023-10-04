<?php

/**
 * The core plugin class for complete profiles functionality.
 *
 * This is used to define complete profiles internationalization, dashboard-specific hooks, and
 * public-facing site hooks.
 *
 * @since   1.5.1
 * @package     TTI_Platform
 * @subpackage  TTI_Platform/includes
 * @author  Presstigers
 */



/**
* Fires before before main applicant dashboard
* 
* @since   1.0.0
*/
do_action('ttisi_platform_before_cp_datatables');  
$counter = 0;

?>
<div class="tti_assessment_cp">
    <table id="tti_assessment_cp_table" class="display responsive nowrap" style="width:100%" >
        <thead>
           <th><?php _e('Respondent','tti-platform'); ?></th>
           <th><?php _e('Email Address','tti-platform'); ?></th>
           <th><?php _e('Organization Name','tti-platform'); ?></th>
           <th><?php _e('Date Completed','tti-platform'); ?></th>
           <th><?php _e('Report Type','tti-platform'); ?></th>
           <th><?php _e('Download PDF','tti-platform'); ?></th>
        </thead>
        <tbody>
            
                <?php 
                  if(isset($data) && count($data) > 0) {  
                      foreach ($data as $key => $value) {  
                        
                        $assess_id = get_assessment_id($value->link_id);
                       
                        $status_locked = get_post_meta($assess_id, 'status_locked', true);
                        if($status_locked == 'true') {
                          $status_locked = 'close';
                        } else {
                          $assess_id = get_assessment_id($value->link_id);  
                          $status_locked = 'open';
                        }
                        
                        $report_api_check = get_post_meta($assess_id, 'report_api_check', true);
                        if(strtolower($report_api_check) == 'no')  {
                          if($value->selected_all_that_apply != NULL && !empty($value->selected_all_that_apply)) {
                            ?>
                            <tr>
                              <td><?php echo esc_html($value->first_name.' '.$value->last_name); ?></td>
                              <td><?php echo esc_html($value->email); ?></td>
                              <td><?php
                                if(isset($value->company) && !empty($value->company)) {
                                  echo esc_html($value->company); 
                                } else {
                                  echo '-'; 
                                }
                                
                              ?></td>
                              <td><?php format_the_date($value->created_at); ?></td>
                              <td><?php 
                                  echo get_the_title($assess_id);
                                  if(isset($value->position_job) && !empty($value->position_job)) {
                                    echo '<br /><br />';
                                    if($value->position_job != 'none') {
                                      echo $value->position_job;
                                    }
                                  }
                              ?>
                              </td>
                              <!-- <td><?php echo $value->version; ?></td> -->
                             <td><a target="_blank" id="tti_cp_download_btn" data-email="<?php echo esc_attr($value->email); ?>" data-assess="<?php echo esc_attr($assess_id); ?>" href="<?php echo get_site_url().'?report_type=quick_strength&user_id='.esc_html($value->user_id).'&assess_id='.$assess_id; ?>&tti_print_consolidation_report=1&version=<?php echo $value->version; ?>" >
                                  <img width="40px" src="<?php echo plugins_url().'/tti-platform/public/partials/completed-profiles/assets/images/download.png'; ?>" alt="" />
                                </a></td>
                            </tr>
                            <?php
                          }
                        } else {
                        ?>
                        <tr>
                          <td><?php echo esc_html($value->first_name.' '.$value->last_name); ?></td>
                          <td><?php echo esc_html($value->email); ?></td>
                          <td><?php
                            if(isset($value->company) && !empty($value->company)) {
                              echo esc_html($value->company); 
                            } else {
                              echo '-'; 
                            }
                            
                          ?></td>
                          <td><?php format_the_date($value->created_at); ?></td>
                          <td><?php 
                              echo get_the_title($assess_id);
                              if(isset($value->position_job) && !empty($value->position_job)) {
                                echo '<br /><br />';
                                if($value->position_job != 'none') {
                                  echo $value->position_job;
                                }
                              }
                          ?>
                          </td>
                          <!-- <td><?php echo $value->version; ?></td> -->
                         <td><a target="_blank" id="tti_cp_download_btn" data-email="<?php echo esc_attr($value->email); ?>" data-assess="<?php echo esc_attr($assess_id); ?>" href="<?php echo get_site_url().'?assessment_id='.$assess_id; ?>&version=<?php echo $value->version; ?>&user_id=<?php echo esc_html($value->user_id); ?>" >
                                <img width="40px" src="<?php echo plugins_url().'/tti-platform/public/partials/completed-profiles/assets/images/download.png'; ?>" alt="" />
                              </a></td>
                        </tr>
                        <?php
                        }
                     }
                  }
                ?>
            
        </tbody>
    </table>
</div>

<?php


/**
* Format the date (10/04/2019 07:45 PM)
*/
function format_the_date($date) {
  $origDate = $date;
  $newDate = date("m/d/Y H:i A", strtotime($origDate));
  echo $newDate;
}

/**
* Create URL PDF download.
*/
function get_assessment_id($link_id) {
    global $wpdb;
    $assessment_id = 0;
    $results = $wpdb->get_row( "select post_id, meta_key from $wpdb->postmeta where meta_value = '".$link_id."'" );
    if(isset($results->post_id)) {
      $assessment_id = $results->post_id;
    }
    return $assessment_id;
}


/**
* Fires before before main applicant dashboard
* 
* @since   1.0.0
*/
do_action('ttisi_platform_after_cp_datatables');  