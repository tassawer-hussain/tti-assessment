<?php

/**
 * The plugin bootstrap file
 *
 * @author 			Presstigers
 * @link 			http://presstigers.com
 * @since 			1.0.0
 * @package 		TTI_Platform
 *
 * @wordpress-plugin
 * Plugin Name: 		TTI Assessment
 * Plugin URI: 			https://www.ttisi.com/
 * Description: 		Eliminate your people problems with our assessment tools, management techniques, and global network of experts.
 * Version: 			1.7.3
 * Author: 			 	Presstigers
 * Author URI: 			http://presstigers.com/
 * License: 			GPL-2.0+
 * License URI: 		http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: 		tti-platform
 * Domain Path: 		/languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}
// Used for referring to the plugin file or basename
if ( ! defined( 'TTI_PLATFORM_FILE' ) ) {
    define( 'TTI_PLATFORM_FILE', plugin_basename( __FILE__ ) );
}

/* CRON job file */
require plugin_dir_path( __FILE__ ) . 'cron/class-tti-platform-cron-job.php';

define('WP_HTTP_BLOCK_EXTERNAL', false); 
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-now-hiring-activator.php
 */
function tti_platform_activate_TTI_Platform() {
    //ob_start();
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-tti-platform-activator.php';
    $TTI_Platform_Activator = new TTI_Platform_Activator_Class();
    $TTI_Platform_Activator->createListenerPage();
    $TTI_Platform_Activator->createTableForAssessment();
    $TTI_Platform_Activator->createTableForUsersLimit();
    $TTI_Platform_Activator->schdule_cron_job();
    //trigger_error(ob_get_contents(),E_USER_ERROR);
}

/*************************************/

/************ Check lock status  ***********/
add_action('init', 'tti_platform_check_lock_status');
/**
* Check the lock status
*/
function tti_platform_check_lock_status() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-tti-platform-activator.php';
	$check_locked_index_status = get_option('ttisi_check_locked_index_status');
    if(!$check_locked_index_status || $check_locked_index_status < 2) {
    	$TTI_Platform_Activator = new TTI_Platform_Activator_Class();
    	$TTI_Platform_Activator->check_locked_status();
    	if(!$check_locked_index_status) {
    		$check_locked_index_status = 0;
    	}
    	$check_locked_index_status++;
    	update_option('ttisi_check_locked_index_status', $check_locked_index_status);
    }
}
/**************************************/


/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-now-hiring-deactivator.php
 */
function tti_platform_deactivate_TTI_Platform() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-tti-platform-deactivator.php';
    TTI_Platform_Deactivator_Class::deactivate();
    TTI_Platform_Deactivator_Class::stop_cron_assessment_status_hecker();
}

/* Activation Hook */
register_activation_hook( __FILE__, 'tti_platform_activate_TTI_Platform' );
/* De-activation Hook */
register_deactivation_hook( __FILE__, 'tti_platform_deactivate_TTI_Platform' );

/* Error Log code */
require plugin_dir_path( __FILE__ ) . 'includes/class-tti-platform-error-log.php';

/**
 * The core plugin class that is used to define internationalization,
 * dashboard-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-tti-platform.php';

/* Load public files only in public */
require plugin_dir_path( __FILE__ ) . 'public/class-tti-platform-public.php';

/* Load admin files only in admin */
$actual_link = esc_url_raw("$_SERVER[REQUEST_URI]");
$arr = explode('/', $actual_link);
if(
	is_admin() || 
	isset($arr[count($arr)-1]) && $arr[count($arr)-1] == 'shortcode-popup.php'
) {
	require plugin_dir_path( __FILE__ ) . 'admin/class-tti-platform-admin.php';
}


/* Load dependencies */
$init_tti = new TTI_Platform_Main_Class();
/********************/

/**
 * Create 15 minute cron job
 *
 * @param array $schedules
 * @return array
 */
function tti_platform_add_every_fifteen_minutes( $schedules ) {
    $schedules['fifteen_minutes_ttsi_cron'] = array(
            'interval' => 15 * 60,
            'display'  => esc_html__( 'Every Fifteen Minutes' ),
        );
    return $schedules;
}

