<?php
/**
 * Class to download interview data of specific user into PDF
 *
 * @link       http://presstigers.com
 * @since      1.0.0
 *
 * @package    TTI_Platform_Application_Screening
 */



class TTI_Platform_Public_PDF_Report2
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
     * date
     * @var string
     */
    public $created_at_date;
    
    
    /**
     * contains output array
     * @var array 
     */
    public $output_arr;
    
    /**
     * number of sections
     * @var string 
     */
    public $no_of_sections;
    
    /**
     * Define the core functionality of the plugin for frontend.
     *
     * @since       1.0.0
     * @access   public
     */
    public function __construct($report_type, $assess_id, $cdate, $user_id=0)
    {   
        $this->assess_id = $assess_id;
        $this->created_at_date = $cdate;
        $this->report_type = $report_type;
        $this->user_id = $user_id;
        $this->mhtml = '';
        
    }

    /**
     * Initiziale type two PDF development
     *
     * @since       1.0.0
     * @access   public
     */
    public function init_pdf_process($output_arr)
    {   
        $this->output_arr = $output_arr;
        $this->create_report_html();
        $this->download_pdf();
    }
    
    
    /**
     * Function to convert interview data into PDF using mpdf library
     *
     * @since       1.6.7
     * @access   public
     */
    public function download_pdf()
    {   
         global $current_user; 
        

        define('_MPDF_TTFONTPATH', __DIR__ . '/ttfonts');
        
        // Require composer autoload
        require_once plugin_dir_path(__FILE__) . 'mpdf/vendor/autoload.php';
        
        $mpdfConfig = array(
            'mode' => 'utf-8',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 25,
            'margin_bottom' => 12,
            'margin_header' => 3,
            'margin_footer' => 5,
            'orientation' => 'L',
            'format' => [215.9, 279.4],
        );

        $this->mpdf->img_dpi = 96;

        
        $this->mpdf = new \Mpdf\Mpdf($mpdfConfig);

        $this->set_style();

        if($this->report_type == 'quick_strength') {
            /* Set header of report quick strength */
            $this->set_header2();  
        } else {
            /* Set header of report quick screen */
            $this->set_header();    
        }
        
        
        /* Set the body */
        //$this->set_html();
        
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

            if ( $this->report_type == 'quick_strength' ) {
                $file_name .= ' '.get_the_title($this->assess_id);
                $file_name .= '.pdf';
            } else {
                //$file_name .= ' '.get_the_title($this->assess_id);
                $file_name .= ' - Consolidation Report.pdf';
            }
        }

        
        if(isset($_GET['user_id'])) {
            $file_name = '';
            $user_id = sanitize_text_field($_GET['user_id']);
            
            // Get user data by user id
            $user = get_userdata( $user_id );
            
            if ( $this->report_type == 'quick_strength' ) {
                $file_name = $user->display_name;
                $file_name .= ' '.get_the_title($this->assess_id);
                $file_name .= '.pdf';
            } else {
                $file_name = $user->display_name;
                //$file_name .= ' '.get_the_title($this->assess_id);
                $file_name .= ' - Consolidation Report.pdf';
            }
        }

      //$this->mpdf->Output();
      $this->mpdf->Output($file_name, 'D');
     
      /* delete converted image */
     if( isset( $_GET['keyname'] )) {
        $keyname_old_check = $_GET['keyname'];
        unlink( plugin_dir_path(__FILE__) .$keyname_old_check.'.jpg' );
      }
      
      exit();
    }


    /**
     * Function to set the style of PDF report.
     *
     * @since       1.6.7
     * @access   public
     */
    public function set_style() {
        $ul_margin_bottom = 'margin-bottom: -13px;';
        $ul_padd_bottom = 'padding-bottom: 2px;';
        if($this->report_type == 'quick_screen') {
          $ul_margin_bottom = 'margin-bottom: 15px;';
          $ul_padd_bottom = 'padding-bottom: 3px;';
        }
        $this->mhtml .= ' 
        <style>
               @media print {
                    #break-after {
                        page-break-after: always;
                    }
                }
                body {
                    font-family:OpenSans;
                }
                ul {
                    padding-left: 0px;
                    list-style-position: outside;
                    
                }
                .move-fifteen-up {
                    '.$ul_margin_bottom.'
                }
                li {
                   
                }
                ul li {
                    '.$ul_padd_bottom.'
                    font-family:OpenSans;
                    padding-left: 0px;
                    list-style-position: inside;
                    list-style-type: disc;
                    list-style-position: inside;
                    text-indent: -1em;
                    padding-left: 1em;
                    font-size: 9pt;
                }
                .float-left {
                    float: left;
                    padding-left: 0px;
                }
                .float-right {
                    float: right;
                }
                .float-right-pd-left {
                    padding-left : 40px;
                }
                .left-section {
                    width: 58%;
                }
                .left-section-small {
                    width: 53%;
                    margin-right: 20px;
                }
                .heading-block {
                  text-align:left;
                    color:#fff;
                    margin-top: 15px;
                    font-family:exo;
                    font-size: 12pt;
                    border-radius: 20px 20px 0px 0px;
                    background: 
                        linear-gradient(
                          to right, 
                          #14A0D8 0%,
                          #217EBB 35%,
                          #226FAD 65%,
                          #1F5D97 100%
                        )
                        left 
                        bottom
                        #777    
                        no-repeat; 
                        padding-bottom: 15px;
                        padding-top: 15px;
                        padding-left: 30px;
                        margin-top: 20px;
                }
                .keys-leading-block {
                  text-align:left;
                    padding-top: 10px;
                    padding-left: 30px;
                    padding-bottom: 10px;
                    margin-bottom: 10px;
                    border-top: 0px;
                    border-bottom: 2px solid #1F5D97;
                    border-left: 2px solid #1F5D97;
                    border-right: 2px solid #1F5D97;
                    border-radius: 0px 0px 20px 20px;
                }
                .footer img {
                    margin-top: 15px;
                }
                .footer p {
                    font-size: 8pt; 
                    font-family: OpenSans;
                    margin-left: 30px;
                    margin-top: -6px;
                    padding-left: 50px;
                }
                
            </style>';
    }

    /**
     * Function to set the header of PDF report.
     *
     * @since       1.6.7
     * @access   public
     */
    public function set_header() {
        global $current_user; 

        $display_name = '';
        if(isset($_GET['user_id'])) {
            $user_id = sanitize_text_field($_GET['user_id']);
            
            // Get user data by user id
            $user = get_userdata( $user_id );
            $display_name = $user->display_name;
        }

        if(isset($display_name) && !empty($display_name)) {
            $this->mpdf->SetHTMLHeader ('
            <div style="width:64%;padding-right: 0px;">
                <div style="float:left;width:50%;text-align: left;">
                    <img src="https://ucarecdn.com/1bb4b658-2aa1-42ab-a8e6-48f140483a7c/QuickStrengthslogo02.png" width="250" style="margin-left: -10px;" /><br />
                    <div style="margin-left: 55px;margin-top: -25px;"><span style="font-size: 12pt;color:#050505;">'.$this->created_at_date.'</span></div>
                </div>
                <div style="float: right;width:50%;text-align: right;padding-top: 40px;">
                    <div style="font-size: 18pt;color:#227ABF;text-align: right;font-style:bold;"><b>'.esc_html(ucwords($display_name)).'</b></div>
                </div>
            </div>
            <div style="clear:both;"></div>
            <div style="text-align:center;
              padding-bottom:5px;
              margin-top: 1px;
              margin-bottom: 10px;
              width:64%;
              background: 
                linear-gradient(
                  to left, 
                  #14A0D8 0%,
                  #2097D2 12%,
                  #218ECC 47%,
                  #2684C6 100%
                )
                left 
                bottom
                #777    
                no-repeat; 
                background-size:100% 2px;"></div>
            '
        );
        } else {
            $this->mpdf->SetHTMLHeader ('
            <div style="width:64%;padding-right: 0px;">
                <div style="float:left;width:50%;text-align: left;">
                    <img src="https://ucarecdn.com/1bb4b658-2aa1-42ab-a8e6-48f140483a7c/QuickStrengthslogo02.png" width="250" style="margin-left: -10px;" /><br />
                    <div style="margin-left: 55px;margin-top: -25px;"><span style="font-size: 12pt;color:#050505;">'.$this->created_at_date.'</span></div>
                </div>
                <div style="float: right;width:50%;text-align: right;padding-top: 40px;">
                    <div style="font-size: 18pt;color:#227ABF;text-align: right;font-style:bold;"><b>'.esc_html(ucwords($current_user->display_name)).'</b></div>
                </div>
            </div>
            <div style="clear:both;"></div>
            <div style="text-align:center;
              padding-bottom:5px;
              margin-top: 1px;
              margin-bottom: 10px;
              width:64%;
              background: 
                linear-gradient(
                  to left, 
                  #14A0D8 0%,
                  #2097D2 12%,
                  #218ECC 47%,
                  #2684C6 100%
                )
                left 
                bottom
                #777    
                no-repeat; 
                background-size:100% 2px;"></div>
            '
        );
        }
        
        
    }

    /**
     * Function to set the header of PDF report.
     *
     * @since       1.6.7
     * @access   public
     */
    public function set_header2() {
        global $current_user; 
        
        if(isset($_GET['user_id'])) {
            $user_id = sanitize_text_field($_GET['user_id']);
            
            // Get user data by user id
            $user = get_userdata( $user_id );
            $display_name = $user->display_name;
        }

        if(isset($display_name) && !empty($display_name)) {
          $this->mpdf->SetHTMLHeader ('
            <div style="text-align:right;padding-bottom:0px;">
                <img src="https://ucarecdn.com/1bb4b658-2aa1-42ab-a8e6-48f140483a7c/QuickStrengthslogo02.png" width="250" style="float: left;margin-left:-10px;" />
                <div style="float: right;text-align:right;padding-top:10px;">
                    <span style="font-size: 18pt;color:#227ABF;font-style:bold;"><b>'.esc_html(ucwords($display_name)).'</b></span><br>
                    <span style="font-size: 12pt;color:#050505;">'.$this->created_at_date.'</span><br>
                </div>
            </div>
            <div style="text-align:center;
              padding-bottom:10px;
              background: 
                linear-gradient(
                  to left, 
                  #14A0D8 0%,
                  #2097D2 12%,
                  #218ECC 47%,
                  #2684C6 100%
                )
                left 
                bottom
                #777    
                no-repeat; 
                background-size:100% 1px;"></div>
            '
        );
        } else {
          $this->mpdf->SetHTMLHeader ('
            <div style="text-align:right;padding-bottom:0px;">
                <img src="https://ucarecdn.com/1bb4b658-2aa1-42ab-a8e6-48f140483a7c/QuickStrengthslogo02.png" width="250" style="float: left;margin-left:-10px;" />
                <div style="float: right;text-align:right;padding-top:10px;">
                    <span style="font-size: 18pt;color:#227ABF;font-style:bold;"><b>'.esc_html(ucwords($current_user->display_name)).'</b></span><br>
                    <span style="font-size: 12pt;color:#050505;">'.$this->created_at_date.'</span><br>
                </div>
            </div>
            <div style="text-align:center;
              padding-bottom:10px;
              background: 
                linear-gradient(
                  to left, 
                  #14A0D8 0%,
                  #2097D2 12%,
                  #218ECC 47%,
                  #2684C6 100%
                )
                left 
                bottom
                #777    
                no-repeat; 
                background-size:100% 1px;"></div>
            '
        );
        }

        
    }

     /**
     * Function to set the footer of PDF report.
     *
     * @since       1.6.7
     * @access   public
     */
    public function set_footer() {
        if($this->report_type == 'quick_strength') {
            $this->mpdf->SetHTMLFooter('
                    <div class="footer">
                        <div style="width:40%;float: left;"></div>
                        <div style="width:20%;float: left;"></div>
                        <div style="width:40%;float: right;" >
                            <img src="https://ucarecdn.com/fc56b4b3-372f-4d66-9ead-80e3d0350607/Hnetcomimage.png" style="width: 80%;"  />
                            <p>Copyright ©2004-2021. Insights International, Inc.</p>
                        </div>
                    </div>
            ');
        } else {
            $this->mpdf->SetHTMLFooter('
                    <div class="footer">
                        <div style="width:40%;float: left;"></div>
                        <div style="width:20%;float: left;"></div>
                        <div style="width:40%;float: right;" >
                            <img src="https://ucarecdn.com/fc56b4b3-372f-4d66-9ead-80e3d0350607/Hnetcomimage.png" style="width: 100%;"  />
                            <p>Copyright ©2004-2021. Insights International, Inc.</p>
                        </div>
                    </div>
            ');
        }
    }

     /**
     * Function to create HTML for PDF.
     *
     * @since       1.6.7
     * @access   public
     */
    public function create_report_html() { 
        if($this->report_type == 'quick_strength') {
            $this->mhtml = '<div style="padding-bottom: 5px;"></div>';
        } else {
            $this->mhtml = '<div style="padding-bottom: 10px;"></div>';
        }
        
        $this->mhtml .= '<div class="body">';
        
        if(isset($this->output_arr['text'])) {
            $this->mhtml .= '<div class="float-left left-section" >';

            $this->no_of_sections = count($this->output_arr['text']);
            if($this->report_type == 'quick_screen') {
              $this->no_of_sections = $this->no_of_sections - 2;
            }
            $count = 1;
            foreach ($this->output_arr['text'] as $key => $value) {

                if($this->report_type == 'quick_screen' && ($key == 'DONTS' || $key == 'DOS')) {
                    continue;
                }

                if($count == 1 || $this->no_of_sections <= 3) {
                    $this->create_report_heading($key);
                    $this->mhtml .= '<ul class="move-fifteen-up" style="font-size: 9pt;color:#242021;font-family:OpenSans;">';
                    foreach ($value as $innerkey => $innervalue) {
                        $this->mhtml .= '<li>';
                        $this->mhtml .= stripslashes($innervalue);
                        $this->mhtml .= '</li>';
                    }
                    $this->mhtml .= '</ul>';
                } elseif(($count == 2 || $count == 3)  && $this->no_of_sections >= 4 && $this->report_type == 'quick_strength') {
                    if($count == 2) {
                        $this->mhtml .= '<div>';
                        $this->mhtml .= '<div class="float-left left-section-small" >';
                    }
                    if($count == 3) {
                        $this->mhtml .= '<div class="float-right" >';
                    }
                    $this->create_report_heading($key);
                    $this->mhtml .= '<ul class="move-fifteen-up" style="font-size: 9pt;color:#242021;font-family:OpenSans;">';
                    foreach ($value as $innerkey => $innervalue) {
                        $this->mhtml .= '<li>';
                        $this->mhtml .= stripslashes($innervalue);
                        $this->mhtml .= '</li>';
                    }
                    $this->mhtml .= '</ul>';
                    $this->mhtml .= '</div>';
                    if($count == 3) {
                        $this->mhtml .= '</div>';
                    }
                   
                } elseif($count == 2  && $this->no_of_sections >= 4 && $this->report_type == 'quick_screen') {
                    $this->mhtml .= '<div>';
                    $this->create_report_heading($key);
                    $this->mhtml .= '<ul class="move-fifteen-up" style="font-size: 9pt;color:#242021;font-family:OpenSans;">';
                    foreach ($value as $innerkey => $innervalue) {
                        $this->mhtml .= '<li>';
                        $this->mhtml .= stripslashes($innervalue);
                        $this->mhtml .= '</li>';
                    }
                    $this->mhtml .= '</ul>';
                    $this->mhtml .= '</div>';
                   
                } elseif(($count == 4 || $count == 5)  && $this->no_of_sections > 4) {
                    if($count == 4) {
                        $this->mhtml .= '<div>';
                        $this->mhtml .= '<div class="float-left left-section-small" >';
                    }
                    if($count == 5) {
                        $this->mhtml .= '<div class="float-right" >';
                    }
                    $this->create_report_heading($key);
                    $this->mhtml .= '<ul class="move-fifteen-up" style="font-size: 9pt;color:#242021;font-family:OpenSans;">';
                    foreach ($value as $innerkey => $innervalue) {
                        $this->mhtml .= '<li>';
                        $this->mhtml .= stripslashes($innervalue);
                        $this->mhtml .= '</li>';
                    }
                    $this->mhtml .= '</ul>';
                    $this->mhtml .= '</div>';
                    if($count == 5) {
                        $this->mhtml .= '</div>';
                    }
                   
                }  elseif($count == 4  && $this->no_of_sections == 4 && $this->report_type == 'quick_strength') { 
                    $this->mhtml .= '<div>';
                    $this->create_report_heading($key);
                    $this->mhtml .= '<ul class="move-fifteen-up" style="font-size: 9pt;color:#242021;font-family:OpenSans;">';
                    foreach ($value as $innerkey => $innervalue) {
                        $this->mhtml .= '<li>';
                        $this->mhtml .= stripslashes($innervalue);
                        $this->mhtml .= '</li>';
                    }
                    $this->mhtml .= '</ul>';
                    $this->mhtml .= '</div>';
                } elseif(($count == 3 || $count == 4)  && $this->no_of_sections == 4 && $this->report_type == 'quick_screen') { 
                    if($count == 3) {
                        $this->mhtml .= '<div>';
                        $this->mhtml .= '<div class="float-left left-section-small" >';
                    }
                    if($count == 4) {
                        $this->mhtml .= '<div class="float-right" >';
                    }
                    $this->create_report_heading($key);
                    $this->mhtml .= '<ul class="move-fifteen-up" style="font-size: 9pt;color:#242021;font-family:OpenSans;">';
                    foreach ($value as $innerkey => $innervalue) {
                        $this->mhtml .= '<li>';
                        $this->mhtml .= stripslashes($innervalue);
                        $this->mhtml .= '</li>';
                    }
                    $this->mhtml .= '</ul>';
                    $this->mhtml .= '</div>';
                    if($count == 4) {
                        $this->mhtml .= '</div>';
                    }
                }

                $count++;
            } /* main loop ends here */
            $this->mhtml .= '</div>';
        }
        
        $this->initiate_right_section();
       
        $this->mhtml .= '</div>';

    }

     /**
     * Function to create heading for PDF.
     *
     * @since       1.6.7
     * @access   public
     */
    public function create_report_heading($key) {
        $heading = '';
        if($key == 'GENCHAR') {
            $heading = 'General Characteristics';
        } elseif($key == 'DOS') {
           $heading = 'Communication Tips';
        } elseif($key == 'IDEALENV') {
            $heading = 'Ideal Work Environment';
        } elseif($key == 'DONTS') {
            $heading = 'Communication Barriers';
        } elseif($key == 'MOT') {
            $heading = 'Keys to Motivating';
        } elseif($key == 'MAN') {
            $heading = 'Keys to Leading';
        }
        $this->mhtml .= '<p style="margin-bottom:-25px;font-size: 12pt;font-family:exo;color:#227ABE;">'.$heading.'</p>';
    }

     /**
     * Function to create right chart section of PDF.
     *
     * @since       1.6.7
     * @access   public
     */
    public function initiate_right_section() {
        $this->mhtml .= '<div class="float-right float-right-pd-left" style="text-align:center;">';
        

        if(isset($this->output_arr['images']['WHEEL']) && $this->report_type == 'quick_screen') {
            $this->output_charts($this->output_arr['images']['WHEEL']);
            if(isset($this->output_arr['images']['MICHART1'])) {
                $this->mhtml .= '<img src="'.sanitize_url($this->output_arr['images']['MICHART1']).'" width="100%" style="margin-top:20px;text-align:center;" />';
            }
        } 


        /* decide right section bottom part according to shortcode */
        if($this->report_type == 'quick_strength') {
            if(isset($this->output_arr['images']['MICHART1'])) {
                $this->mhtml .= '<img src="'.sanitize_url($this->output_arr['images']['MICHART1']).'" width="100%" style="margin-top:10px;text-align:center;" />';
            }

            if(isset($this->output_arr['images']['NaturalWheel'])) {
                $this->output_charts($this->output_arr['images']['NaturalWheel']);
            }
            $this->keys_leading_section();
        } 
        
        $this->mhtml .= '</div>';
    }

    /**
     * Function to handle outputing the charts.
     *
     *
     * @since       1.6.7
     * @access   public
     */
    public function output_charts($url)
    {
        $svg_url = $url;
        
        $random_numer = rand(5023430055,100324300550);
        //$svg_url = $this->strip_param_from_url( $svg_url, 'adaptedpos' );

        if( $this->user_id ) {
            $hit_url = plugin_dir_url( __FILE__ ).'tti_platform_convert_svg_jpg.php?report_type='.$this->report_type.'&user_id='.$this->user_id.'&key_name='.$random_numer.'&assess_id='.$this->assess_id.'&svg_url='.urlencode($svg_url);
        } else {
            $hit_url = plugin_dir_url( __FILE__ ).'tti_platform_convert_svg_jpg.php?report_type='.$this->report_type.'&key_name='.$random_numer.'&assess_id='.$this->assess_id.'&svg_url='.urlencode($svg_url);
        }
        
        $keyname_old_check = $_GET['keyname'];

        $wheel_width = '85%';
        $wheel_margin = 'margin-top:20px;';
        if($this->report_type == 'quick_screen') {
            $wheel_width = '85%';
            $wheel_margin = 'margin-top:-20px;';
        }
        if($this->report_type == 'quick_strength') {
            $wheel_width = '80%';
            $wheel_margin = 'margin-top:-20px;';
        }
        
        if(file_exists(plugin_dir_path( __FILE__ ).$keyname_old_check.'.jpg')) {
            $this->mhtml .= '<img src="'.plugin_dir_url( __FILE__ ).$keyname_old_check.'.jpg" width="'.$wheel_width.'"  style="'.$wheel_margin.';"/>';
        } else {
            header('Location: '.$hit_url);
        }
          
    }

    /**
     * Function to strip parameters from URL.
     *
     *
     * @since       1.6.7
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
     * @since       1.6.7
     * @param string $url contains file url
     * @access   public
     * @return boolean contains true/false
     */
    function check_file_exists_here($url){
       $result=get_headers($url);
       return stripos($result[0],"200 OK")?true:false; //check if $result[0] has 200 OK
    }


    /* Function to create keys to leading.
     *
     * @since       1.6.7
     * @access   public 
     */
    public function keys_leading_section() {
        $count = 0;
        
        foreach ($this->output_arr['text'] as $key => $value) {
            if($key == 'MAN') {
               
                foreach ($value as $innerkey => $innervalue) {
                    if(!empty($innervalue) && $count == 0) {
                        $this->mhtml .= '<div class="heading-block" >Keys to Leading Rodney</div>';
                        $this->mhtml .= '<div class="keys-leading-block" >';
                         $this->mhtml .= '<ul>';
                        $count=1;   
                    }
                    $this->mhtml .= '<li>'.stripslashes($innervalue).'</li>';
                }
            }
        }

        if($count == 1) {
            $this->mhtml .= '</ul>';
            $this->mhtml .= '</div>';
        }
    }

}