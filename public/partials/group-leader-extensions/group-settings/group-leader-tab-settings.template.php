<?php
/**
 * Group settings tab template (Procedural Programming)
 *
 * @since      1.6.5
 * @package    TTI_Platform
 * @subpackage TTI_Platform/includes
 * @author     Presstigers
 */


global $wpdb;
$meta_key = 'learndash_group_leaders_'.$group_id;
$sql_str  = $wpdb->prepare(
                "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = '%s'",
                $meta_key );
$group_leaders = $wpdb->get_results($sql_str);

$leader = $group_leaders[0]->user_id;

if( ! $leader ) {
    $leader = get_current_user_id();
}

$key = 'group_user_'.$leader.'_settings';
$setting_block_email = get_user_meta($leader, $key, 'false');

// echo $setting_block_email.'--'.$key;
if($setting_block_email == 'false') {
    $setting_block_email_checkout = 'checked';
    $setting_block_email_class = 'switch-gp-settings-left-on';
}

if($setting_block_email == 'true' || "" == $setting_block_email ) {
    $setting_block_email_checkout = '';
    $setting_block_email_class = 'switch-gp-settings-left-off';
}

// echo '--------------';
// echo $setting_block_email_class;
?>
<div id="tab-4" class="tab-content tab-group-settings">
	<form id="pt-group-form-settings-form">
			<h4>Settings</h4>
			<div class="group-setting-one">
				<div class="pt-left">
					<h5>Send User Enrollment Emails</h5>
				</div>
				<div class="pt-middle">
					<p>Turn on or off the sending of the enrollment emails to your client. You will always get a copy of the enrollment email no mater how this option is selected.</p>
				</div>
				<div class="pt-right">
					<input type="checkbox" id="toggle" class="checkbox-gp-settings" <?php echo esc_attr($setting_block_email_checkout); ?> />  
    				<div class="enroll-user-btn">
                        <span>Off</span>
                        <label for="toggle" class="switch-gp-settings <?php echo esc_attr($setting_block_email_class); ?>"></label>
                        <span>On</span>
                    </div>
				</div>
				</div>
			<div style="clear:both;"></div>

			<input type="hidden" class="group-leader-gp-settings-glid" data-group_leader_id="<?php echo esc_attr( $leader ); ?>"  value="" />

			<input type="hidden" class="group-leader-gp-settings-gid" data-group_id="<?php echo esc_attr( $group_id ); ?>" value="" />

		<!-- <button type="button" class="pt-group-form-settings-btn" id="pt-group-form-settings-form-btn">Save</button> -->
	</form>
</div>