add_filter( 'cron_schedules', 'tti_platform_add_every_fifteen_minutes' );

/**
 * Create 3 days cron job
 *
 * @param array $schedules
 * @return array
 */
function tti_platform_add_every_three_days( $schedules ) {
    $schedules['three_days_ttsi_cron'] = array(
            'interval' => 4320 * 60,
            'display'  => esc_html__( 'Every 3 Days' ),
        );
    return $schedules;
}

add_filter( 'cron_schedules', 'tti_platform_add_every_three_days' );
/********************/

/* CRON job function */
$tti_cron_job = new TTI_PLATFORM_CRON_JOB_TASK();
/********************/

$api_url = 'https://ttiplatform.com/tti_plugin/automatic-theme-plugin-update-master/api';
$plugin_slug = basename(dirname(__FILE__));
//add_filter('pre_set_site_transient_update_plugins', 'tti_platform_check_for_plugin_update');
/**
 * Check for the plugin update
 *
 * @param $checked_data array 
 * @return array
 */
function tti_platform_check_for_plugin_update($checked_data) {
	global $api_url, $plugin_slug;
	$args = array(
		'slug' => $plugin_slug,
		'version' => $checked_data->checked[$plugin_slug .'/'. $plugin_slug .'.php'],
	);
	$request_string = array(
		'body' => array(
			'action' => 'basic_check', 
			'request' => serialize($args),
			'api-key' => md5(get_bloginfo('url'))
		),
		'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url')
	);
	$raw_response = wp_remote_post($api_url, $request_string);	
	if (!is_wp_error($raw_response) && ($raw_response['response']['code'] == 200))
		$response = unserialize($raw_response['body']);
	if (is_object($response) && !empty($response)) // Feed the update data into WP updater
		$checked_data->response[$plugin_slug .'/'. $plugin_slug .'.php'] = $response;
	return $checked_data;
}
//add_filter('plugins_api', 'tti_platform_plugin_api_call', 10, 3);
/**
 * Plugin API call
 *
 * @since 1.0.0
 * @param $def string 
 * @param $action string 
 * @param $args array 
 * @return array/obj
 */ 
function tti_platform_plugin_api_call($def, $action, $args) {
	global $plugin_slug, $api_url;
	if ($args->slug != $plugin_slug)
		return false;
	// Get the current version
	$plugin_info = get_site_transient('update_plugins');
	$current_version = $plugin_info->checked[$plugin_slug .'/'. $plugin_slug .'.php'];
	$args->version = $current_version;
	$request_string = array(
		'body' => array(
			'action' => $action, 
			'request' => serialize($args),
			'api-key' => md5(get_bloginfo('url'))
		),
		'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url')
	);	
	$request = wp_remote_post($api_url, $request_string);
	if (is_wp_error($request)) {
		$res = new WP_Error('plugins_api_failed', __('An Unexpected HTTP Error occurred during the API request.</p> <p><a href="?" onclick="document.location.reload(); return false;">Try again</a>'), $request->get_error_message());
	} else {
		$res = unserialize($request['body']);	
		if ($res === false)
			$res = new WP_Error('plugins_api_failed', __('An unknown error occurred'), $request['body']);
	}
	return $res;
}
add_filter('plugin_action_links', 'ttisi_platform_add_action_plugin', 10, 5);

/**
 * Plugin page settings link
 *
 * @since 1.0.0
 * @param $actions array 
 * @param $plugin_file string 
 * @return array
 */
function ttisi_platform_add_action_plugin($actions, $plugin_file) 
{
	static $plugin;
	if (!isset($plugin))
	$plugin = plugin_basename(__FILE__);
	if ($plugin == $plugin_file) {
	$settings 	= array('settings' 	=> '<a href="admin.php?page=ttiplatform_settings">' . __('Settings', 'General') . '</a>');
	$actions = array_merge($settings, $actions);
	}
	return $actions;
}   

/* Woocommerce add to cart option */
add_action( 'woocommerce_after_shop_loop_item', 'tti_platform_shop_view_product_button', 10);

/**
 * Show product view button below product thumbnail
 *
 * @since 1.4
 */
