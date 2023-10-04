<?php


/**
 * User assessments list class to extend the function of WP_LIST_CLASS
 *
 * This class is used to define main user related functionality in WordPress admin user's profile.
 *
 * @since   1.0.0
 * @package     TTI_Platform
 * @subpackage  TTI_Platform/includes
 * @author  Presstigers
 */

/* add required WordPress list table class */
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class TTI_Platform_Admin_WP_Lists_Class extends WP_List_Table {

    /**
    * String contains user id
    * @var string
    */
    public $user_id;

    /**
    * Plugin text domain
    * @var string
    */
    public $plugin_text_domain;   


    /**
    * Plugin text domain
    * @var string
    */
    public $ass_data;

    /**
    * Plugin text domain
    * @var string
    */
    public $data; 

    /**
     * Constructor function to class initialize properties and hooks.
     *
     * @since       1.7.0
     */
    public function __construct($data) { 
        $this->user_id = $this->get_user_id(); // set user id property
        $this->data = $data;
        $this->plugin_text_domain = 'tti-platform';
        parent::__construct( array( 
            'plural'    =>  'Assessments',    // Plural value used for labels and the objects being listed.
            'singular'  =>  'Assessment',     // Singular label for an object being listed, e.g. 'post'.
            'ajax'      =>  false,      // If true, the parent class will call the _js_vars() method in the footer      
        ));

    }

    /**
     * Text displayed when no customer data is available
     *
     * @since       1.7.0
     */
    public function no_items() {
      _e( 'No Assessments Avaliable', 'tti-platform' );
    }

    /**
     * Prepares the list of default columns
     * 
     * 
     * @since   1.7.0
     */
    public function column_default( $item, $column_name ) {     
        switch ( $column_name ) {           
            case 'title':
            case 'account_login':
            case 'api_service_location':
            case 'survey_location':
            case 'link_id':
            case 'send_rep_group_lead':
            case 'status_locked':
            case 'report_api_check':
            default:
              return $item[$column_name];
        }
    }

    /**
     * Prepares the list of items for displaying.
     * 
     * Query, filter data, handle sorting, and pagination, and any other data-manipulation required prior to rendering
     * 
     * @since   1.7.0
     */
    public function prepare_items() {
        $columns = $this->get_columns();
        $this->process_bulk_action();
        $hidden = array();

        $sortable = array();
        $this->_column_headers = array($columns, $hidden, $sortable);


        $this->fetch_table_data();


        $per_page     = $this->get_items_per_page( 'assessments_per_page', 10 );
        $current_page = $this->get_pagenum();
         if(!empty($this->ass_data)) {
            $total_items  = $this->ass_data;
        } else {
            $total_items  = array();
        }
        
        $this->set_pagination_args([
            'total_items' => count($total_items), //WE have to calculate the total number of items
            'per_page'    => $per_page //WE have to determine how many items to show on a page
         ]);

        $this->items = $this->ass_data;
    }

    /**
     * Prepares the bulk actions for assessments listing
     * 
     * @since   1.7.0
     */
    function get_bulk_actions() {
       $actions = array(
            'delete'    => 'Delete'
       );
       return $actions;
    }

    /**
     * Prepares the column title Edit & Delete actions
     * 
     * @since   1.7.0
     * @return array
     */
    function column_title($item) { 
       $actions = array (
            'edit'  => sprintf('<a href="?page=%s&action=%s&link_id=%s&user_id=%d">Edit</a>',$_REQUEST['page'],'edit',$item['link_id'], $_GET['user_id']),
            'delete'  => sprintf('<a href="?page=%s&action=%s&link_id=%s&user_id=%d">Delete</a>',$_REQUEST['page'],'delete',$item['link_id'],$_GET['user_id']),
        );
       return sprintf('%1$s %2$s', $item['title'], $this->row_actions($actions) );
    }


    /**
     * Get a list of columns. The format is:
     * 'internal-name' => 'Title'
     *
     * @since 1.7.0
     * 
     * @return array
     */ 
    public function get_columns() {
        $columns = array (
            'cb'                    => '<input type="checkbox" />',
            'title'                 => 'Title',
            'account_login'         => 'Account Login',
            'api_key'               => 'Api Key',
            'api_service_location'  => 'API Service Location',
            'survey_location'       => 'Survey Location',
            'link_id'               => 'Link ID',
            'send_rep_group_lead'   => 'Group Leader',
            'status_locked'         => 'Status',
            'report_api_check'      => 'Report Type',
          );
        return $columns;
    }


    /**
     * Render the bulk edit checkbox
     *
     * @param array $item
     *
     * @return string
     */
    function column_cb( $item ) {
      return sprintf(
        '<input type="checkbox" name="bulk-delete[]" value="%s" />', $this->user_id.'--'.$item['link_id']
      );
    }

    /**
     * Render the bulk edit checkbox
     *
     * @param array $item
     *
     * @return string
     */
    function column_status_locked( $item ) {
      if(empty($item['status_locked']) || $item['status_locked'] == 'false') {
        return 'Disable';
      } else {
        return 'Active';
      }
       
    }

    /**
     * Render the bulk edit checkbox
     *
     * @param array $item
     *
     * @return string
     */
    function column_report_api_check( $item ) {
      if(empty($item['report_api_check']) || strtolower($item['report_api_check']) == 'yes') {
        return 'API';
      } else {
        return 'Response';
      }
       
    }

    /**
     * Function to process the bulk action
     *
     * @return string
     */
    public function process_bulk_action() {

        // If the delete bulk action is triggered
        if ( ( isset( $_POST['action'] ) && sanitize_text_field($_POST['action']) == 'delete' )) {
            $delete_ids = esc_sql( $_POST['bulk-delete'] );
            // loop over the array of record IDs and delete them
            foreach ( $delete_ids as $id ) {
              $this->delete_assessment( sanitize_text_field($id) );
            }
            wp_redirect( get_site_url().'/wp-admin/users.php?user_id='.$this->user_id.'&page=tti-profile-assessment-page' );
            exit;
        }
    }

    /**
     * Delete a assessment record
     *
     * @param int $id assessment ID
     */
    public function delete_assessment( $id ) {
        $ids_details = explode('--', $id);
        $user_id = $ids_details[0];
        $link_id = $ids_details[1];
        if($link_id) {
            $lists = get_user_meta( $user_id, 'user_assessment_data', true);
            if(count(unserialize($lists)) >= 1) {
               $lists = unserialize($lists);
               unset($lists[$link_id]);
               update_user_meta( $user_id, 'user_assessment_data', serialize($lists));
               return true;
            } 
        }
        return false;
     
    }

    /*
     * Fetch table data from the WordPress database.
     * 
     * @since 1.7.0
     * 
     * @return  Array
     */
    
    public function fetch_table_data() {
        global $wpdb;

        $search = ( isset( $_POST['s'] ) ) ? sanitize_text_field($_POST['s']) : false;

        if(!empty($search)) {
            if($this->data) {
                foreach ($this->data as $key => $value) {
                    foreach ($value as $innerkey => $innervalue) {
                        if (strpos($innervalue, $search) !== false) {
                            $this->ass_data[] = $value;
                            break;
                        }
                    }
                }
            }
        } else {
            if($this->data) {
                foreach ($this->data as $key => $value) {
                   $this->ass_data[] = $value;
                }
            }
        }

        
    }



    /**
     * Get the user ID.
     *
     * Look for $_GET['user_id']. If anything else, force the user ID to the
     * current user's ID so they aren't left without a user to edit.
     *
     * @since 1.7.0
     *
     * @return int
     */
    private function get_user_id() {
        
        $this->user_id = (int) get_current_user_id();

        // We'll need a user ID when not on self profile.
        if ( ! empty( $_GET['user_id'] ) ) {
            $this->user_id = (int) $_GET['user_id'];
        }

        return $this->user_id;
    }



}

