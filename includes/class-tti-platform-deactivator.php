<?php

/**
 * Class to handle plugin deactivation functionality.
 *
 * @link 	http://presstigers.com
 * @since 	1.0.0
 *
 * @package 	TTI_Platform
 * @subpackage 	TTI_Platform/includes
 */

class TTI_Platform_Deactivator_Class {

	/**
	 * Function to delete unwanted options on deactivation.
	 *
	 * @since 		1.0.0
	 */
	public static function deactivate() {
            $getListener_ID = get_option('listener_page_id');
            wp_delete_post( $getListener_ID, true );
            delete_option( 'ttisi_check_locked_index_status' );
	}

	/**
	 * Function to stop the assessment checker custom CRON job.
	 *
	 * @since 		1.0.0
	 */
	public static function stop_cron_assessment_status_hecker() {
		wp_clear_scheduled_hook( 'assessments_status_checker' );
		wp_clear_scheduled_hook( 'assessments_pdf_files_checker' );
	}

}