function tti_platform_shop_view_product_button() {
global $product;
$link = $product->get_permalink();
echo '<a href="' . $link . '" class="button addtocartbutton">View Product</a>';
}

 add_action( 'woocommerce_after_shop_loop_item', 'tti_platform_remove_add_to_cart_buttons', 1 );

    function tti_platform_remove_add_to_cart_buttons() {
      if( is_product_category() || is_shop()) { 
        remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart' );
      }
    }

add_action( 'woocommerce_before_add_to_cart_button', 'tti_platform_before_add_to_cart_btn',100 );
/**
 * Show enroll me notification before add to cart button single product page
 *
 * @since 1.4
 */
function tti_platform_before_add_to_cart_btn(){
    global $product;
    
    $product_id = $product->get_id();
    $is_show_msg = false;

    $wdm_ldgr_paid_course = get_post_meta($product_id, '_is_ldgr_paid_course', true);
    $is_group_purchase_active = get_post_meta($product_id, '_is_group_purchase_active', true);

    if(function_exists('ldgr_is_user_in_group')) {
        if ( ! ldgr_is_user_in_group( $product_id ) ) {
            $is_show_msg = true;
        }
    }

    if($wdm_ldgr_paid_course == 'on' && $is_group_purchase_active == 'on' && $is_show_msg) {
        echo '<div class="ttisi-front-noti" style="display:none;"><p style="margin:0px;">IMPORTANT - Check <strong>"Enroll Me"</strong> if you will be completing the assessment yourself. Selecting this option will take one purchased usage and the remaining usages (if purchasing more than one) will be banked so you can assign them to respondents as needed.</p></div>';
        ?>
        <script type="text/javascript">
            jQuery( document ).ready(function() {
                if ( jQuery( ".wdm-enroll-me-div" ).length ) {
                    jQuery( ".ttisi-front-noti" ).show();
                } else {
                    jQuery( ".ttisi-front-noti" ).hide();
                }
            });
        </script>
        <?php
    }
}

/* Woocommerce add to cart option ends */
add_action( 'admin_menu', 'tti_hide_menu_add_new');
function tti_hide_menu_add_new () {
    wp_enqueue_style('tti_platform_admin_menu_style', plugin_dir_url(__FILE__) . 'admin/css/tti-platform-admin-menu.css', array(), '11', 'all');

}



add_action( 'admin_enqueue_scripts', 'tti_platform_admin_script_group_registration' );

function tti_platform_admin_script_group_registration() {
    wp_enqueue_script(
        'tti_platform_admin_script_group_registration', 
        plugin_dir_url(__FILE__) . 'admin/js/tti-platform-group-edit.js', 
        array('jquery'), 
        generateRandomStringMainFile(), 
        true
    );
}


 function generateRandomStringMainFile($length = 20) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
}

/**** Group Registration Hooks ****/

add_filter('ldgr_filter_group_registration_tab_headers', 'retake_assessment_group_leader', 99, 2);
add_filter('ldgr_filter_group_registration_tab_contents', 'retake_assessment_group_leader_content', 99, 2);

// update the email sending settings to true.
add_filter( 'ldgr_filter_sub_group_update_result', 'set_sub_group_leader_email_settings', 99, 2 );

/**
* Retake assessment tab button.
*/ 
function retake_assessment_group_leader( $tabs, $group_id )
{   
    /* Initialize main group leader class */
    require_once plugin_dir_path( __FILE__ ) . 'public/partials/group-leader-extensions/class-tti-platform-group-leader.php';

    $is_assessment_group = is_group_has_assessment_shortcode( $group_id );
    foreach( $tabs as $key => $value ) {

        if ( 'Enrolled Users' === $value['title'] ) {
            $tabs[$key]['title'] = 'People Enrolled';
            
            // if assessment related group
            if ( $is_assessment_group ) {
                $tabs[$key]['slug'] = 'tti_platform_retake_assessment';
            }
        }

        if ( 'Report' === $value['title'] ) {
            $tabs[$key]['title'] = 'Progress';
        }
    }

    // Settings Tab.
    $tabs[] = array (
        'title' => __( 'Settings', 'tti-platform' ),
        'slug'  => 'tti_platform_group_settings',
        'id'    => 4,
        'icon'  => '',
    );

    usort($tabs, function($a, $b) {
        return $a['id'] <=> $b['id'];
    });

    return $tabs;
}


