<?php
/**
 * Enrolled users tab contents display template (Procedural Programming)
 *
 * @since      1.6
 * @package    TTI_Platform
 * @subpackage TTI_Platform/includes
 * @author     Presstigers
 */



$content_ids = array();
$links_id = array();
$assess_ids = array();
$assess_names = array();
$assessment_ids = array();
$enrolled_course_id = array();

$counter = 0;

global $enrolled_course_id;
global $links_id;
global $assess_ids;

/* Get link ids related to current group */
get_the_current_link_id($group_id);

if(isset($links_id) && count($links_id) > 0) {
	$links_id = array_unique($links_id);
	$assess_ids = array_unique($assess_ids);
}

set_transient( 'group_dashboard_assess_id_'.$group_id, $assess_ids, DAY_IN_SECONDS );

?>
<div id="tab-1" class="tab-content current">
	<!-- <input type='button' id='bulk_remove' value='<?php esc_html_e( 'Bulk Remove', 'tti-platform' ); ?>'> -->
	<table id='tti_group_leader_retake' class="display responsive nowrap">
		<thead>
			<?php if(isset($links_id) && !empty($links_id)) { ?>
			<tr>
				<!-- <th><input type="checkbox" name="select_all" class="bb-custom-check"></th> -->
				<th><?php esc_html_e( 'Name', 'tti-platform' ); ?></th>
				<th><?php esc_html_e( 'Email', 'tti-platform' ); ?></th>
				<th><?php esc_html_e( 'Date Assigned', 'tti-platform' ); ?></th>
				<th><?php esc_html_e( 'Date Completed', 'tti-platform' ); ?></th>
				<!-- <th><?php esc_html_e( 'Retake Assessment', 'tti-platform' ); ?></th> -->
				<!-- <th><?php esc_html_e( 'Users Limit', 'tti-platform' ); ?></th> -->
				<th><?php esc_html_e( 'Report Details', 'tti-platform' ); ?></th>
				<!-- <th><?php esc_html_e( 'Status', 'tti-platform' ); ?></th> -->
				<th><?php esc_html_e( 'Action', 'tti-platform' ); ?></th>
			</tr>
		<?php } else { ?>
			<tr>
				<th><input type="checkbox" name="select_all" class="bb-custom-check"></th>
				<th><?php esc_html_e( 'Name', 'wdm_ld_group' ); ?></th>
				<th><?php esc_html_e( 'Email', 'wdm_ld_group' ); ?></th>
				<th><?php esc_html_e( 'Action', 'wdm_ld_group' ); ?></th>
			</tr>
		<?php } ?>
		</thead>
		<tbody>
			<?php
			// Put in a method.
			$default = array( 'removal_request' => array() );
			if ( ! empty( $users ) ) {
				$removal_request['removal_request'] = maybe_unserialize( get_post_meta( $group_id, 'removal_request', true ) );
				$removal_request                    = array_filter( $removal_request );

				$removal_request = wp_parse_args( $removal_request, $default );
				$removal_request = $removal_request['removal_request'];

				$ldgr_reinvite_user = get_option( 'ldgr_reinvite_user' );
				$reinvite_class_data = 'wdm-reinvite';
				$reinvite_text_data  = apply_filters( 'wdm_change_reinvite_label', __( 'Re-Invite', 'tti-platform' ) );

				$new_users_arr = array();
				if(isset($links_id) && !empty($links_id)) {
					foreach ($links_id as $key => $link_id) { 
						$new_users_arr[$link_id] = $users;
						$assess_names[$link_id] = get_the_title($assess_ids[$key]);
						$assessment_ids[$link_id] = $assess_ids[$key];
					}
				$counter2 = 0;
				foreach ( $new_users_arr as $link_id => $values ) { // $value contains user id
					foreach ( $values as $k => $value ) {
						
					$group_leade_report = get_post_meta($assessment_ids[$link_id], 'send_rep_group_lead', true);
					$pdf_version = get_current_assess_user_counts($value, $link_id);
					
					$user_data = get_user_by( 'id', $value );

					$counter++;
					?>
					<tr id="userid-<?php echo  $value; ?>-<?php echo  $link_id; ?>">
					<!-- <td class="select_action">
							<input
								type="checkbox"
								name="bulk_select"
								data-user_id ="<?php echo esc_html( $value ); ?>"
								data-group_id="<?php echo esc_html( $group_id ); ?>">
						</td> -->
						<td data-title="Name">
							<?php
								echo esc_html( get_user_meta( $value, 'first_name', true ) . ' ' . get_user_meta( $value, 'last_name', true ) );
							?>
						</td>
						<td data-title="Email">
							<?php echo esc_html( $user_data->user_email ); ?>
						</td>
						<td data-title="Enrolled Date">
							<?php 

								$enrolled_to_group_course_date = 
								get_user_meta( $value, 'course_'.$enrolled_course_id.'_access_from', true );
								if(
									(empty($enrolled_to_group_course_date) || 
									!isset($enrolled_to_group_course_date)) &&
									function_exists('learndash_user_group_enrolled_to_course_from')
								) {
									/** If the user registered AFTER the course was enrolled into the group then we use the user registration date. */
									$enrolled_to_group_course_date = learndash_user_group_enrolled_to_course_from( $value, $enrolled_course_id );
									
									if ( empty($enrolled_to_group_course_date) ) {
										$userdata = get_userdata( $value );
										$enrolled_to_group_course_date = strtotime( $userdata->user_registered );
									}
									
								} else {
									$userdata = get_userdata( $value );
									$enrolled_to_group_course_date = strtotime( $userdata->user_registered );
								} 
								echo esc_html(date('M j, Y', $enrolled_to_group_course_date));
								
							?>
						</td>
						<td data-title="Report Date">
							<?php 
								if($pdf_version) {
									$assess_date = get_current_assess_create_date($value, $link_id, $pdf_version);
									$assess_date = new DateTime($assess_date);
									$final_ass_giv_date = $assess_date->format('M j, Y');
									echo esc_html($final_ass_giv_date); 
								} else {
									echo '-'; 
								}
								
							?>
						</td>
						
						<?php
						if ( ! in_array( $value, $removal_request ) ) {
							$class_data = 'wdm_remove';
							$text_data  = __( 'Remove', 'tti-platform' );
						} else {
							$class_data = 'request_sent';
							$text_data  = __( 'Request sent', 'tti-platform' );
						}
						?>
						<!-- 
						<td class="ldgr-actions">
							<button 
								    type="button"
									id="tti_retake_assessment_<?php echo esc_attr( $value ); ?>" 
									class="tti_retake_assessment" 
									data-link_id ="<?php echo esc_attr( $link_id ); ?>" 
									data-user_id ="<?php echo esc_attr( $value ); ?>" 
									data-group_id="<?php echo esc_attr( $group_id ); ?>"
									data-mail = "<?php echo esc_attr( $user_data->user_email ); ?>" >
								<?php esc_html_e( 'Retake Assessment', 'tti-platform' ); ?>
							</button>
						<img src="<?php echo TTI_PLAT_GL_IMG_ASSETS_PATH.'ttisi-spinner.gif'; ?>" id="retake_assessment_loader_<?php echo esc_attr( $value ); ?>" class="retake_assessment_loader" style="display: none"/>
						</td> -->
						
						<td class="ldgr-pdf-details ldgr-user-limit">
								 <?php 
								// $value contains user_id
								//foreach ($links_id as $key => $link_id) { 
									//echo 'Link ID : ' . get_the_title($assess_ids[]).'<br />';
									$ass_comp_re = check_current_user_completed_assessment($value, $link_id);
									if($ass_comp_re) {
										echo '<small class="assess-complete-text ttisi-info-gp ttisi-info-success-color">Assessment Completed</small>';
										//echo '<br />';
										if(isset($completed_ass_count) && count($completed_ass_count) > 0) {
											foreach ($completed_ass_count as $key => $count) { 
												if(empty($count)) {
													//echo '<small class="ttisi-info-gp ttisi-info-warning-color ttisi-pos-rel-tp-6 ">'.__('Assessment Not Completed', 'tti-platform').' : 0</small>';
												} else {
													//echo '<small class="ttisi-info-gp ttisi-info-warning-color ttisi-pos-rel-tp-6 ">'.__('Assessment Not Completed', 'tti-platform').' : '.$count.'</small>';
												}
											}
										}
										
										if ( "QuickStrengths" == $assess_names[$link_id] ) {
											$course_complete_date = learndash_user_get_course_completed_date( $value, (int)$enrolled_course_id );
											if( $course_complete_date ) {
												echo '<span><span class="ttisi-info-blue-color ttisi-info-gp ttisi-pos-rel-tp-6-minus">Course Completed</span></span>';
											} else {
												echo '<span><span class="logged-in ttisi-info-gp ttisi-info-logged-color">Course Not Completed</span></span>';
											}
										}

									} else {
										

										//echo '<br />';
										echo '<small class="ttisi-info-gp ttisi-info-warning-color ttisi-pos-rel-tp-6 ">'.__('Assessment Not Completed', 'tti-platform').' </small>';

										/* Check if user logged in or not */
										$user_last_login = check_last_login($value);
										if($user_last_login) {
											echo '<small class="logged-in ttisi-info-gp ttisi-info-logged-color">'.__('User Only Logged In', 'tti-platform').'</small>';
										} 

									}
									//echo '<br />';
								//}
							?>
							
							<span><?php echo '<span class="ttisi-info-blue-color ttisi-info-gp ttisi-pos-rel-tp-6-minus">'.$assess_names[$link_id].'</span>'; ?></span>
                          <?php
                          		$completed_ass_count = array();
                          		//foreach ($links_id as $key => $link_id) { 
									
									
									$completed_ass_count[] = $pdf_version;

									if(strtolower($group_leade_report) == 'yes') {
					
										if($pdf_version) {
											?>
												<a target="_blank" id="tti_cp_download_btn" data-email="<?php echo esc_attr($value->email);
												 ?>" data-assess="<?php echo esc_attr($assess_id); ?>" href="<?php echo get_site_url().'?cp_page=true&user_id='.esc_html($value).'&assessment_id='.$assessment_ids[$link_id]; ?>&version=<?php echo esc_attr($pdf_version); ?>&group_leader=true" >
			                            		<u><img class="tti-gp-column" src="<?php echo TTI_PLAT_GL_IMG_ASSETS_PATH.'download.png' ?>" />
			                            			<?php esc_html_e('Download Latest PDF','tti-platform'); ?> </u>
			                            		</a>
			                            		<sub><strong style="font-size: 9px;">(<?php esc_html_e('Version','tti-platform'); ?> : <?php echo $pdf_version.'.0'; ?>)</strong></sub>
			                            		
											<?php
										} else {
											?>
											<span class="ttisi-info-gp"><?php esc_html_e( 'No PDF Available', 'tti-platform' ); ?></span>
										<?php 
										}
									}

									//echo '<br />';
								//}
                          ?>
                          <!-- Show user limits -->
							<?php  
								//foreach ($links_id as $key => $link_id) { 
							echo '<small class="ttisi-info-gp ttisi-info-limit-color ttisi-info-limit ttisi-pos-rel-tp-6">'.__('Retake Limit', 'tti-platform').' : '.get_user_limits($value, $group_id, $link_id).'</small>';
							//echo '<br />';
									
								//}
							 ?>

						
						</td>
						<!-- <td class="ldgr-user-limit">
							
							
						</td> -->
						<td class="ldgr-actions">
						
						<span  style="overflow: visible; position: relative; width: 80px;">
						   <div class="dropdown">
						      
						   <ul class="kt-nav">
						            <li class="kt-nav__item">
										<a class="kt-nav__link" href="#">
											<span class="kt-nav__link-text">
												<span 
													type="button"
													id="tti_retake_assessment_<?php echo esc_attr( $value ); ?>" 
													class="tti_retake_assessment" 
													data-link_id ="<?php echo esc_attr( $link_id ); ?>" 
													data-user_id ="<?php echo esc_attr( $value ); ?>" 
													data-group_id="<?php echo esc_attr( $group_id ); ?>"
													data-reg-left = "<?php echo esc_attr( get_post_meta( $group_id, 'wdm_group_users_limit_'.$group_id, true ) ); ?>"
													data-group_leader_id="<?php echo esc_html( get_current_user_id() ); ?>" 
													data-mail = "<?php echo esc_attr( $user_data->user_email ); ?>" >
													<?php esc_html_e( 'Retake Assessment', 'tti-platform' ); ?>
												</span>
						        			</span>
										</a>
									</li>
						            <li class="kt-nav__item">
						              <?php
										if($ass_comp_re) {

										} else{
											if($user_last_login) {
											} else {
												if ( 'on' == $ldgr_reinvite_user ) { ?>
												<span
													href="#"
													data-user_id ="<?php echo esc_html( $value ); ?>"
													data-group_id="<?php echo esc_html( $group_id ); ?>"
													class="<?php echo esc_html( $reinvite_class_data ); ?> button">
													<?php echo esc_html( $reinvite_text_data ); ?>
												</span>&nbsp;
												<?php }
											}
										}
						              	?>
						            </li>
						           <?php if ( apply_filters( 'wdm_ldgr_remove_user_button', true, $value, $group_id ) ) { ?>
						            	<li class="kt-nav__item"> 
											<?php if ( "wdm_remove" == $class_data): ?>
												<span
													data-assessment-status = "<?php echo ($ass_comp_re) ? "complete" : "not-complete"; ?>"
													data-group-type = "assessment"
													class="tti-user-removal button">
													<?php echo esc_html( $text_data ); ?>
												</span>
											<?php endif; ?> 
											<span
												<?php echo ( "wdm_remove" == $class_data) ? 'style="display: none;"' : ''; ?>
												href="#"
												data-user_id ="<?php echo esc_html( $value ); ?>"
												data-group_id="<?php echo esc_html( $group_id ); ?>"
												data-nonce="<?php echo esc_attr( wp_create_nonce( 'ldgr_nonce_remove_user' ) ); ?>"
												class="ldgr-user-removal <?php echo esc_html( $class_data ); ?> button">
													<?php echo esc_html( $text_data ); ?>
											</span>
										</li>
									<?php } ?>
						         </ul>
						   </div>
						</span>

							
							
							
						</td>
					</tr>
					<?php
					 $counter2++;
				}
			}
		} else {
				foreach ( $users as $k => $value ) {
					$user_data = get_user_by( 'id', $value );
					?>
					<tr>
						<td class="select_action">
							<input
								type="checkbox"
								name="bulk_select"
								data-user_id ="<?php echo esc_html( $value ); ?>"
								data-group_id="<?php echo esc_html( $group_id ); ?>">
						</td>
						<td data-title="Name">
							<p>
							<?php
								echo esc_html( get_user_meta( $value, 'first_name', true ) . ' ' . get_user_meta( $value, 'last_name', true ) );
							?>
							</p>
						</td>
						<td data-title="Email">
							<p><?php echo esc_html( $user_data->user_email ); ?></p>
						</td>
						<?php
						if ( ! in_array( $value, $removal_request ) ) {
							$class_data = 'wdm_remove';
							$text_data  = __( 'Remove', 'wdm_ld_group' );
						} else {
							$class_data = 'request_sent';
							$text_data  = __( 'Request sent', 'wdm_ld_group' );
						}
						?>
						<td class="ldgr-actions">
							<?php if ( 'on' == $ldgr_reinvite_user ) { ?>
								<a
									href="#"
									data-user_id ="<?php echo esc_html( $value ); ?>"
									data-group_id="<?php echo esc_html( $group_id ); ?>"
									class="<?php echo esc_html( $reinvite_class_data ); ?> button"><?php echo esc_html( $reinvite_text_data ); ?></a>&nbsp;
							<?php } ?>

							<?php if ( apply_filters( 'wdm_ldgr_remove_user_button', true, $value, $group_id ) ) { ?>
								<?php if ( "wdm_remove" == $class_data): 
									$course_id = th_get_current_group_courses( $group_id );
									$course_status = learndash_user_get_course_progress( $value,  $course_id[0] );
									?>
									<a
										href="#"
										data-assessment-status = "<?php echo $course_status['status']; ?>"
										data-group-type = "course"
										class="tti-user-removal button"><?php echo esc_html( $text_data ); ?>
									</a>
								<?php endif; ?>
								<a
									<?php echo ( "wdm_remove" == $class_data) ? 'style="display: none;"' : ''; ?>
									href="#"
									data-user_id ="<?php echo esc_html( $value ); ?>"
									data-group_id="<?php echo esc_html( $group_id ); ?>"
									data-nonce="<?php echo esc_attr( wp_create_nonce( 'ldgr_nonce_remove_user' ) ); ?>"
									class="<?php echo esc_html( $class_data ); ?> button"><?php echo esc_html( $text_data ); ?></a>
							<?php } ?>
							<?php do_action( 'ldgr_group_row_action', $value, $group_id ); ?>
						</td>
					</tr>
					<?php
				}
			}
	}
			?>
		</tbody>
	</table>
