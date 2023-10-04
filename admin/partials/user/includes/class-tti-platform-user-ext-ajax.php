<?php
/**
 * Class contains AJAX based functions to extend the user functionality.
 *
 * This class is used to define main AJAX based functionality in WordPress admin user's profile.
 *
 * @since   1.7.0
 * @package     TTI_Platform
 * @subpackage  TTI_Platform/includes
 * @author  Presstigers
 */

class TTI_Platform_Admin_User_Ext_Ajax_Class
{

    /**
     * String contains user id
     * @var string
     */
    public $user_id;

    /**
     * Constructor function to class initialize properties and hooks.
     *
     * @since       1.7.0
     */
    public function __construct()
    {

    }

    /**
     * Function to validate given user assessment
     *
     * @since   1.7.0
     */
    public function tti_validate_user_assessment()
    {
        $response = array();

        /* start the validation processs */
        $this->process_ass_validation();

        $response = array (
            'success' => '1',
            'message' => 'contains success message'
        );

        echo json_encode($response);
        exit;
    }

    /**
     * Function to save user assessment settings
     *
     * @since   1.7.0
     */
    public function tti_save_user_assessment_sett()
    {
        $response = array();

        $tti_user_id = sanitize_text_field($_POST['tti_user_id']);
        $settings_data['user_capa'] = sanitize_text_field($_POST['user_capa']);

       /* Update user metadata */
        $result = update_user_meta( $tti_user_id, 'user_assessment_settings', serialize($settings_data));

        $response = array (
            'status' => 'success',
            'message' => 'Successfully Save Assessment Settings'
        );

        echo json_encode($response);
        exit;
    }

    /**
     * Function process assessment validation
     *
     * @since    1.7.0
     */
    public function process_ass_validation()
    {
        $queryParams = '';

        $access_token = sanitize_text_field($_POST['api_key_user']);

        if (isset($_POST['account_login_user']))
        {
            $account_login = sanitize_text_field($_POST['account_login_user']);
        }

        if (isset($_POST['tti_link_id_user']))
        {
            $tti_link_id = sanitize_text_field($_POST['tti_link_id_user']);
        }

        if (!empty($account_login))
        {
            $queryParams = 'account_login=' . $account_login_user . '&page=1';
        }

        /* Validate the assessment by link id */
        $this->tti_validate_assessment_by_link( $queryParams, $access_token, $account_login, $tti_link_id );

        exit;
    }

    /**
     * Function to validate the assessment by link ID
     *
     * @since   1.7.0
     * @param array $queryParams contains parametets for query
     * @param string $access_token contains access token for api
     * @param string $account_login contains username
     * @param string $tti_link_id contain link id of assessment
     */
    public function tti_validate_assessment_by_link( $queryParams, $access_token, $account_login, $tti_link_id )
    {   
        $can_print_report = 'false';

        /* API v 3.0 url */
        $newUrl = esc_url_raw($_POST['api_service_location_user']) . '/api/v3/links/' . $tti_link_id;

        $headers = array(
            'Authorization' => $access_token,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        );
        $args = array(
            'method' => 'GET',
            'headers' => $headers,
        );
        
        $response = wp_remote_request($newUrl, $args);
        
        if (is_wp_error($response))
        {
            $error_message = $response->get_error_message();
            $api_response['status'] = 'error';
            echo $error_message;
        }
        else
        {
            $api_response_pt = json_decode(wp_remote_retrieve_body($response));
            
            if (isset($api_response_pt->status) && $api_response_pt->status == 500)
            {
                $api_response['message'] = 'No response from the API. 500 internel server error thrown.';
                $api_response['status'] = 'error';
                $api_response = json_encode($api_response);
            }
            else
            {
                /* Check for error */
                if (isset($api_response_pt->status) && $api_response_pt->status == 'error')
                {
                    $can_print_report = 'false';
                    $api_response['print_status'] = $can_print_report;
                    $api_response['status'] = 'error';
                    /* error message */
                    if (isset($api_response_pt->message) && $api_response_pt->message != '')
                    {
                        $api_response['message'] = 'This Link Login not found and cannot be added. Please provide a valid details.';
                    }
                    else
                    {
                        $api_response['message'] = 'This Link Login not found and cannot be added. Please provide a valid details.';
                    }
                    $api_response = json_encode($api_response);
                }
                else
                {
                    /* No error */
                    if (isset($api_response_pt->email_to) && $api_response_pt->email_to == true)
                    {
                        $can_print_report = 'true';
                    }
                    /* Assessment status */
                    if (isset($api_response_pt->disabled) && $api_response_pt->disabled == 0)
                    {
                        $api_response['assessment_status_hidden'] = 'true';
                    }
                    else
                    {
                        $api_response['assessment_status_hidden'] = 'false';
                        $api_response['message'] = 'This Link Login is disabled and cannot be added. Please provide a valid Link Login.';
                    }
                    /* Assessment name */
                    if (isset($api_response_pt->name) && $api_response_pt->name != '')
                    {
                        $api_response['assessment_name_hidden'] = $api_response_pt->name;
                    }
                    else
                    {
                        $api_response['assessment_name_hidden'] = 'Assessment';
                    }

                    /* Assessment locked status */
                    if (isset($api_response_pt->locked) && $api_response_pt->locked == true)
                    {
                        $api_response['assessment_locked_status'] = 'true';
                    }
                    else
                    {
                        $api_response['assessment_locked_status'] = 'false';
                    }
                    $api_response['print_status'] = $can_print_report;
                    $api_response['status'] = 'success';
                    $api_response = json_encode($api_response);
                }
            }
            
            echo $api_response;
        }



    }