/**
* Retake assessment tab content.
*/ 
function retake_assessment_group_leader_content($tabs_content, $group_id)
{
    $partials_url = plugin_dir_path(dirname( __FILE__ )).'tti-platform/public/partials/group-leader-extensions/';
    
    $is_assessment_group = is_group_has_assessment_shortcode( $group_id );
    foreach( $tabs_content as $key => $value ) {
        $tabs_content[$key]['active'] = false;

        // Enrolled User tab.
        if ( false !== strpos( $value['template'], 'enrolled-users-tab.template.php') ) {
            $tabs_content[$key]['active'] = true;
            // if assessment related group - update the template
            if ( $is_assessment_group ) {
                $tabs_content[$key]['template'] = $partials_url.'/group-dashboard/group-leader-tab.template.php';
            }
        }
    }

    // Settings Tab.
    $tabs_content[] = array (
        'id'       => 4,
        'active'   => false,
        'template' => $partials_url.'/group-settings/group-leader-tab-settings.template.php',
    );

    usort($tabs_content, function($a, $b) {
        return $a['id'] <=> $b['id'];
    });

    return $tabs_content;
}

function set_sub_group_leader_email_settings( $result, $post ) {

    // Let's make all selected leaders as group leaders.
    if ( ! empty( $post['groupLeaders'] ) ) {
        foreach ( $post['groupLeaders'] as $sub_group_leader_id ) {
            update_user_meta( $sub_group_leader_id, 'group_user_'.$sub_group_leader_id.'_settings', 'false' );
        }	
    }
    return $result;
}



/**** Group Registration Hooks Ends ****/

/**** Group Registration Hooks Email Related *****/
add_action('init', 'tti_email_init_class');
function tti_email_init_class() {
    /* Email controlling class for admin */
    require plugin_dir_path( __FILE__ ) . 'admin/class-tti-platform-emails.php';
    new TTI_Platform_Emails_Class();
}

add_action('init', 'tti_email_gl_init_class');

function tti_email_gl_init_class() {
    /* Email controlling class for admin */
    require plugin_dir_path( __FILE__ ) . 'public/partials/group-leader-extensions/includes/class-tti-platform-gl-emails.php';
    new TTI_Platform_Emails_Gl_Class();
}

add_action('wp_logout','auto_redirect_after_logout');
function auto_redirect_after_logout(){
  wp_redirect( get_home_url() );
  exit();
}


/*Initialize emails filter hooks group registration plugin */
    require_once plugin_dir_path(dirname( __FILE__ )).'tti-platform/public/partials/group-leader-extensions/group-settings/class-tti-platform-gp-settings-hooks.php';
 /* LearnDash hooks  */
require_once plugin_dir_path(dirname( __FILE__ )).'tti-platform/public/lms-hook/tti-lms-hooks-process.php';
   

/* ****************************** GITHUB UPDATES ******************************** */
  
//   require 'plugin-update-checker/plugin-update-checker.php';
//   $myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
//     'https://github.com/ttisi/tti-platform/',
//     __FILE__,
//     'tti-platform'
//   );

// if(isset($_GET['puc_check_for_updates']) && $_GET['puc_check_for_updates'] == 1) {
//   //$token = $_SESSION['access_token'];
//   $token = 'bab4813ad9619d6bec688501c9a6be4cb92c9d27';
//   // echo $token;exit;
//   //Optional: If you're using a private repository, specify the access token like this:
//   $myUpdateChecker->setAuthentication($token);
//   //Optional: Set the branch that contains the stable release.
//   $myUpdateChecker->setBranch('master');
// }


// require_once 'plugin-update-checker/plugin-update-checker.php';
// $url = 'https://api.github.com/repos/ttisi/tti-platform/contents/plugin.json?access_token=f943f85f0192bdb20d41b5dbcd0bdf092006cc80';
// $data = wp_remote_get( $url );
// $request = wp_remote_retrieve_body($data);
// $request = json_decode($request);


// $myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
//   $request->download_url,
//   __FILE__, //Full path to the main plugin file or functions.php.
//   'tti-platform'
// );



