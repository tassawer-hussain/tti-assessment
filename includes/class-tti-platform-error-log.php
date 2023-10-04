<?php
/**
 * Class to handle error log.
 *
 * @link 	http://presstigers.com
 * @since 	1.4.6
 *
 * @package 	TTI_Platform
 * @subpackage 	TTI_Platform/includes
 */

class TTI_Platform_Deactivator_Error_Log {

	/**
	* Error log path
    * @var object
	*/
	public $error_log_path;

	/**
     * Initialize variables.
     *
     * @since 		1.0.0
     */
    public function __construct() {
        
    }

    /**
     * Function to update error log.
     *
     * @since 		1.0.0
     * @param array $data contains string data for error log
     * @param string $type contain data type 
     */
    public function put_error_log($data, $type = 'string') {
    	if($type == 'array') {
    		$data = json_encode($data);
    	} 
    	$date = date('m-d-Y', time());
    	$error_log_spath = plugin_dir_path(__FILE__).'error-log/debug_'.$date.'.log';
		error_log(date("g:i a").':    '. $data.PHP_EOL, 3, $error_log_spath);
    }

}