    /**
     * Function insert user assessment
     *
     * @since    1.7.0
     */
    public function tti_insert_user_assessments()
    {
        $res = $this->insert_user_assessment();
        if($res) {
            $api_response['status'] = 'success';
        } else {
            $api_response['status'] = 'fail';
        }
        echo json_encode($api_response);;
        exit;
    }

    /**
     * Function update user assessment
     *
     * @since    1.7.0
     */
    public function insert_user_assessment()
    {

        $user_id                     =  sanitize_text_field($_POST['tti_user_id']);
        $name                        =  sanitize_text_field($_POST['name']);
        $link_id                     =  sanitize_text_field($_POST['link_id']);
        $status_assessment           =  sanitize_text_field($_POST['status_assessment']);
        $organization_hidden         =  sanitize_text_field($_POST['organization_hidden']);
        $print_report                =  sanitize_text_field($_POST['print_report']);
        $send_rep_group_lead         =  sanitize_text_field($_POST['send_rep_group_lead']);
        $api_key_hidden              =  sanitize_text_field($_POST['api_key_hidden']);
        $account_login_hidden        =  sanitize_text_field($_POST['account_login_hidden']);
        $api_service_location_hidden =  sanitize_text_field($_POST['api_service_location_hidden']);
        $survay_location_hidden      =  sanitize_text_field($_POST['survay_location_hidden']);
        $status_locked               =  sanitize_text_field($_POST['status_locked']);
        $report_api_check            =  sanitize_text_field($_POST['report_api_check']);  

        $user_ass = array (
            'title' => $organization_hidden, 
            'account_login' => $account_login_hidden, 
            'api_key' => $api_key_hidden,
            'api_service_location' => $api_service_location_hidden,
            'survey_location'   => $survay_location_hidden,
            'link_id' => $link_id,
            'status_assessment' => $status_assessment,
            'organization_hidden' => $organization_hidden,
            'print_report' => $print_report,
            'send_rep_group_lead' => $send_rep_group_lead,
            'status_locked' => $status_locked,
            'report_api_check' => $report_api_check,
            'name' => $name
        );

        

        /* saving report metadata script */
        $newUrl = esc_url_raw($api_service_location_hidden) . '/api/v3/links/' . $link_id;

        $headers = array(
            'Authorization' => $api_key_hidden,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        );
        $args = array(
            'method' => 'GET',
            'headers' => $headers,
        );
        
        $data = wp_remote_request($newUrl, $args);
        $response = json_decode(wp_remote_retrieve_body($data));

        /* can print report script */
        $can_print_report = 'false';
        $can_group_leader_mail = 'false';

        if(isset($response->email_to) && $response->email_to == true) {
            $can_print_report = 'true';
            $can_group_leader_mail = 'true';
        }
        $user_ass['can_print_assessment'] = $can_print_report;
        
        if($can_print_report == 'true') {
            $user_ass['print_report'] = $print_report;
        } else {
            $user_ass['print_report'] = '';
        }

        /* Update the Group Leader Mail function */
        if($can_group_leader_mail == 'true') {
            $user_ass['send_rep_group_lead'] = $send_rep_group_lead;
        } else {
            $user_ass['send_rep_group_lead'] = '';
        }

        /* Api report */
        if(strtolower($report_api_check) == 'yes' || strtolower($report_api_check) == 'no') {
            $user_ass['report_api_check'] = $report_api_check;
        } else {
            $user_ass['report_api_check'] = '';
        }
        $report_view_id = 0;
        /* can print report script ends */
        foreach ($response->reportviews as $key => $value) { 
            $report_view_id = $value->id;
            $report_instrument_details = $value->assessment; // link id / assessment instrument details
            $user_ass['report_view_id'] = $report_view_id;
            $user_ass['report_instrument_details'] = $report_instrument_details;
            $report_data[$report_view_id] = $this->get_report_metadata($report_view_id, $api_service_location_hidden, $api_key_hidden);
        }

        update_user_meta($user_id, 'report_metadata_'.$link_id, serialize($report_data));
        /* saving report metadata script ends */

        /* Update user metadata */
        $lists = get_user_meta( $user_id, 'user_assessment_data', true);
        $lists = unserialize($lists);

        if( !empty($lists) && is_array($lists)) { 
           $lists[$link_id] = $user_ass;
        } else {
            $lists = array();
            $lists[$link_id] = $user_ass;
        }

        $result = update_user_meta( $user_id, 'user_assessment_data', serialize($lists));

        return true;
    }


