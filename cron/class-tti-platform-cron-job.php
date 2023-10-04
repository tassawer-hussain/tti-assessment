<?php
/**
 * Class to handle CRON JOB related functionality.
 *
 * @since 	1.2.0
 * @package 	TTI_Platform
 * @author 	Presstigers
 */
class TTI_PLATFORM_CRON_JOB_TASK {
    /**
     * Define the core functionality of the plugin.
     *
     * @since  1.0.0
     */
    public function __construct() {
      add_action('assessments_status_checker', array($this, 'assessments_status_checker_function'));
      add_action('assessments_pdf_files_checker', array($this, 'assessments_pdf_files_checker_function'));
    }

     /**
     * Function to schedule the CRON job.
     *
     * @since  1.0.0
     */
    function assessments_status_checker_function() { 
        // Check the links status from API
        $this->update_disabled_assessment_link();
    }
    
     /**
     * Function to schedule the CRON job.
     *
     * @since  1.0.0
     */
    function assessments_pdf_files_checker_function() { 
      $log_directory = WP_CONTENT_DIR . '/uploads/tti_assessments/*';
  		$files = array_filter(glob($log_directory), 'is_dir');

  		foreach ($files as $file) {
  			$original_dir = $file;
  		 	$link_array = explode('/',$file);
  		    $folder_name_date = end($link_array);
  		    $this->check_three_days_old($folder_name_date, $original_dir);
  		}
    }

    /**
    * Function to check 3 days recent files.
    *
    * @since  1.0.0
    * @param string $last_log contains log content
    * @param string $original_dir contains direcctory where log file exists
    */
    function check_three_days_old($last_log, $original_dir) {
		if(strtotime($last_log) < strtotime('-3 day')) {
	    	/* Delete that folder */
	    	$dirname = $original_dir;
			$this->deleteDirectory($dirname);
		} else {
			/* Don't delete that folder */
			
		}
	}

	/**
	* Function to delete directory function.
  *
  * @since  1.0.0
  * @param string $dirPath contains directory path
	*/
	function deleteDirectory($dirPath) {
	    if (is_dir($dirPath)) {
	        $objects = scandir($dirPath);
	        foreach ($objects as $object) {
	            if ($object != "." && $object !="..") {
	                if (filetype($dirPath . DIRECTORY_SEPARATOR . $object) == "dir") {
	                    deleteDirectory($dirPath . DIRECTORY_SEPARATOR . $object);
	                } else {
	                    unlink($dirPath . DIRECTORY_SEPARATOR . $object);
	                }
	            }
	        }
	    reset($objects);
	    rmdir($dirPath);
	    }
	}


    /**
    * Function to check which assessment links are disabled.
    *
    * @since  1.2
    */
    function update_disabled_assessment_link() {
     
    	/* Get all assessment post types */
    	$disabled_links = array();
     	$args = array( 'post_type' => 'tti_assessments', 'posts_per_page' => -1 );
            $loop = new WP_Query( $args );
            $assessmenrArr = array();
            while ( $loop->have_posts() ) : $loop->the_post();
              $assesment_id = get_the_ID();
              $status_assessment = get_post_meta($assesment_id, 'status_assessment', true);
              $status_locked = get_post_meta($assesment_id, 'status_locked', true);
              $api_service_location = get_post_meta($assesment_id, 'api_service_location', true);
              $api_key = get_post_meta($assesment_id, 'api_key', true);
              $assessment_link = get_post_meta($assesment_id, 'link_id', true);
              $api_link_status = $this->get_status_assessment_link($api_service_location, $assessment_link, $api_key);
              $ass_lock_status = $this->get_status_assessment_locked($api_service_location, $assessment_link, $api_key);

              /* check locked status */
              if($status_locked != $ass_lock_status) {
                update_post_meta( $assesment_id, 'status_locked', $ass_lock_status );
              }
             
              /* check suspend status */
              if($status_assessment != $api_link_status) {
              	update_post_meta( $assesment_id, 'status_assessment', $api_link_status );
              }
            endwhile;
    }

    /**
    * Function to update the assessment locked status.
    *
    * @since  1.4.1
    *
    * @param string $api_service_location contains api service location link
    * @param string $link_id contains assessment link id
    * @param string  $access_token contains access token
    * @return string true or false
    */
   function get_status_assessment_locked($api_service_location, $link_id, $access_token) {
        /* API v 3.0 url */  
        $newUrl =  $api_service_location . '/api/v3/links/'.$link_id ;
              
        $headers = array(
          'Authorization' => $access_token,
          'Accept' => 'application/json',
          'Content-Type' => 'application/json'
        );
        
        $args = array(
          'method' => 'GET',
          'headers' => $headers,
        );

        $data = wp_remote_request($newUrl, $args);
        $getStatus = json_decode(wp_remote_retrieve_body($data));

        if(isset($getStatus->locked) && $getStatus->locked == 1) {
          return 'true';
        } elseif(isset($getStatus->locked) && $getStatus->locked == 0) {
          return 'false';
        } else {
          return 'true';
        }
    }


    
    /**
    *  Function to update the disabled assessment links
    *
    * @since  1.2
    *
    * @param string $api_service_location contains api service location link
    * @param string $link_id contains link id
    * @param string $access_token contains access token
    * @return string contains status
    */
    function get_status_assessment_link($api_service_location, $link_id, $access_token) {
    	  /* API v3.0 url */  
        $url = $api_service_location . '/api/v3/links/'.$link_id.'/status';
        
        $headers = array(
            'Authorization' => $access_token,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        );
        $args = array(
            'method' => 'GET',
            'headers' => $headers,
        );

        $data = wp_remote_request($url, $args);
        $getStatus = json_decode(wp_remote_retrieve_body($data));
        if(isset($getStatus->disabled) && $getStatus->disabled == 1) {
        	return 'Suspended';
        } else {
        	return 'Active';
        }
    }

}

