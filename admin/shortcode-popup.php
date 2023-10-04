<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once('../../../../wp-load.php');
require_once('../../../../wp-config.php');
require_once('../../../../wp-includes/load.php');
require_once('../../../../wp-includes/plugin.php');
    
wp_head();

?>

<!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script> -->
<script type="text/javascript">
    $( document ).ready(function() {
        var cl = $('#ttisi-shortcode-generator-block').clone();
        $('body').empty();
        $('body').append(cl);
        $('jdiv').remove();
    });
</script>

<body style="background: transparent !important;">

<!-- TTISI shortcode generator block -->
<div id="ttisi-shortcode-generator-block">
    <!-- TTISI CSS Style -->
    <style>
    #ttisi-shortcode-generator-block button, 
    #ttisi-shortcode-generator-block h3,
    #ttisi-shortcode-generator-block strong {
        font-family: Arial !important;
    }
    #ttisi-shortcode-generator-block strong {
        font-size: 13px;
    }
    jdiv { display: none !important; }
    </style>
    <div class="tti-platform-loader-admin">
        <img src="<?php echo plugin_dir_url(__FILE__) . '/images/loader.gif'; ?>" alt="" />
    </div>
    <div class="tti-platform-tab">
        <?php
            /**
             * Fires before settings page content.
             * 
             * @since   1.2
             */
            do_action('ttisi_platform_shortcode_generator_popup_left_menu_before');
        ?>
        <button class="tti-platform-tablinks active" onclick="openTab(event, 'Assessment')"><?php _e('Assessment', 'tti-platform'); ?></button>
        <button class="tti-platform-tablinks" onclick="openTab(event, 'Text')"><?php _e('Text Feedback', 'tti-platform'); ?></button>
        <button class="tti-platform-tablinks" onclick="openTab(event, 'Graphic')"><?php _e('Graphic Feedback', 'tti-platform'); ?></button>
        <button class="tti-platform-tablinks" onclick="openTab(event, 'PDF')"><?php _e('PDF Download', 'tti-platform'); ?></button>
        <button class="tti-platform-tablinks" onclick="openTab(event, 'Tti_Cons_Report')"><?php _e('Consolidation Report', 'tti-platform'); ?></button>
        <?php
            /**
             * Fires after settings page content.
             * 
             * @since   1.2
             */
            do_action('ttisi_platform_shortcode_generator_popup_left_menu_after');
        ?>
    </div>
    <div class="tab-detail">
        <div id="Assessment" class="tabcontent" style="display: block;">
            <?php
                /**
                 * Fires before shortcode generator popup assessment block 
                 * 
                 * @since   1.2
                 */
                do_action('ttisi_platform_shortcode_generator_assessment_block_before');
            ?>
            <h3><?php _e('Assessment', 'tti-platform'); ?></h3>
            <p><?php _e('To have a participant take an assessment select the assessment below and click Insert Code.', 'tti-platform'); ?></p>
            
            <div id="assessment_list">
                <p><strong><?php _e('Select Assessment:', 'tti-platform'); ?></strong> <select><option></option></select></p>
            </div>
            <?php
                /**
                 * Fires after shortcode generator popup assessment block 
                 * 
                 * @since   1.2
                 */
                do_action('ttisi_platform_shortcode_generator_assessment_block_after');
            ?>
        </div>

        <div id="Text" class="tabcontent">
            <?php
                /**
                 * Fires before shortcode generator popup assessment text feedback_ block 
                 * 
                 * @since   1.2
                 */
                do_action('ttisi_platform_shortcode_generator_assessment_text_feedback_before');
            ?>
            <h3><?php _e('Text Feedback', 'tti-platform'); ?></h3>
            <p><?php _e('To text feedback complete the options below and click Insert Code.', 'tti-platform'); ?></p>
            
            <div id="assessment_list_text">
                <p><strong><?php _e('Select Assessment:', 'tti-platform'); ?></strong> <select id="assessment_list_for_text"><option></option></select></p>
                <p><strong><?php _e('Select Feedback:', 'tti-platform'); ?></strong> <select id="assessment_list_text_feedback"><option></option></select></p>
            </div>
            
            <div id="assessment_checklist">
                <div class="rowTitles"></div>
                <div class="checklist_feedback"></div>
            </div>
            <?php
                /**
                 * Fires after shortcode generator popup assessment text feedback_ block 
                 * 
                 * @since   1.2
                 */
                do_action('ttisi_platform_shortcode_generator_assessment_text_feedback_after');
            ?>
        </div>

        <div id="Graphic" class="tabcontent">
            <?php
                /**
                 * Fires before shortcode generator popup assessment text graphic block 
                 * 
                 * @since   1.2
                 */
                do_action('ttisi_platform_shortcode_generator_assessment_graphic_feedback_before');
            ?>
            <h3><?php _e('Graphic', 'tti-platform'); ?></h3>
            <p><?php _e('To graphic feedback complete the options below and click Insert Code.', 'tti-platform'); ?></p>
            
            <div id="assessment_list_graphic">
                <p><strong><?php _e('Select Assessment:', 'tti-platform'); ?></strong> <select id="assessment_list_for_graphic"><option></option></select></p>
                <p><strong><?php _e('Select Feedback:', 'tti-platform'); ?></strong> <select id="assessment_list_graphic_feedback"><option></option></select></p>
            </div>
            
            <div id="assessment_checklist_for_graphic">
                <div class="rowTitles"></div>
                <div class="checklist_feedback_graphic"></div>
            </div>
            <?php
                /**
                 * Fires after shortcode generator popup assessment text graphic block 
                 * 
                 * @since   1.2
                 */
                do_action('ttisi_platform_shortcode_generator_assessment_graphic_feedback_after');
            ?>
        </div>

        <div id="PDF" class="tabcontent">
            <?php
                /**
                 * Fires before shortcode generator popup assessment text graphic block 
                 * 
                 * @since   1.2
                 */
                do_action('ttisi_platform_shortcode_generator_assessment_pdf_feedback_before');
            ?>
            <h3><?php _e('PDF Download', 'tti-platform'); ?></h3>
            <p><?php _e('To PDF download, click Insert Code.', 'tti-platform'); ?></p>

            <div id="assessment_list_pdf">
                <p>
                    <strong><?php _e('Select Assessment:', 'tti-platform'); ?></strong>
                    <select id="assessment_list_for_pdf">
                        <option></option>
                    </select>
                </p>
            </div>

    
            
            <?php
                /**
                 * Fires after shortcode generator popup assessment text graphic block 
                 * 
                 * @since   1.2
                 */
                do_action('ttisi_platform_shortcode_generator_assessment_pdf_feedback_after');
            ?>
        </div>
        
                <!-- Consilidation Report Shortcode -->
        <div id="Tti_Cons_Report" class="tabcontent">
            <?php
                /**
                 * Fires before shortcode generator popup assessment consilidation report
                 * 
                 * @since   1.2
                 */
                do_action('ttisi_platform_shortcode_generator_assessment_pdf_consilidation_report_before');
            ?>
            <h3><?php _e('Consolidation PDF Report', 'tti-platform'); ?></h3>
            <p><?php _e('To PDF download, click Insert Code.', 'tti-platform'); ?></p>

            <div id="assessment_list_for_cons_report_block">
                 <p><strong><?php _e('Select Assessment:', 'tti-platform'); ?></strong> <select id="assessment_list_for_cons_report"><option></option></select></p>
                <p><strong><?php _e('Select Report Type:', 'tti-platform'); ?></strong> <select id="assessment_list_for_cons_report_type">
                    <option value="0"></option><option value="type_one">Type One</option><option value="quick_strength">Quick Strength</option><option value="quick_screen">Quick Screen</option></select></p>
            </div>


            
            <?php
                /**
                 * Fires after shortcode generator popup assessment consilidation report
                 * 
                 * @since   1.2
                 */
                do_action('ttisi_platform_shortcode_generator_assessment_pdf_consilidation_report_after');
            ?>
        </div>

           <?php
                /**
                 * Fires after shortcode generator popup assessment text graphic block 
                 * 
                 * @since   1.2
                 */
                do_action('ttisi_platform_shortcode_generator_assessment_list_action');
            ?>
        
    </div>
</div>
<?php
    wp_footer();
?>
</body>