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
$counter = 0;

global $links_id;
global $assess_ids;

/* Get link ids related to current group */
get_the_current_link_id($group_id);
if(isset($links_id) && count($links_id) > 0) {
	$links_id = array_unique($links_id);
	$assess_ids = array_unique($assess_ids);
}



?>
<div id="tab-0" class="tab-content current"> 
	<input type='button' id='bulk_remove' value='<?php esc_html_e( 'Bulk Remove', 'tti-platform' ); ?>'>
	<table id='tti_group_leader_retake' class="display responsive nowrap">
		<thead>
			<tr>
				<!-- <th><input type="checkbox" name="select_all" class="bb-custom-check"></th> -->
				<th><?php esc_html_e( 'Name', 'tti-platform' ); ?></th>
				<th><?php esc_html_e( 'Email', 'tti-platform' ); ?></th>
				<!-- <th><?php esc_html_e( 'Retake Assessment', 'tti-platform' ); ?></th> -->
				<!-- <th><?php esc_html_e( 'Users Limit', 'tti-platform' ); ?></th> -->
				<th><?php esc_html_e( 'Report Details', 'tti-platform' ); ?></th>
				<th><?php esc_html_e( 'Status', 'tti-platform' ); ?></th>
				<th><?php esc_html_e( 'Action', 'tti-platform' ); ?></th>
			</tr>
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
				foreach ($links_id as $key => $link_id) { 
					$new_users_arr[$link_id] = $users;
					$assess_names[$link_id] = get_the_title($assess_ids[$key]);
					$assessment_ids[$link_id] = $assess_ids[$key];
				}
				
				

				foreach ( $new_users_arr as $link_id => $values ) { // $value contains user id
					foreach ( $values as $k => $value ) {
					
					$user_data = get_user_by( 'id', $value );

					$counter++;
					?>
					<tr id="userid-<?php echo  $value; ?>-<?php echo  $link_id; ?>">
					<!-- 	<td class="select_action">
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
						
						<td class="ldgr-pdf-details">
							<span><?php echo '<span class="ttisi-info-blue-color ttisi-info-gp ttisi-pos-rel-tp-6-minus">'.$assess_names[$link_id].'</span>'; ?></span>
                          <?php
                          		$completed_ass_count = array();
                          		//foreach ($links_id as $key => $link_id) { 
									$pdf_version = get_current_assess_user_count($value, $link_id);
									$completed_ass_count[] = $pdf_version;
									if($pdf_version) {
										?>
											<a target="_blank" id="tti_cp_download_btn" data-email="<?php echo esc_attr($value->email); ?>" data-assess="<?php echo esc_attr($assess_id); ?>" href="<?php echo get_site_url().'?cp_page=true&user_id='.esc_html($value).'&assessment_id='.$assessment_ids[$link_id]; ?>&version=<?php echo esc_attr($pdf_version); ?>&group_leader=true" >
		                            		<u><img class="tti-gp-column" src="<?php echo TTI_PLAT_GL_IMG_ASSETS_PATH.'download.png' ?>" />
		                            			<?php esc_html_e('Download Latest PDF','tti-platform'); ?> </u>
		                            		</a>
		                            		<sub><strong style="font-size: 11px;">(<?php esc_html_e('Version','tti-platform'); ?> : <?php echo $pdf_version.'.0'; ?>)</strong></sub>
		                            		
										<?php
									} else {
										?>
										<span class="ttisi-info-gp"><?php esc_html_e( 'No PDF Available', 'tti-platform' ); ?></span>
									<?php 
								}
									//echo '<br />';
								//}
                          ?>
						</td>
						<td class="ldgr-user-limit">
							<?php 
								// $value contains user_id
								//foreach ($links_id as $key => $link_id) { 
									//echo 'Link ID : ' . get_the_title($assess_ids[]).'<br />';
									$ass_comp_re = check_current_user_completed_assessment($value, $link_id);
									if($ass_comp_re) {
										echo '<small class="assess-complete-text ttisi-info-gp ttisi-info-success-color">Assessment Completed Successfully</small>';
										//echo '<br />';
										if(isset($completed_ass_count) && count($completed_ass_count) > 0) {
											foreach ($completed_ass_count as $key => $count) { 
												if(empty($count)) {
													echo '<small class="ttisi-info-gp ttisi-info-warning-color ttisi-pos-rel-tp-6 ">'.__('No of completed assessments', 'tti-platform').' : 0</small>';
												} else {
													echo '<small class="ttisi-info-gp ttisi-info-warning-color ttisi-pos-rel-tp-6 ">'.__('No of completed assessments', 'tti-platform').' : '.$count.'</small>';
												}
											}
										} 
									} else {
										

										/* Check if user logged in or not */
										$user_last_login = check_last_login($value);
										if($user_last_login) {
											echo '<small class="logged-in ttisi-info-gp ttisi-info-logged-color">'.__('User Only Logged In', 'tti-platform').'</small>';
										} 

										//echo '<br />';
										echo '<small class="ttisi-info-gp ttisi-info-warning-color ttisi-pos-rel-tp-6 ">'.__('No of completed assessments', 'tti-platform').' : 0</small>';

									}
									//echo '<br />';
								//}
							?>
							<!-- Show user limits -->
							<?php  
								//foreach ($links_id as $key => $link_id) { 
							echo '<small class="ttisi-info-gp ttisi-info-limit-color ttisi-info-limit ttisi-pos-rel-tp-6">'.__('Remaining User Limit', 'tti-platform').' : '.get_user_limits($value, $group_id, $link_id).'</small>';
							//echo '<br />';
									
								//}
							 ?>
						</td>
						<td class="ldgr-actions">
						
						<span  style="overflow: visible; position: relative; width: 80px;">
						   <div class="dropdown">
						      <a style="width: 73%;" id="row-<?php echo $counter; ?>" class="gl-action-btn btn btn-sm btn-clean btn-icon btn-icon-md ttisi-blue-color" > 
						      		Select Action   <i class="fa fa-caret-down" aria-hidden="true"></i> 
						      </a>     

						      <div class="dropdown-menu dropdown-menu-right row-<?php echo $counter; ?> gl-action-drodown" x-placement="top-end" style="display: none;will-change: transform;z-index: 999999;position: absolute;left: auto;right: auto; margin: auto;">
						         <ul class="kt-nav">
						            <li class="kt-nav__item"> <a class="kt-nav__link" href="#"> <span class="kt-nav__link-text">
									<span 
									    type="button"
										id="tti_retake_assessment_<?php echo esc_attr( $value ); ?>" 
										class="tti_retake_assessment" 
										data-link_id ="<?php echo esc_attr( $link_id ); ?>" 
										data-user_id ="<?php echo esc_attr( $value ); ?>" 
										data-group_id="<?php echo esc_attr( $group_id ); ?>" 
										data-group_leader_id="<?php echo esc_html( get_current_user_id() ); ?>" 
										data-mail = "<?php echo esc_attr( $user_data->user_email ); ?>" >
									<?php esc_html_e( 'Retake Assessment', 'tti-platform' ); ?>
								</span>
						        </span> </a></li>
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
												class="<?php echo esc_html( $reinvite_class_data ); ?> button"><?php echo esc_html( $reinvite_text_data ); ?></span>&nbsp;
											<?php }
										}
									}
						              ?>
						            </li>
						           <?php if ( apply_filters( 'wdm_ldgr_remove_user_button', true, $value, $group_id ) ) { ?>
						            	<li class="kt-nav__item"> 
											 <span
												href="#"
												data-user_id ="<?php echo esc_html( $value ); ?>"
												data-group_id="<?php echo esc_html( $group_id ); ?>"
												class="<?php echo esc_html( $class_data ); ?> button"><?php echo esc_html( $text_data ); ?>
													
												</span>
											</li>
										<?php } ?>
						         </ul>
						      </div>
						   </div>
						</span>

							
							
							
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
function get_current_assess_user_count($c_usrid, $link_id) {
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
	$user_last_login = get_user_meta( $user_id, 'last_activity', true );
	if(empty($user_last_login)) {
		return false;
	}
	return true;
}

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
                get_links_id_by_content_id($content_id);
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
     function get_links_id_by_content_id($content_id) {
        $content_post = get_post($content_id);
        if(isset($content_post->post_content)) {
            $content = $content_post->post_content;
            $content = wpautop( $content_post->post_content );
            match_all_assessment_ids($content);
        }
    }

      /**
    * Check if assessment id exists.
    */
     function match_all_assessment_ids($content) {
     	global $links_id;
     	global $assess_ids;
        $args = array( 'post_type' => 'tti_assessments');
        $loop = new WP_Query( $args );
        while ( $loop->have_posts() ) : $loop->the_post();
            $searc_string = '[take_assessment assess_id="'.get_the_id().'"';
            if (strpos($content, $searc_string) !== false) {
                $links_id[] = get_post_meta( get_the_id(), 'link_id', true );
                $assess_ids[] =  get_the_id();
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
