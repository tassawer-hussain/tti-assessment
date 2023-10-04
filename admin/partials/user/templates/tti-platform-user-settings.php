<?php
/**
*   User assessment settings tab content
*/


$capability_ass = isset($settings_data['user_capa']) ? $settings_data['user_capa'] : 'No';
?>
<!-- add tab content -->

<div class="user-settings-form">
   
   <div class="user_per" id="user_per_settings">
      <h3>Apply User Assessment Capability</h3>
      <input type="radio" <?php if($capability_ass == 'Yes') { echo 'checked '; } ?> name="user_capa" id="user_per_yes" value="Yes"> <label for="user_per_yes">Yes</label>
      <input type="radio" <?php if($capability_ass == 'No') { echo 'checked '; } ?> name="user_capa" id="user_per_no" value="No"> <label for="user_per_no">No</label>
    </div>
  <input type="hidden" name="tti_user_id" id="tti_user_id" value="<?php echo esc_attr($user_id); ?>">
   <div class="print_report">
      <div class="add_user_settings">
        <button type="button" class="button button-primary button-large" id="add_user_settings">Save</button>
        <span id="loader_settings_assessment"><img src="<?php echo site_url() . '/wp-content/plugins/tti-platform/admin/images/loader.gif'; ?>" alt="" width="20"></span>
        </div>
      </div>
   </div>

</div>