    /**
    * Function to get all report metadata.
    *
    * @since    1.7.0
    * @param array $Function to report_view_id contains report view id
    * @param string $api_service_location contains service location link 
    * @param string $api_key contains api key
    * @return array contains api response
    */
    public function get_report_metadata($report_view_id, $api_service_location, $api_key) {
        /* API v 3.0 url */  
        $url =   esc_url_raw($api_service_location) . '/api/v3/reportviews/'. $report_view_id;
        
        $api_response = $this->send_api_request($url, $api_key);
        return $api_response;
    }

    /**
    * Function to send API request.
    *
    * @since    1.7.0
    * @param $input string contains url for CURL request
    * @param $input string contains API key
    * @return array contains response from API 
    */
    public function send_api_request($url, $api_key) {

        $headers = array (
            'Authorization' => $api_key,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        );
        $args = array (
            'method' => 'GET',
            'headers' => $headers,
        );

        $response = wp_remote_request($url, $args);

        if ( is_wp_error( $response ) ) {
            $error_message = $response->get_error_message();
            return $error_message;
        } else {
            $api_response = json_decode(wp_remote_retrieve_body($response));
            return $api_response;
        }
    }


     /**
     * Function process assessment validation
     *
     * @since    1.7.0
     */
    public function tti_update_user_assessment()
    {
        $new_assess = array();

        $link_id = sanitize_text_field($_POST['tti_link_id_user']);
        $user_id = sanitize_text_field($_POST['tti_user_id']);

        $lists = get_user_meta( $user_id, 'user_assessment_data', true);
        $lists = unserialize($lists);


        $new_assess['title'] = sanitize_text_field($_POST['title']);
        $new_assess['api_key'] = sanitize_text_field($_POST['api_key_user']);
        $new_assess['account_login'] = sanitize_text_field($_POST['account_login_user']);
        $new_assess['api_service_location'] = sanitize_text_field($_POST['api_service_location_user']);
        $new_assess['survey_location'] = sanitize_text_field($_POST['survey_location']);
        $new_assess['link_id'] = sanitize_text_field($_POST['tti_link_id_user']);
        $new_assess['status_assessment'] = sanitize_text_field($_POST['status_assessment']);
        $new_assess['organization_hidden'] = sanitize_text_field($_POST['organization_hidden']);
        $new_assess['print_report'] = sanitize_text_field($_POST['print_report']);
        $new_assess['send_rep_group_lead'] = sanitize_text_field($_POST['send_rep_group_lead']);
        $new_assess['status_locked'] = sanitize_text_field($_POST['status_locked']);
        $new_assess['name'] = sanitize_text_field($_POST['name']);
        $new_assess['report_view_id'] = sanitize_text_field($_POST['report_view_id']);
        $new_assess['report_api_check'] = sanitize_text_field($_POST['report_api_check']);
        $new_assess['report_instrument_details'] = $lists[$link_id]['report_instrument_details'];

        
        if( !empty($lists) && is_array($lists)) { 
           $lists[$link_id] = $new_assess;
        } else {
            $lists = array();
            $lists[$link_id] = $new_assess;
        }

        
        update_user_meta( $user_id, 'user_assessment_data', serialize($lists));

        $api_response['status'] = 'success';
        echo json_encode($api_response);

        exit;
    }

}

