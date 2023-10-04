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
global $links_id;

/* Get link ids related to current group */
get_the_current_link_id($group_id);

?>

<div id="tab-1" class="tab-content current"> 
	<input type='button' id='bulk_remove' value='<?php esc_html_e( 'Bulk Remove', 'tti-platform' ); ?>'>

	<table id='wdm_group'>
		<thead>
			<tr>
				<th><input type="checkbox" name="select_all" class="bb-custom-check"></th>
				<th><?php esc_html_e( 'Name', 'tti-platform' ); ?></th>
				<th><?php esc_html_e( 'Email', 'tti-platform' ); ?></th>
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
							$text_data  = __( 'Remove', 'tti-platform' );
						} else {
							$class_data = 'request_sent';
							$text_data  = __( 'Request sent', 'tti-platform' );
						}
						?>
						<td class="ldgr-actions">
							
							<?php if ( apply_filters( 'wdm_ldgr_remove_user_button', true, $value, $group_id ) ) { ?>
								<!-- <a
									href="#"
									data-user_id ="<?php echo esc_html( $value ); ?>"
									data-group_id="<?php echo esc_html( $group_id ); ?>"
									class="<?php echo esc_html( $class_data ); ?> button"><?php echo esc_html( $text_data ); ?></a> -->
							<?php } ?>
							<?php
								// $value contains user_id
								foreach ($links_id as $key => $link_id) { 
									$ass_comp_re = check_current_user_completed_assessment($value, $link_id);
									if($ass_comp_re) {
										echo '<span class="assess-complete-text">Assessment Completed Successfully</span>';
										echo '<br />';
									} else {
										/* Check if user logged in or not */
										$user_last_login = check_last_login($value);
										if($user_last_login) {
											echo '<span class"logged-in">User Logged In Already</span>';
										} else {
											if ( 'on' == $ldgr_reinvite_user ) { ?>
											<a
												href="#"
												data-user_id ="<?php echo esc_html( $value ); ?>"
												data-group_id="<?php echo esc_html( $group_id ); ?>"
												class="<?php echo esc_html( $reinvite_class_data ); ?> button"><?php echo esc_html( $reinvite_text_data ); ?></a>&nbsp;
											<?php }
										}
									}
									

								}
							?>
						</td>
					</tr>
					<?php
				}
			}
			?>
		</tbody>
	</table>
</div>

</form><!-- End of first Tab  -->


<?php


/**
*  Check current user completed assessment status.
*/
function check_current_user_completed_assessment($c_usrid, $link_id) {
	global $wpdb;
	$assessment_table_name = $wpdb->prefix.'assessments';
    $results = $wpdb->get_row("SELECT * FROM $assessment_table_name WHERE user_id ='$c_usrid' AND link_id='$link_id'");
    if(isset($results) && count($results) > 0){
    	return true;
    }
    return false;
}

/**
* Check if user logged-in atlease once
*/
function check_last_login($user_id) {
	$user_last_login = get_user_meta( $user_id, 'last_login', true );
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
        $args = array( 'post_type' => 'tti_assessments');
        $loop = new WP_Query( $args );
        while ( $loop->have_posts() ) : $loop->the_post();
            $searc_string = '[take_assessment assess_id="'.get_the_id().'"';
            if (strpos($content, $searc_string) !== false) {
                $links_id[] = get_post_meta( get_the_id(), 'link_id', true );
            }
        endwhile;
    }