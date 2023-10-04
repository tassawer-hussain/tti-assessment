<?php
/**
 * Class to download interview data of specific user into PDF
 *
 * @link       http://presstigers.com
 * @since      1.0.0
 *
 * @package    TTI_Platform_Application_Screening
 */


class TTI_Platform_Public_PDF_Report
{
    
    /**
     * Report type
     * @var string 
     */
    public $report_type;
    
    /**
     * Assessment id
     * @var integer
     */
    public $assess_id;
    
    /**
     * User ID
     * @var integer 
     */
    public $user_id;
    
    /**
     * mpdf library object
     * @var objet 
     */
    public $mpdf;
    
    /**
     * mpdf html
     * @var string 
     */
    public $mhtml;
    
    /**
     * version number
     * @var integer 
     */
    public $version;
    
    
    /**
     * contains output array
     * @var array 
     */
    public $output_arr;

     /**
     * contains string
     * @var string 
     */
    public $created_at_date;
    
    /**
     * file key
     * @var string 
     */
    public $file_key;
    
    /**
     * Define the core functionality of the plugin for frontend.
     *
     * @since       1.0.0
     * @access   public
     */
    public function __construct()
    { 
        $this->mhtml = '';
        
    }
    
    
    /**
     * Function to convert interview data into PDF using mpdf library
     *
     * @since       1.6.3
     * @access   public
     */
    public function download_pdf()
    {   
         global $current_user; 
        wp_get_current_user();

        define('_MPDF_TTFONTPATH', __DIR__ . '/ttfonts');
        
        // Require composer autoload
        require_once plugin_dir_path(__FILE__) . 'mpdf/vendor/autoload.php';
        
        $mpdfConfig = array(
            'mode' => 'utf-8',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 25,
            'margin_bottom' => 12,
            'margin_header' => 5,
            'margin_footer' => 5,
            'orientation' => 'L',
            'format' => [215.9, 279.4],
        );
        
        $this->mpdf = new \Mpdf\Mpdf($mpdfConfig);

        /* Set header of report */
        $this->set_header();    
        
        /* Set footer of report */
        $this->set_footer();

        $this->mpdf->useActiveForms  = true;
        $this->mpdf->curlAllowUnsafeSslRequests = true;
        $this->mpdf->autoPageBreak   = true;
        $this->mpdf->use_kwt         = true; // Default: false
        $this->mpdf->useKerning      = true; // set this to improve appearance of Circular text

        //$this->mpdf->mirrorMargins = 1;

        $this->mpdf->setAutoTopMargin='stretch';

        ob_get_clean();
        
        $this->mpdf->WriteHTML($this->mhtml);

        $file_name = 'consolidation report.pdf';

        if(isset($current_user->display_name)) {
            $file_name = ucwords($current_user->display_name);
            //$file_name .= ' '.get_the_title($this->assess_id);
            $file_name .= ' - Consolidation Report.pdf';
        }
       
        
        if(isset($_GET['user_id'])) {
            $file_name = '';
            $user_id = sanitize_text_field($_GET['user_id']);
            
            // Get user data by user id
            $user = get_userdata( $user_id );
            $file_name = $user->display_name;
            //$file_name .= ' '.get_the_title($this->assess_id);
            $file_name .= ' - Consolidation Report.pdf';
        }

      //$this->mpdf->Output();
      $this->mpdf->Output($file_name, 'D');
    
      /* delete converted image */
      if( isset( $_GET['keyname'] )) {
        $keyname_old_check = $_GET['keyname'];
        unlink( plugin_dir_path(__FILE__) . $keyname_old_check . '.jpg' );
      }
    }

