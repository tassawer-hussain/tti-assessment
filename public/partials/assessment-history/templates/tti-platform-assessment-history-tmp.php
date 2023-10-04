<?php

/**
 * Template to show user assessment details
 *
 * This is used to define internationalization, dashboard-specific hooks, and
 * public-facing site hooks.
 *
 * @since   1.6
 * @package     TTI_Platform
 * @subpackage  TTI_Platform/includes
 * @author  Presstigers
 */

?>


<?php 

$print_optin = get_post_meta($assess_id, 'print_report', true);
if(strtolower($print_optin) == 'yes') {
	
?>

<?php if($show_as_link == 'yes') { ?>
	<div class="tti-asses-history-sliderbutton">
        <a ><?php echo __('Click here to show/hide assessment history', 'tti-platform'); ?></a>
    </div>
<?php } ?>	
<div class="tti-asses-history-slider" <?php if($show_as_link == 'yes') { ?> <?php } ?> data-show_link='<?php echo esc_attr($show_as_link); ?>' style="display:block;">
<div class="tti-user-assess-history">
	<ol class="tti-activity-feed">
	<?php 
		if(isset($data) && !empty($data)) {
			
			$assess_title = get_the_title($assess_id);

			echo '<h3 style="margin-top:0px;">'.__('Assessment History', 'tti-platform').'</h3>';
			foreach ($data as $key => $value) {
				?>
					<li class="feed-item"><?php 
						$now = new DateTime($value->created_at);
						$newDateString = $now->format('M j, Y');
						//echo $newDateString.'<br />'; 
					?>
					<time class="date" ><?php echo $newDateString; ?></time>
					<?php
						if(isset($value->version)) {
							$version = $value->version;
						} else {
							$version = 1;
						}
						//$pageURL = esc_url_raw($_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"]);
						$pageURL = get_site_url();
					?>
					<div class="assessment-details" id="user-assess-<?php echo $version; ?>">
						<span><?php echo __('Title', 'tti-platform'); ?> : <strong><?php echo $assess_title; ?></strong></span><br/>
						<span><?php echo __('Name', 'tti-platform'); ?> : <strong><?php echo $value->first_name.' '.$value->last_name; ?></strong></span><br/>
						<span><?php echo __('Email', 'tti-platform'); ?> : <strong><?php echo $value->email; ?></strong></span><br/>
						<?php 
							if(isset($value->company) && !empty($value->company)){
								?>
								<span><?php echo __('Company', 'tti-platform'); ?>  : <strong><?php echo $value->company; ?></strong></span><br/>
								<?php
							}
						?>
						<?php 
							if(isset($value->gender) && !empty($value->gender)){
								?>
								<span><?php echo __('Gender', 'tti-platform'); ?> : <strong><?php echo $value->gender == 'M' ? 'Male' : 'Female'; ?></strong></span><br/>
								<?php
							}
						?>
						<?php 
							if(isset($value->position_job) && !empty($value->position_job) && $value->position_job != 'none'){
								?>
								<span><?php echo __('Position Job', 'tti-platform'); ?> : <strong><?php echo $value->position_job; ?></strong></span><br/>
								<?php
							}
						?>
						<?php 
							if(isset($value->version) && !empty($value->version)){
								?>
								<span><?php echo __('Version', 'tti-platform'); ?> : <strong><?php echo $value->version.'.0'; ?></strong></span><br/>
								<?php
							}
						?>
					</div>
    				<span ><a ><?php echo '<a assessment-id="'.esc_attr($assess_id).'" class="download_pdf_user_history" href="'.$pageURL.'?assessment_id='.esc_attr($assess_id).'&version='.$value->version.'" target="_blank"><img width="40" src="'. TTI_PLAT_AH_IMG_ASSETS_PATH . 'download.png" alt="" /> <span class="download-text">'.__('Download Assessment', 'tti-platform').'</span></a>'; ?></a></span>
						
					</li>
				<?php				
			}
		} else {
			_e('No Assessment History', 'tti-platform');
		}
	?>
</ol>
</div>
</div>
<?php } ?>