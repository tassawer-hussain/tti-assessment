<?php

/**
 * Class to handle plugin uninstalled functionality.
 *
 *
 * @link 	http://presstigers.com
 * @since 	1.0.0
 * @package TTI_Platform
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/* delete the options on deletion of plugins */
delete_option('ttisi_check_locked_index_status');