    /**
     * Function to set the header of PDF report.
     *
     * @since       1.6.3
     * @access   public
     */
    public function set_header() {
        global $current_user; 
        wp_get_current_user();
        $display_name = '';
        if(isset($_GET['user_id'])) {
            $user_id = sanitize_text_field($_GET['user_id']);

            // Get user data by user id
            $user = get_userdata( $user_id );
            $display_name = $user->display_name;
        }

        
        if(isset($display_name) && !empty($display_name)) {
             $this->mpdf->SetHTMLHeader ('
            <div style="text-align:right;border-bottom:2px solid #000;padding-bottom:5px;font-family:montserrat;">
                <img src="https://ucarecdn.com/6e313bca-ab43-4bbc-83e1-11e35ea8f54c/Wordmark__Primary.png" width="280" style="float: left;" />
                <div style="float: right; text-align:right;">
                    <span style="font-size: 18px;color:#000;">'.esc_html(ucwords($display_name)).'</span><br>
                    <span style="font-size: 12px;color:#000;">'.$this->created_at_date.'</span><br>
                    <span style="font-size: 12px;color:#000;">'.__('Consolidation Report','tti-platform').'</span><br>
                </div>
            </div>
            '
        );
        } else {
             $this->mpdf->SetHTMLHeader ('
            <div style="text-align:right;border-bottom:2px solid #000;padding-bottom:5px;font-family:montserrat;">
                <img src="https://ucarecdn.com/6e313bca-ab43-4bbc-83e1-11e35ea8f54c/Wordmark__Primary.png" width="280" style="float: left;" />
                <div style="float: right; text-align:right;">
                    <span style="font-size: 18px;color:#000;">'.esc_html(ucwords($current_user->display_name)).'</span><br>
                    <span style="font-size: 12px;color:#000;">'.$this->created_at_date.'</span><br>
                    <span style="font-size: 12px;color:#000;">'.__('Consolidation Report','tti-platform').'</span><br>
                </div>
            </div>
            '
        );
        }
       
    }

