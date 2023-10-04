<?php
/**
 * Class handles the mapping page functionality
 *
 * This class is used to define main AJAX based functionality in WordPress admin user's profile.
 *
 * @since   1.7.0
 * @package     TTI_Platform
 * @subpackage  TTI_Platform/includes
 * @author  Presstigers
 */

class TTI_Platform_Mapping_Handler
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
    public function tti_update_mapping_data()
    {
        $params = array();
        
        if(isset($_POST['data'])) {
            parse_str($_POST['data'], $params);
            /* sanitize array */

            $params = $this->sanitize_array($params);
            $result = update_option('tti_platform_mapping_data' , $params);
        }
        
        $response['status'] = 'success';
        $response = json_encode($response);
        
        echo $response;
        exit;
    }

    /**
     * Return the mapping saved data
     * 
     * @since  1.7.0
     * @return array
     */
    function return_mapping_data ( ) {
        $result = get_option('tti_platform_mapping_data');
        return $result;
    }

   
    /**
     * Recursive sanitation for text or array
     * 
     * @param $array_or_string (array|string)
     * @since  1.7.0
     * @return mixed
     */
    function sanitize_array( &$array ) {
        foreach ($array as &$value) {   
            if( !is_array($value) ) 
                // sanitize if value is not an array
                $value = sanitize_text_field( $value );
            else
                // go inside this function again
                $this->sanitize_array($value);
        }
        return $array;
    }

}

