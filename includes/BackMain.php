<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( ! class_exists('MBLG_Main') ){
    class MBLG_Main {

        // customer WP_List_Table object
        public $customers_obj;
        // acces controle 
        public $cabability_access = array("1"=>'manage_options',
        "2"=>'edit_pages') ;
        
       

        // class constructor
        public function __construct() {
            add_filter( 'set-screen-option',   array(__CLASS__, 'set_screen')  , 25, 3 );
            add_action( 'admin_menu',  array( $this, 'plugin_menu')   );

            add_action( 'admin_enqueue_scripts', array( &$this, 'load_admin_scripts' ) );
        }

        public static function set_screen( $status, $option, $value ) {
            return $value;
        }

        public static function showsetting(){
            require_once( MBLG_ABSPATH . 'templates/BackSetting.php' );
        }

        public function plugin_menu() {
            global $wpdb;
            $table_option = $wpdb->prefix . "miragetgen_opt";
            $access_user =  $wpdb->get_var( "select value from " . $table_option . " where metakey='user_access'" );
            
            // $hook = add_menu_page(
            //     'Anonymous Visit',
            //     'Miraget Leads',
            //     $this->cabability_access[$access_user],
            //     'mblg_main',
            //      array($this, 'main_page_build') ,
            //     MBLG()->plugin_url() . '/image/miraget.png'
            // );
            $hook = add_menu_page(
                'Anonymous Visit',
                'Miraget Leads',
                'manage_options',
                'mblg_settings',
                 array($this, 'showsetting') ,
                MBLG()->plugin_url() . '/image/miraget.png'
            );

            // add_submenu_page( 'mblg_main', 
            // 'Settings', 
            // 'Settings', 
            // 'manage_options' , 
            // 'mblg_settings' ,  
            // array($this, 'showsetting')  );

            add_action( "load-$hook",  array($this, 'screen_option')  );
        }

        public function main_page_build(){
            wp_enqueue_style( 'mblg_style', MBLG()->plugin_url() . '/css/totalactivity.css', array(), MBLG_VERSION );

            ?>
            <div class="wrap pkd-container">

                <?php
                mblg_back_main_tab( 0 );
                ?>
                <br>
                <br>
                    <h1 style="float:left;">Miraget Leads</h1>
                    <?php
                     global $wpdb;
                     $useractivity_opt = $wpdb->prefix . "miragetgen_opt";
                     $limit_message_value = $wpdb->get_var( "select value from ".$useractivity_opt." where metakey='limit_message'" );
                     $limit_message_value = esc_html( $limit_message_value );
                     if( $limit_message_value != "" )
                         {
                            if(substr($limit_message_value,0,3) == '200' or substr($limit_message_value,0,3) == '201' )
                                 echo ' <span class=" apiStatus sec"> Miraget service available. </span>';
                                 else
                                echo '<span class="apiStatus err"> ' . $limit_message_value . '</span>';
                                 }
                            else{
                                 echo "";
                            }
                   ?>
                   <br class="clear">
                    
                <hr>
               
                
                <div id="poststuff">
                    <div id="post-body" class="metabox-holder columns-3">
                        <div id="post-body-content">

                           <div class='button'>
                                <a href="#" id ="export" role='button'>Export to CSV File
                                </a>
                            </div><!-- and -->   
                            
                            <div class="tablenav-pages">
                              
                                <input type='hidden' name='last_record_uid' id='last_record_uid' value='0'>
                                <span class="displaying-num" id='total_item'>2items</span>
                                <a id='go_first'    style='cursor:pointer'><span class="tablenav-pages-navspan" aria-hidden="true">«</span></a>
                                <a id='go_previous' style='cursor:pointer'><span class="tablenav-pages-navspan" aria-hidden="true">‹</span></a>
                                <label for="current-page-selector" class="screen-reader-text">Current Page</label>
                                <input class="current-page" id="current_page" type="text" name="current_page" value="1" size="1" aria-describedby="table-paging">
                                <span class="tablenav-paging-text"> of <input id="total_page" value="2" disabled style="text-align:center;  border:none; width:90px;" ></span>
                                <a id='go_next' style='cursor:pointer'><span class="tablenav-pages-navspan" aria-hidden="true">›</span></a>
                                <a id='go_last' style='cursor:pointer'><span class="tablenav-pages-navspan" aria-hidden="true">»</span></a>
                              

                            </div><!-- and -->
                            <br>
                            
                            <div class="meta-box-sortables ui-sortable" id="dvData">
                                <table  class="widefat">
                                    <thead>
                                    <tr>
                                        <th >Country</th>
                                        <th>Company</th>
                                        <th>Domain</th>
                                        <th>Page</th>
                                        <th>Device/OS</th>
                                        <th>Time</th>
                                        <th>Region/City</th>
                                        <th>Tel</th>
                                        <th>Email</th>
                                        <th>Visited</th>
                                    </tr>
                                    </thead>
                                    <tbody  id="mainTableDiv">

                                    </tbody>
                                </table>
                            </div><!-- and -->
                        </div><!-- and -->
                    </div>
                    <br class="clear">
                </div><!-- and post stuff-->
            </div><!--wrap pkd-container-->
       
        <!-- Scripts ----------------------------------------------------------- -->
        <script type='text/javascript' src='https://code.jquery.com/jquery-1.11.0.min.js'></script>
        
        <script type='text/javascript'>
            // $(document).ready(function () {

              
               
            //     function exportTableToCSV($table, filename) {
            //         var $headers = $table.find('tr:has(th)')
            //             ,$rows = $table.find('tr:has(td)')
                    
            //             ,tmpColDelim = String.fromCharCode(11) // vertical tab character
            //             ,tmpRowDelim = String.fromCharCode(0) // null character
            //             // actual delimiter characters for CSV format
            //             ,colDelim = '","'
            //             ,rowDelim = '"\r\n"';
            //             // Grab text from table into CSV formatted string
            //             var csv = '"';
            //             csv += formatRows($headers.map(grabRow));
            //             csv += rowDelim;
            //             csv += formatRows($rows.map(grabRow)) + '"';
            //             // Data URI
            //             var csvData = 'data:application/csv;charset=utf-8,' + encodeURIComponent(csv);
            //         $(this)
            //             .attr({
            //             'download': filename
            //                 ,'href': csvData
            //                 //,'target' : '_blank' //if you want it to open in a new window
            //         });
            //         //------------------------------------------------------------
            //         // Helper Functions 
            //         //------------------------------------------------------------
            //         // Format the output so it has the appropriate delimiters
            //         function formatRows(rows){
            //             return rows.get().join(tmpRowDelim)
            //                 .split(tmpRowDelim).join(rowDelim)
            //                 .split(tmpColDelim).join(colDelim);
            //         }
            //         // Grab and format a row from the table
            //         function grabRow(i,row){
                        
            //             var $row = $(row);
            //             //for some reason $cols = $row.find('td') || $row.find('th') won't work...
            //             var $cols = $row.find('td'); 
            //             if(!$cols.length) $cols = $row.find('th');  
            //             return $cols.map(grabCol)
            //                         .get().join(tmpColDelim);
            //         }
            //         // Grab and format a column from the table 
            //         function grabCol(j,col){
            //             //var regex = /(<div class="tooltip">(.*?)<\/div>|<br>|;)/g;
            //             var regex = /(<a(.*?)class="blgStr">(.*?)<\/a>|<span class="blgStr">(.*?)<\/span>|<br>|;)/g;
            //             var $col = $(col);
            //             var dd= $col.clone();
            //             var $clean_col = $col.html().replace(regex,'');// remove div class="tooltip">, <br>
                       
            //             dd.html($clean_col);
                        
            //             var  $text = dd.text().trim();
            //             var cleantxt = $text.replace(/\r?\n|\r/,'');//remove line break
            //             string = cleantxt.replace(/  +/g, ' ');//remove multiple white space
            //             return string.replace('"', '""'); // escape double quotes
            //         }
            //     }
            //     // This must be a hyperlink
            //     $("#export").click(function (event) {
            //         // var outputFile = 'export'
            //         function getFormattedTime() {
            //             var today = new Date();
            //             var y = today.getFullYear();
            //             // JavaScript months are 0-based.
            //             var m = today.getMonth() + 1;
            //             var d = today.getDate();
            //             var h = today.getHours();
            //             var mi = today.getMinutes();
            //             var s = today.getSeconds();
            //             return y + "-" + m + "-" + d + "-" + h + "-" + mi + "-" + s;
            //         }
            //         var outputFile = getFormattedTime()+'_datas.csv';
            //         //outputFile = outputFile.replace('.csv','') + '.csv'
                    
            //         // CSV
            //         exportTableToCSV.apply(this, [$('#dvData>table'), outputFile]);
                    
                    
            //     });
            // });
        </script>
        <?php
        }

        public function screen_option() {

            $option = 'per_page';
            $args   = array(
                'label'   => 'Customers',
                'default' => 25,
                'option'  => 'customers_per_page'
            );

            add_screen_option( $option, $args );
        }

        public function load_admin_scripts(){
            wp_enqueue_style( 'mblg_style', MBLG()->plugin_url() . '/css/totalactivity.css', array(), time() );
        }
    }
    $mblg = new MBLG_Main();
   
    
}