     /**
     * Function to set the footer of PDF report.
     *
     * @since       1.6.3
     * @access   public
     */
    public function set_footer() {
        
     $this->mpdf->SetHTMLFooter('
        <table width="100%">
            <tr>
                <td width="50%" style="font-size:10px;text-align: left;font-family:montserrat;font-weight:300;">'.__('Copyright © 2004-2021. Insights International, Inc','tti-platform').'</td>
                <td width="25%" align="center"></td>
                <td width="25%" style="text-align: right;font-family:montserrat;font-weight:300;font-size:10px;">{PAGENO}</td>
            </tr>
        </table>');
    }
    
    /**
     * Function to download the report.
     *
     * @since 1.6.3
     * @param integer $assess_id contains assessment id
     * @param string $report_type contains report type 
     * @access   public
     */
    public function download_report($assess_id, $report_type)
    { 

        $this->assess_id = $assess_id;

        $this->report_type = $report_type;
        
        if ($report_type == 'type_one') {
            /* Initiate process number one */
            $this->init_type_one_process();
            $this->create_report_html();
            $this->download_pdf();

        } elseif ($report_type == 'quick_strength') {
            /* Initiate process number one */
            require_once 'class-tti-platform-pdf-report2.php';
            
            $this->init_type_two_process();
            $type_twp_report_class = new TTI_Platform_Public_PDF_Report2($report_type, $assess_id, $this->created_at_date, $this->user_id);
            $type_twp_report_class->init_pdf_process($this->output_arr);
        } elseif ($report_type == 'quick_screen') {
            /* Initiate process number one */
            require_once 'class-tti-platform-pdf-report2.php';
            
            $this->init_type_two_process();
            $type_twp_report_class = new TTI_Platform_Public_PDF_Report2($report_type, $assess_id, $this->created_at_date);
            $type_twp_report_class->init_pdf_process($this->output_arr);
        } else {
            _e('No Report Type Specified.', 'tti-platform');
        }
        
    }
    
    /**
     * Function to initiate type one process.
     *
     * @since       1.6.3
     * @access   public
     */
    public function init_type_one_process()
    {
        if (isset($this->assess_id) && isset($this->report_type)) {
            $this->get_report_one_data();
        } else {
            _e('No PDF Data Available.', 'tti-platform');
        }
    }

     /**
     * Function to initiate type one process.
     *
     * @since       1.6.3
     * @access   public
     */
    public function init_type_two_process()
    {
        if (isset($this->assess_id) && isset($this->report_type)) { 
            $this->get_report_one_data();
        } else {
            _e('No PDF Data Available.', 'tti-platform');
        }
    }

    /**
     * Function to create report html.
     *
     * It includes following information (GENCHAR, DOS, DONTS, IDEALENV)
     *
     * @since       1.6.3
     * @access   public
     */
    public function create_report_html() {
        $this->mhtml .= '<div>';

        $this->mhtml = '
            <style>
               @media print {
                    #break-after {
                        page-break-after: always;
                    }
                }

            </style>
        ';
        
        if(isset($this->output_arr['text'])) {
            $this->mhtml .= '<div style="font-family:montserrat;font-size:11px;width:100%;text-align:left;">';
            
            $this->output_charts();
            $this->mhtml .= '<div style="padding-right:25px;float:left;">';
            foreach ($this->output_arr['text'] as $key => $value) {
                if($key == 'GENCHAR') {
                    //$this->mhtml .= '<columns column-count="2" vAlign="J" column-gap="7" />';
                    $this->mhtml .= '<h2 style="font-family:montserrat;font-size:17px;text-align:left;"><b>General Characteristics</b></h2>';
                } elseif($key == 'DOS') {
                   // $this->mhtml .= '<columnbreak />';
                    if(isset($this->output_arr['text']['DONTS']) && isset($this->output_arr['text']['DOS'])) {
                        $this->mhtml .= '<div><div style="width:50%;float:left;text-align:left;">';
                    }
                    $this->mhtml .= '<h2 style="margin-top:-4px;font-family:montserrat;font-size:17px;text-align:left;"><b>Communication Tips</b></h2>';
                } elseif($key == 'DONTS') {
                   // $this->mhtml .= '<columns/>';
                    if(isset($this->output_arr['text']['DONTS']) && isset($this->output_arr['text']['DOS'])) {
                        $this->mhtml .= '<div style="width:50%;float:right;">';
                    }
                    $this->mhtml .= '<h2 style="margin-top:-4px;font-family:montserrat;font-size:17px;text-align:left;"><b>Communication Barriers </b></h2>';
                } elseif($key == 'IDEALENV') {
                    if(isset($this->output_arr['text']['MOT']) && isset($this->output_arr['text']['IDEALENV'])) {
                        $this->mhtml .= '<div><div style="width:50%;float:left;">';
                        $this->mhtml .= '<h2 style="margin-top:-4px;font-family:montserrat;font-size:17px;text-align:left;"><b>Ideal Environment</b></h2>';
                    } else {
                        $this->mhtml .= '<div><h2 style="font-family:montserrat;font-size:17px;text-align:left;"><b>Ideal Environment</b></h2>';
                    }

                } elseif($key == 'MOT') {
                    if(isset($this->output_arr['text']['MOT']) && isset($this->output_arr['text']['IDEALENV'])) {
                        $this->mhtml .= '<div style="width:50%;float:right;">';
                    }
                    $this->mhtml .= '<h2 style="margin-top:-4px;font-family:montserrat;font-size:17px;text-align:left;"><b>Keys to Motivating Me</b></h2>';
                } elseif($key == 'MAN') {
                    $this->mhtml .= '<div><h2 style=margin-top:-4px;font-family:monts"errat;font-size:17px;text-align:left;"><b>Keys to Leading Me</b></h2>';
                }

                if(isset($value) && !empty($value)) {
                    if($key == 'DOS') {
                       
                    } elseif ($key == 'DONTS') {
                        
                    }
                    $this->mhtml .= '<ul style="font-family:montserrat;font-size:11px;">';
                    foreach ($value as $innerkey => $innervalue) {
                        $this->mhtml .= '<li>';
                        $this->mhtml .= stripslashes($innervalue);
                        $this->mhtml .= '</li>';
                    }
                    $this->mhtml .= '</ul>';
                    if( $key == 'DOS' && isset($this->output_arr['text']['DONTS']) && isset($this->output_arr['text']['DOS'])) {
                        $this->mhtml .= '</div>';
                    }
                    if( $key == 'DONTS' && isset($this->output_arr['text']['DONTS']) && isset($this->output_arr['text']['DOS'])) {
                        $this->mhtml .= '</div>';
                    }
                    if($key == 'DONTS' && isset($this->output_arr['text']['DONTS']) && isset($this->output_arr['text']['DOS'])) {
                        $this->mhtml .= '<div style="clear:both;"></div></div>';
                    }
                    if($key == 'IDEALENV' && isset($this->output_arr['text']['MOT']) && isset($this->output_arr['text']['IDEALENV'])) {
                        $this->mhtml .= '</div>';
                    }
                    if($key == 'MOT' && isset($this->output_arr['text']['MOT']) && isset($this->output_arr['text']['IDEALENV'])) {
                        $this->mhtml .= '</div><div style="clear:both;"></div>';
                    }
                    if($key == 'MAN') {
                        $this->mhtml .= '</div>';
                    }

                }
            }$this->mhtml .= '</div>';
            $this->mhtml .= '</div>';
        }
        $this->mhtml .= '</div>';
        
    }