/*************************************************************************************************/
add_filter('ldgr_group_email_headers', 'tti_ldgr_group_email_headers_func',1);
function tti_ldgr_group_email_headers_func($headers) {
    $user_data  = wp_get_current_user();
    $headers[0]  = 'Reply-To: '.$user_data->data->display_name.' <'.$user_data->data->user_email.'>';
    return $headers;
}

/** Tassawer Implemented to findout the Current Group course content has assessment shortocde or not. */
/**
* Get assessment links id by course id.
*
* @param integer $c_id course id
* @since   1.7.0
*/
function is_group_has_assessment_shortcode( $group_id ) {

    $content_ids = array();

    // retrieve all courses against given group id
    $courses = th_get_current_group_courses( $group_id );

    if( count( $courses ) > 0 ) {
        foreach ($courses as $c_id) { 
            $content_ids = array_merge( $content_ids, get_contents_post_id_by_course_id( $c_id ) );
        }
        $content_ids = array_merge( $content_ids, $courses );
        
        if( count( $content_ids ) > 0) {
            $content_ids = array_unique($content_ids);
            
            foreach ($content_ids as $key => $content_id) { 
                $is_found = check_assessment_shortcode_exist_in_current_post($content_id);
                
                if($is_found) {
                    return true;
                }
            }
        }
    }

    return false;
    
}

/**
* Get current group courses.
*/
function th_get_current_group_courses( $group_id ) {
    global $wpdb;
    $course_ids = array();
    $key = 'learndash_group_enrolled_' . $group_id;
    $meta = $wpdb->get_results("SELECT * FROM `".$wpdb->postmeta."` WHERE meta_key='".$wpdb->escape($key)."'");
    

    foreach ($meta as $key => $value) {
        if(isset($value->post_id) && !empty($value->post_id)) {
            $course_ids[] = $value->post_id;
        }
    }
    return $course_ids;
}

/**
* Get assessment links id by course id.
*
* @param integer $c_id course id
*/
function get_contents_post_id_by_course_id( $c_id ) {
    global $wpdb;
    $content_ids = array();

    $key = 'ld_course_'.$c_id;

    $content_ids[] = $c_id; /* assign course id */
    
    $meta = $wpdb->get_results("SELECT * FROM `".$wpdb->postmeta."` WHERE meta_key='".$wpdb->escape($key)."'");
    foreach ($meta as $key => $value) {
        if(isset($value->post_id) && !empty($value->post_id)) {
            $content_ids[] = $value->post_id;
        }
    }
    
    $meta = $wpdb->get_results("SELECT * FROM `".$wpdb->postmeta."` WHERE meta_key='course_id' AND meta_value=".$c_id);
    foreach ($meta as $key => $value) {
        if(isset($value->post_id) && !empty($value->post_id)) {
            $content_ids[] = $value->post_id;
        }
    }

    return $content_ids;
}

/**
 * Get post content.
 */
function check_assessment_shortcode_exist_in_current_post( $content_id ) {
    $content_post = get_post( $content_id );

    // var_dump($content_post);
    if( isset( $content_post->post_content ) ) {
        $content = $content_post->post_content;
        $content = wpautop( $content );
        $searc_string  = '[take_assessment assess_id=';

        if ( strpos( $content, $searc_string ) !== false ) {
            return true;
        }

    }
    return false;
}

/**
 * Override the Template which is sued to display the list of Groups on Enrolled User page.
 */
add_filter( 'ldgr_filter_template_path', 'ttisi_override_the_group_list_template', 99, 2 );
function ttisi_override_the_group_list_template( $template_path, $args ) {

    if ( FALSE !== strpos( $template_path, 'ldgr-group-users-select-wrapper.template.php' ) ) {
        $template_path = plugin_dir_path(dirname( __FILE__ )).'tti-platform/public/partials/group-leader-extensions/group-dashboard/ldgr-group-users-select-wrapper.template.php';
    }

    if ( FALSE !== strpos( $template_path, 'ldgr-group-users-tabs.template.php' ) ) {
        $template_path = plugin_dir_path(dirname( __FILE__ )).'tti-platform/public/partials/group-leader-extensions/group-dashboard/ldgr-group-users-tabs.template.php';
    }
    
    return $template_path;
}