</div>

</form><!-- End of first Tab  -->


<?php


/**
*  Get user assessment latest version
*/
function get_current_assess_create_date($c_usrid, $link_id, $pdf_version) {
	global $wpdb;
	$assessment_table_name = $wpdb->prefix.'assessments';
    $results = $wpdb->get_row("SELECT created_at FROM $assessment_table_name WHERE user_id ='$c_usrid' AND link_id='$link_id' AND status = 1 AND version = ".$pdf_version);

    if(isset($results->created_at)){
    	return $results->created_at;
    } 
    return false;
}

/**
*  Get user assessment latest version
*/
function get_current_assess_user_counts($c_usrid, $link_id) {
	global $wpdb;
	$assessment_table_name = $wpdb->prefix.'assessments';
    $results = $wpdb->get_results("SELECT * FROM $assessment_table_name WHERE user_id ='$c_usrid' AND link_id='$link_id' AND status = 1");
    if(empty($results)){
        $results = array();
    }
    if(isset($results) && count($results) > 0){
    	return count($results);
    } 
    return false;
}


/**
*  Check current user completed assessment status.
*/
function check_current_user_completed_assessment($c_usrid, $link_id) {
	global $wpdb;
	$assessment_table_name = $wpdb->prefix.'assessments';
    $results = $wpdb->get_row("SELECT * FROM $assessment_table_name WHERE user_id ='$c_usrid' AND link_id='$link_id' AND status = 1");
    
    if(isset($results) && !empty($results)) {
        return true;
    }
    return false;
}

