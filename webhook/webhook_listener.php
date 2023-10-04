<?php

header("Status: 200");

/**
 * Webhook file called after user completed the assessment.
 *
 *
 * @author    presstiger <support@presstigers.com>
 */
require_once "../../../../wp-load.php";
require_once "../public/class-tti-platform-public.php";

class TTI_Platform_Webhook_Handler
{

    /**
     * Contains user id
     * @var integer
     */
    private $user_id;

    /**
     * Contains assessment table name
     * @var string
     */
    private $assessment_table_name;

    /**
     * Contains error log file path
     * @var string
     */
    private $error_log_path;

    /**
     * Contains payload data
     * @var array
     */
    private $user_data;

    /**
     * Contains error log class object
     * @var object
     */
    private $error_log;

    /**
     * Contains public class object
     * @var object
     */
    private $tti_public;

    /**
     * Contains assessment id of current link
     * @var integer
     */
    private $assessment_id;

    /**
     * Contains link id
     * @var string
     */
    private $link_id;

    /**
     * Contains link password
     * @var string
     */
    private $password;

    /**
     * Define the constructor for webhook class
     *
     * @since       1.7.2
     */
    public function __construct()
    {
        /* wait 5 seconds so incase if listener shortcode running it should update DB */
        sleep(4); // try code after removing that
        /* ************************************************************************* */
        $this->tti_platform_define_properties();
        $this->tti_platform_check_user_status();
        $this->init_listener_functionality();

    }

    /**
     * Define the properties we need in webhook class
     *
     * @since       1.7.2
     */
    private function tti_platform_define_properties()
    {
        global $wpdb;
        $this->assessment_table_name = $wpdb->prefix . 'assessments';
        $this->tti_public            = new TTI_Platform_Public_Main_Class();
        $this->error_log             = $this->tti_public->error_log;
        $this->error_log_path        = plugin_dir_path(__FILE__) . 'error-log/debug.log';
        $this->tti_platform_define_sample_data();
        $this->link_id  = $this->user_data->respondent->link;
        $this->password = $this->user_data->respondent->password;
    }

    /**
     * Check if there is need to call webhook
     *
     * @since       1.7.2
     */
    function tti_platform_is_webhook_needed($current_user_id) {
        $webhook_need = get_transient('ttiPlatformCheckWebhookNeed' . $current_user_id);
        $this->error_log->put_error_log('ttiPlatformCheckWebhookNeed' . $current_user_id);
        $this->error_log->put_error_log('webhook needs : '.$webhook_need);
        if(isset($webhook_need) && $webhook_need == 'no') {
            $this->error_log->put_error_log(
             '*********************************** Webhook Not Needed ********************************************'
            );
            http_response_code(200);
            exit;
        }
    }

    /**
     * Define the sample user data (USE SAMPLE DATA FOR TESTING PURPOSES ONLY)
     *
     * @since       1.7.2
     */
    private function tti_platform_define_sample_data()
    {
        $this->user_data = file_get_contents('php://input'); /* FOR PRODUCTION PAYLOAD */
        $this->error_log->put_error_log('webhook_posted_json - ' . $this->user_data);
        /* BELOW HARDCODED DATA IS FOR TESTING PURPOSES ONLY */
        /* $this->user_data = '{"event":"report_created","event_version":1,"event_timestamp":"2021-06-24T04:58:18-07:00","respondent":{"id":"1379WPJ-4436RUF","first_name":"CFA1","last_name":"One","email":"awais.ttisi4321@presstigers.com","gender":"M","company":"test","position_job":"test","link":"1379WPJ","password":"4443XDH"},"report":{"code":"","name":"Leading From Your Strengths","timestamp":"2021-06-24T04:58:18-07:00"}}'; */
        /* ABOVE HARDCODED DATA IS FOR TESTING PURPOSES ONLY ENDS HERE */
        $this->user_data = json_decode($this->user_data);
    }

    /**
     * Check if user assessment already completed or user data not found
     *
     * @since       1.7.2
     */
    private function tti_platform_check_user_status()
    {
        global $wpdb;
        $this->error_log->put_error_log(
            '*********************************** Starts Webhook ********************************************'
        );
        $results = $wpdb->get_row(
            "SELECT user_id, status FROM {$this->assessment_table_name} WHERE password='{$this->password}' && status = 0"
        );

        

        if (isset($results->user_id)) {
            $this->tti_platform_is_webhook_needed($results->user_id);
            if ($results->status == 1) {
                $this->error_log->put_error_log('Assessment has already been completed');
                http_response_code(200);
                $this->error_log->put_error_log(
                    '*********************************** Ends Webhook ********************************************'
                );
                exit;
            } else {
                $this->user_id = $results->user_id;
            }
        } else {
            $this->error_log->put_error_log('Assessment has already been completed Or User ID not found');
            http_response_code(200);
            $this->error_log->put_error_log(
                '*********************************** Ends Webhook ********************************************'
            );
            exit;
        }
    }

    /**
     * Start implmenting functionality of listener loader.
     *
     * @since       1.7.2
     */
    private function init_listener_functionality()
    {
        $this->assessment_id = $this->tti_public->get_post_id_by_meta_key_and_value('link_id', $this->link_id);

        /* Check if assessment/link id is locked or opened */
        $status_locked = get_post_meta($this->assessment_id, 'status_locked', true);

        /* if assessment is opened */
        if ($status_locked == 'false') {
            $this->error_log->put_error_log('Cannot Proceed Because Assessment Type Is Open');
            http_response_code(200);
            exit;
        }

        /* Start hitting listener shortcode functionality */
        $atts             = array();
        $atts['password'] = $this->password;
        $atts['link_id']  = $this->link_id;
        $atts['user_id']  = $this->user_id;
        $atts['webhook']  = true;
        $final_response   = $this->tti_public->listener_shortcode($atts);
        /* call this error log function in end, to log all required data from properties */
        $this->error_log->put_error_log($final_response);
        http_response_code(200);
        exit;
    }

    /**
     * Error log data
     *
     * @since       1.7.2
     */
    private function tti_platform_error_log_data()
    {
        /************************************* error logging starts here *****************************************/
        //$this->error_log->put_error_log('webhook_posted_json - ' . $this->user_data);
        //$this->error_log->put_error_log(
        //  '*********************************** Ends Webhook ********************************************'
        //);
        // Its giving 0 user-id, we cannot use this method to tget user id
        //$this->error_log->put_error_log('current_user - '.$current_user->ID);
        //*************************************** error logging ends here ***************************************/
    }
}

new TTI_Platform_Webhook_Handler(); // initialize webhook handler
http_response_code(200);
exit;