    /**
     * Function to handle outputing the charts.
     *
     *
     * @since       1.6.3
     * @access   public
     */
    public function output_charts()
    {
        $svg_url = '';
        // border-left:2px solid #000;
        $this->mhtml .= '<div style="padding-left:20px;float:right;width: 30%;height:200px;" ><br>';
        if(isset($this->output_arr['images'])) {
            foreach ($this->output_arr['images'] as $key => $value) {
                if(!empty($value)) {
                if($key == 'WHEEL') {
                   $svg_url = $value;
                } else {
                    $this->mhtml .= '<img src="'.$value.'" width="auto" />';
                }
                  $this->mhtml .= '<br><br><br>';
                }
            }
        }
        
    $random_numer = rand(5023430055,100324300550);
    $svg_url = $this->strip_param_from_url( $svg_url, 'adaptedpos' );
    $hit_url = plugin_dir_url( __FILE__ ).'tti_platform_convert_svg_jpg.php?report_type='.$this->report_type.'&key_name='.$random_numer.'&assess_id='.$this->assess_id.'&svg_url='.urlencode($svg_url);
    $keyname_old_check = $_GET['keyname'];
    
    if($this->check_file_exists_here(plugin_dir_url( __FILE__ ).$keyname_old_check.'.jpg')) {
        $this->mhtml .= '<img src="'.plugin_dir_url( __FILE__ ).$keyname_old_check.'.jpg" width="auto" />';
    } else {
      header('Location: '.$hit_url);
    }
    
       $this->mhtml .= '</div>';

    }
    
    /**
     * Function to strip parameters from URL.
     *
     *
     * @since       1.6.3
     * @param string $url contains url
     * @param string $param contains parameter want to remove
     * @access public
     * @return string returns updated url
     */
    function strip_param_from_url( $url, $param )
    {
        $base_url = strtok($url, '?');              // Get the base url
        $parsed_url = parse_url($url);              // Parse it 
        $query = $parsed_url['query'];              // Get the query string
        parse_str( $query, $parameters );           // Convert Parameters into array
        unset( $parameters[$param] );               // Delete the one you want
        $new_query = http_build_query($parameters); // Rebuilt query string
        return $base_url.'?'.$new_query;            // Finally url is ready
    }


    /**
     * Function to check if file exists.
     *
     * @since       1.6.3
     * @param string $url contains file url
     * @access   public
     * @return boolean contains true/false
     */
    function check_file_exists_here($url){
       $result=get_headers($url);
       return stripos($result[0],"200 OK")?true:false; //check if $result[0] has 200 OK
    }

    /**
    * Function to convert svg to png usin (onlineConvertClient) API.
    *
    * @since 1.6.3
    * @param string $url contains file url
    * @access public
    * @return boolean
    */
    public function convert_svg_to_png($url) {
       
        require_once plugin_dir_path(__FILE__) . 'convert-lib/vendor/autoload.php';
        
        $config = new \OnlineConvert\Configuration();
        $config->setApiKey('main', '079dbc370ff7f536c51340bb215c0162');
        $client = new \OnlineConvert\Client\OnlineConvertClient($config, 'main');
        $syncApi = new \OnlineConvert\Api($client);

        $syncJob = [
            'input' => [
                [
                'type' => \OnlineConvert\Endpoint\InputEndpoint::INPUT_TYPE_UPLOAD,
                'source' => $url
                ]
            ],
            'conversion' => [
                [
                'target' => 'png'
                ]
            ]
        ];

        $syncJob = $syncApi->postFullJob($syncJob)->getJobCreated();

        return $syncJob['output'][0]['uri'];
    }

    
    /**
     * Function to get report data.
     *
     * It includes following information (GENCHAR, DOS, DONTS, IDEALENV)
     *
     * @since       1.6.3
     * @access   public
     */
    public function get_report_one_data()
    {
        global $wpdb, $current_usr;
        $current_usr           = wp_get_current_user();
        $current_user          = $current_usr->ID;
        $assessment_table_name = $wpdb->prefix . 'assessments';
        $link_id               = get_post_meta($this->assess_id, 'link_id', true);
        //$link_id = '119324MFJ';
       
        if(isset($_GET['user_id'])) {
            $user_id = sanitize_text_field($_GET['user_id']);
            $current_user = $user_id;
        }

        /* Get assessment version */
        if(isset($_GET['version'])) {
            $asses_version = sanitize_text_field($_GET['version']);
        } else {
            $asses_version = $this->get_current_user_assess_version($current_user, $link_id);
        }

        //$asses_version = $this->get_current_user_assess_version($current_user, $link_id);
        
        // echo '<pre>asses_version ';print_r($asses_version);'</pre>';
        // echo '<pre>link_id ';print_r($this->assess_id);'</pre>';exit();
        
        $this->user_id = $current_user;
        $results = $wpdb->get_row("SELECT selected_all_that_apply,assessment_result,    updated_at FROM $assessment_table_name WHERE user_id ='$current_user' AND link_id ='$link_id' AND status = 1 AND version = $asses_version");
        
        $selected_all_that_apply = unserialize($results->selected_all_that_apply);
        $selected_all_that_apply2 = unserialize($results->assessment_result);
       
        $this->created_at_date = date("M d, Y", strtotime($results->updated_at));

        
        if (count($selected_all_that_apply) > 0) { 
            $this->create_output_array_text($selected_all_that_apply);
            $this->create_output_array_images($selected_all_that_apply2);
        }
    }

