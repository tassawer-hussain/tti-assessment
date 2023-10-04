<?php
/**
*   User profile assessment tab template
*/

?>
<!-- tabs -->
<div id="tti-user-ass-tabs">
    <h2 id="tti-profile-user-nav-inner" class="tti-nav-tab-wrapper" >
        <a class="tti-nav-tab1" href="<?php echo esc_url($profile_url); ?>"><?php _e( 'Profile', 'tti-platform' ); ?></a>
        <a class="tti-nav-tab2 tti-active" href="<?php echo esc_url($tab_url); ?>"><?php _e( 'Assessments', 'tti-platform' ); ?></a>
    </h2>
    <div style="clear: both;"></div>
</div>



<!-- Inner tabs -->
<div class="tti-user-tab">
  <button class="tablinks tti-user-active-tab" id="list-assess">Assessments List</button>
  <button class="tablinks" id="add-user-assess">Add Assessment</button>
  <button class="tablinks" id="add-user-settings">Settings</button>
  <div style="clear: both;"></div>
</div>
<div style="clear: both;"></div>

<!-- Tab content -->
<div id="list-assess-content" class="tabcontent list-assess">
  <?php require plugin_dir_path( __FILE__ ) . 'tti-platform-user-template-lists.php'; ?>
</div>
<div id="add-user-assess-content" class="tabcontent add-user-assess" style="display: none;">
  <?php require plugin_dir_path( __FILE__ ) . 'tti-platform-user-template-add.php'; ?>
</div>
<div id="list-assess-settings" class="tabcontent add-user-settings" style="display: none;">
  <?php require plugin_dir_path( __FILE__ ) . 'tti-platform-user-settings.php'; ?>
</div>

