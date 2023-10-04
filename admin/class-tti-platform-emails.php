<?php

/**
 * Class to extend Learndash board email settings functionality.
 *
 * This class defines all code necessary to extend Learndash board email settings functionality.
 *
 * @since       1.0.0
 * @package     TTI_Platform
 * @subpackage  TTI_Platform/includes
 * @author      presstiger <support@presstigers.com>
 */
class TTI_Platform_Emails_Class
{

    /**
     * Define the constructor
     *
     * @since  1.6.1
     */
    public function __construct()
    {
        add_action('ldgr_action_email_settings_form_end', array(
            $this,
            'show_retake_assess_option'
        ));
        if ( isset( $_POST['sbmt_wdm_gr_email_setting'] ) ) {
            $this->save_email_settings_option();
        }
    }

    /**
     * Function to show email options in Learndash board email settings.
     *
     * @since  1.6.1
     */
    public function show_retake_assess_option()
    {   
        $this->output_retake_assess_option();
        
    }

    /**
     * Function to output email settings in Leardash board.
     *
     * @since  1.6.1
     */
    public function output_retake_assess_option()
    {
        $gl_rmvl_sub_retake_assess = get_option('wdm-gr-retake-assessment');
        $gl_rmvl_body_retake_assess = get_option('wdm-u-add-gr-body-retake-assess');

?>
<br />
<div class="accordion"><b><?php _e('When Group Leader allow user to retake assessment (Group Leader)', 'tti-platform'); ?></b></div>
            <div class="panel">
                <br><table>
                    <tr>
                        <td class="wdm-label">
                            <label for="wdm-gr-retake-assessment"><?php _e('Subject : ', 'tti-platform'); ?></label>
                        </td>
                        <td>
                            <input type="text" name="wdm-gr-retake-assessment" id="wdm-gr-retake-assessment" size="50" value="<?php echo $gl_rmvl_sub_retake_assess; ?>">
                            <span class="wdm-help-txt"><?php _e('Enter Subject for Email sent to User allowed to retake assessment <br/> Default : leave blank', 'tti-platform'); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <td class="wdm-label">
                            <label for="wdm-u-add-gr-body"><?php _e('Body : ', 'tti-platform'); ?></label>
                        </td>
                        <td>
                            <?php
        $editor_settings = array(
            // 'wpautop'=>true,
            'media_buttons' => false,
            'drag_drop_upload' => false,
            'textarea_rows' => 15,
            'textarea_name' => 'wdm-u-add-gr-body-retake-assess',
        );
        wp_editor(stripslashes($gl_rmvl_body_retake_assess) , 'wdm-u-add-gr-body-retake-assess', $editor_settings);
?>
                            <span class="wdm-help-txt"><?php _e('Enter Body for Email sent to User allowed to retake assessment <br/> Default : leave blank', 'tti-platform'); ?></span>
                        </td>
                        <td class="wdm-var-sec">
                            <div>
                                <span class="wdm-var-head"><?php _e('Available Variables', 'tti-platform'); ?></span>
                                <ul>
                                    <li><b>{assessment_title}</b> : <?php _e('Displays Assessment Title', 'tti-platform'); ?></li>
                                    <li><b>{group_leader_name}</b> : <?php _e('Displays Group Leader Name', 'tti-platform'); ?></li>
                                    <li><b>{user_first_name}</b> : <?php _e("Displays User's First Name", 'tti-platform'); ?></li>
                                    <li><b>{user_last_name}</b> : <?php _e("Displays User's Last Name", 'tti-platform'); ?></li>
                                    <li><b>{user_email}</b> : <?php _e("Displays User's Email", 'tti-platform'); ?></li>
                                    <li><b>{login_url}</b> : <?php _e('Displays Login URL', 'tti-platform'); ?></li>
                                    <li><b>{reset_password}</b> : <?php _e('Displays Reset Password link for user', 'tti-platform'); ?></li>
                                    <li><b>{site_name}</b> : <?php _e('Displays Site Name', 'tti-platform'); ?></li>
                                    <li><b>{course_list}</b> : <?php _e('Displays Course List', 'tti-platform'); ?></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                </table><br>
            </div><br>
<?php
    }

    /**
     * Function to save email settings.
     *
     * @since  1.6.1
     */
    public function save_email_settings_option() {
        $gl_rmvl_sub_retake_assess = $_POST['wdm-gr-retake-assessment'];
        $gl_rmvl_body_retake_assess = $_POST['wdm-u-add-gr-body-retake-assess'];
        update_option("wdm-gr-retake-assessment", trim($gl_rmvl_sub_retake_assess));
        update_option("wdm-u-add-gr-body-retake-assess", trim($gl_rmvl_body_retake_assess));
    }
}