     /**
     *  Function to get charts.
     *
     * @since   1.6.3
     * @param array $data contains API response data
     */
    public function create_output_array_images($data)
    {
        
        $sections = $data->report->sections;
        $assessmenrArr = array();
        $count = 0;
        
        foreach ($sections as $arrayResponseData) {
            // if ($arrayResponseData->type == 'MICHART2') {
            //         if(isset($arrayResponseData->graph_url)){
            //             $this->output_arr['images']['MICHART2']= $arrayResponseData->graph_url;
            //         }
            // }else
            if ($arrayResponseData->type == 'MICHART1') {
                    if(isset($arrayResponseData->graph_url)){
                        $this->output_arr['images']['MICHART1']= $arrayResponseData->graph_url;
                    }
            } 
            elseif ($arrayResponseData->type == 'WHEEL') {
                if(isset($arrayResponseData->wheel->url)){
                    $this->output_arr['images']['WHEEL']= $arrayResponseData->wheel->url;
                }
                if(isset($arrayResponseData->wheel->natural->url)){
                    $this->output_arr['images']['NaturalWheel']= $arrayResponseData->wheel->natural->url;
                }
            }
            $count++;
        }
        
    }
    
    /**
     *  Function to create text feedback array.
     *
     * @since   1.6.3
     * @param array $selected_all_that_apply contains selected options from text feedback
     */
    public function create_output_array_text($selected_all_that_apply)
    {   
        $output_arr = array();
        foreach ($selected_all_that_apply as $key => $value) {
            
            foreach ($value['statements'] as $innerkey => $innervalue) {
                foreach ($innervalue['stmts'] as $inkey => $invalue) {
                    $text  = $invalue['text'];
                    $value_check = $invalue['value'];
                    if ($value_check == 1) {
                        $output_arr['text'][$value['type']][] = $text;
                    }
                }
            }
        }
        if(isset($output_arr['text']['GENCHAR'])) {
            $this->output_arr['text']['GENCHAR'] = $output_arr['text']['GENCHAR'];
        } 
        if(isset($output_arr['text']['DOS'])) {
            $this->output_arr['text']['DOS'] = $output_arr['text']['DOS'];
        } 
        if(isset($output_arr['text']['DONTS'])) {
            $this->output_arr['text']['DONTS'] = $output_arr['text']['DONTS'];
        } 
        if(isset($output_arr['text']['IDEALENV'])) {
            $this->output_arr['text']['IDEALENV'] = $output_arr['text']['IDEALENV'];
        } 
        if(isset($output_arr['text']['MOT'])) {
            $this->output_arr['text']['MOT'] = $output_arr['text']['MOT'];
        } 
        if(isset($output_arr['text']['MAN'])) {
            $this->output_arr['text']['MAN'] = $output_arr['text']['MAN'];
        } 
        
    }
    
    /**
     *  Function to get user assessment latest version.
     *
     * @since   1.6.3
     * @param integer $c_usrid contains current user id
     * @param string $link_id contains assessment link id
     * @return integer returns count of assessment
     */
    public function get_current_user_assess_version($c_usrid, $link_id)
    {
        global $wpdb;
        $results               = array(
            'one'
        );
        $assessment_table_name = $wpdb->prefix . 'assessments';
        $results               = $wpdb->get_results("SELECT * FROM $assessment_table_name WHERE user_id ='$c_usrid' AND link_id='$link_id'");
        
        if (isset($results) && count($results) > 0) {
            return count($results);
        }
        return count($results);
    }
}