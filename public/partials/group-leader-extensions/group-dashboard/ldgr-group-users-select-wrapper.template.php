<?php
/**
 * LDGR Group Users [wdm_group_users] shortcode group select wrapper display template
 *
 * @since      4.0
 * @package    Ld_Group_Registration
 * @subpackage Ld_Group_Registration/modules/templates/ldgr-group-users
 * @author     WisdmLabs <support@wisdmlabs.com>
 */

?>
<div class="ldgr-group-listing">
	<div class="ldgr-search-groups">
		<i class="ldgr-icon-Search"></i>
		<input type="text" placeholder="Search by group name" class="ldgr-search">
	</div>

	<style>
		/* Style Group Courses list */
		.ttisi-course-wrapper {
			margin-left: 45px;
    		margin-top: 15px;
		}
		.ttisi-course-wrapper p {
			padding: 0px;
			font-size: 16px;
			font-weight: 700;
			line-height: 1em;
		}
		.ttisi-course-wrapper ul {
			padding: 0px;
			list-style: none;
			line-height: 20px;
		}
		.ttisi-course-wrapper h2 {
			line-height: 14px;
			padding: 0px;
			margin: 0px 0px 10px 30px;
		}
		.ttisi-course-wrapper h2 span.ttisi-ldgr-group-course-name {
			margin: 0px !important;
		}
	</style>

	<div class="ldgr-group-items list">
	<?php
	foreach ( $group_ids as $key => $group ) {
		$group_limit     = intval( get_post_meta( $group, 'wdm_group_users_limit_' . $group, true ) );
		$grp_limit_count = ( $group_limit < 0 ) ? 0 : $group_limit;
		$grp_name        = get_the_title( $group );
		$sub_grp_ids     = $Ld_Group_Registration_Sub_Groups->get_all_sub_group_ids( $group );
		?>
			<div class="ldgr-group-item">
				<div class="ldgr-main-group-content">
					<span class="gr-edit">
						<form class="wdm_search_submit" method='post'>
							<input name="wdm_group_id" type="hidden" value="<?php echo esc_attr( $group ); ?>">
							<button type="submit" style="background: transparent;color: #333;padding: 0;"><i class="ldgr-icon-Edit"></i></button>
						</form>
					</span>
					<span class="gr-icon">
						<!-- <i class="ldgr-icon-Language"></i> -->
						<?php $Ld_Group_Registration_Groups->display_group_image( $group, 100 ); ?>
					</span>

					<div class="ttisi-group-wrapper">
						<h2 class="gr-title"><?php echo esc_html( $grp_name ); ?></h2>
						<div class="ttisi-course-wrapper">
							<p>Included Courses</p>
							<ul>
							<?php
								$ldgr_group_courses = get_option( 'ldgr_group_courses' );
								$group_courses      = array();
								if ( 'on' == $ldgr_group_courses ) {
									$group_courses = learndash_group_enrolled_courses( $group );
								}
								foreach ( $group_courses as $course_id ) : ?>
									<li><h2><span class="gr-title ttisi-ldgr-group-course-name" title="<?php echo esc_attr( get_the_title( $course_id ) ); ?>"><?php echo esc_html( get_the_title( $course_id ) ); ?></span></h2></li>
							<?php endforeach; ?>
							</ul>
						</div>
					</div>

					<div class="gr-left">
					<?php if ( ! get_post_meta( $group, 'ldgr_unlimited_seats', 1 ) ) : ?>
						<?php
						echo sprintf(
							// translators: Group limit count.
							esc_html__( '%d user registration left', 'wdm_ld_group' ),
							$grp_limit_count
						);
						?>
					<?php endif; ?>
					</div>
				</div>
				<?php if ( $is_heirarchical && ! empty( $sub_grp_ids ) ) : ?>
					<div class="ldgr-group-subgroups">
						<h3 class="ldgr-sub-group-label"><?php echo esc_html( \LearnDash_Custom_Label::get_label( 'subgroups' ) ); ?></h3>
						<?php foreach ( $sub_grp_ids as $sub_group_id ) : ?>
							<div class="ldgr-sub-group-item">
								<span class="sub-group-name" title="<?php echo esc_html( get_the_title( $sub_group_id ) ); ?>">
									<?php echo esc_html( mb_strimwidth( get_the_title( $sub_group_id ), 0, 20, '...' ) ); ?></span>
								<span class="sub-group-u-left">
									<?php
									// translators: No of seats.
									echo sprintf(
										esc_html__( 'No of seats %d', 'wdm_ld_group' ),
										get_post_meta( $sub_group_id, 'wdm_group_users_limit_' . $sub_group_id, 1 )
									); ?>
								</span>
								<form class="wdm_search_submit" method='post'>
									<input name="wdm_group_id" type="hidden" value="<?php echo esc_attr( $sub_group_id ); ?>">
									<button type="submit" style="background: transparent;color: #333;padding: 0;">
										<i class="ldgr-icon-Edit"></i>
									</button>
								</form>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		<?php
	}
	?>
	</div>
	<ul class="ldgr-pagination"></ul>
</div>
