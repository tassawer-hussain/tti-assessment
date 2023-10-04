<?php
/**
 * Class to process retake assessment tab contents display template.
 *
 * @since      4.0
 * @package    Ld_Group_Registration
 * @subpackage Ld_Group_Registration/modules/templates/ldgr-group-users/tabs
 * @author     WisdmLabs <support@wisdmlabs.com>
 */


?>

<div id="tab-4" class="tab-content"> 
	<input type='button' id='bulk_remove' value='<?php esc_html_e( 'Bulk Remove', 'tti-platform' ); ?>'>

	<table id='tti_group_leader_retake'>
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
							<button 
								id="tti_retake_assessment_<?php echo esc_attr( $value ); ?>" 
								class="tti_retake_assessment" 
								data-user_id ="<?php echo esc_attr( $value ); ?>" 
								data-group_id="<?php echo esc_attr( $group_id ); ?>"
								data-mail = "<?php echo esc_attr( $user_data->user_email ); ?>" >
							<?php esc_html_e( 'Retake Assessment', 'tti-platform' ) ?>
						</button>
						<img src="<?php echo TTI_PLAT_GL_IMG_ASSETS_PATH.'ttisi-spinner.gif'; ?>" id="retake_assessment_loader_<?php echo esc_attr( $value ); ?>" class="retake_assessment_loader" style="display: none"/>
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