/**
* Check if user logged-in atlease once
*/
function check_last_login($user_id) { 

	//$user_last_login = get_user_meta( $user_id, 'last_activity', true );
	$user_last_login = get_user_meta( $user_id, 'wc_last_active', true );
	if(empty($user_last_login)) {
		return false;
	}
	return true;
}

$courses = array();

/**
* Get the link ID related to current group ID.
*/
function get_the_current_link_id($group_id) {
	global $content_ids, $links_id;
	$courses = get_current_group_courses($group_id);
	
	if(count($courses) > 0) {
		foreach ($courses as $c_id) { 
        	get_contents_post_id_by_cou_id($c_id);
    	}
    	$content_ids = array_merge($content_ids, $courses);

    	if(count($content_ids) > 0) {
    		$content_ids = array_unique($content_ids); 
    		
    		foreach ($content_ids as $key => $content_id) { 
                get_links_id_by_content_id($content_id, $courses);
            }
    	}
    }
}

/**
* Get current group courses.
*/
function get_current_group_courses($group_id) {
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
        global $wpdb, $content_ids;
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
    }


     /**
    * Get post content.
    */
     function get_links_id_by_content_id($content_id, $courses) {
        $content_post = get_post($content_id);

        if(isset($content_post->post_content)) {
            $content = $content_post->post_content;
            $content = wpautop( $content_post->post_content );
            match_all_assessment_ids($content, $content_id, $courses);
        }
    }

      /**
    * Check if assessment id exists.
    */
     function match_all_assessment_ids($content, $content_id, $courses) {
     	global $enrolled_course_id;
     	global $links_id;
     	global $assess_ids;
        $args = array( 'post_type' => 'tti_assessments');
        $loop = new WP_Query( $args );
        while ( $loop->have_posts() ) : $loop->the_post();
            $searc_string = '[take_assessment assess_id="'.get_the_id().'"';
            if (strpos($content, $searc_string) !== false) {
                $links_id[] = get_post_meta( get_the_id(), 'link_id', true );
                $assess_ids[] =  get_the_id();
                if(in_array($content_id, $courses)) {
                	$enrolled_course_id= $content_id;
                } else {
                	$enrolled_course_id = get_post_meta( $content_id, 'course_id', true );
                }
            }
        endwhile;
    }


   /**
    * Get user limit by user id, link id and group id.
    *
    * @param $user_id integer contains user id
    * @param $group_id integer contains group id
    * @param $link_id string contains link id
    */
     function get_user_limits($user_id, $group_id, $link_id) {
        global $wpdb;
        
        $users_limit = $wpdb->prefix.'tti_users_limit';
        $results = $wpdb->get_row("SELECT * FROM $users_limit WHERE user_id ='$user_id' AND data_link = '$link_id'");
        
        if(isset($results) && !empty($results)) {
        	foreach ($results as $key => $value) {
        		if (strpos($results->group_id, $group_id) !== false) {
				    return $results->limits;
				}
        	}
        } elseif(empty($results)) {
        	$assessment_table_name = $wpdb->prefix.'assessments';
		    $results = $wpdb->get_row("SELECT * FROM $assessment_table_name WHERE user_id ='$user_id' AND link_id='$link_id' AND status = 1");
		   
		    if(isset($results) && !empty($results)) {
		    	return '0';
		    }
		    return '1';
        }
        return '0';
    }
