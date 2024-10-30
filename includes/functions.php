<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
 
//add_action( 'admin_footer', 'mblg_ajax_total_activity_front_end' );
function mblg_ajax_total_activity_front_end() {
    ?>
    <script type="text/javascript">
         
        jQuery(document).ready(function($) {

            $('#mvp #the-list tr td.column-primary,#mvp #the-list tr td.Page  ' ).each(function( index ) {
              var elmTxt =  $( this ).children("button").remove(); ;
                
               
               elmTxt =  $( this ).text() ;
                

                if(elmTxt.length > 25) {
                    elmTxt = elmTxt.substring(0,24)+"..."; 
                }
                $( this ).text( elmTxt ) ;
 
            }); 

            refresh_data( 0 );
            function fetch_out_page_information() {
                $.ajax({
                    method: "POST",
                    url: ajaxurl,
                    data: {
                        'action': 'mblg_ajax_total_activity_page_information',
                        'security' : '<?php echo wp_create_nonce( 'fetch_out_page_information' ) ?>',
                        'customers_per_page' : $( '#customers_per_page' ).val(),
                        'current_page' : $( '#current_page' ).val()
                    }
                })
                    .done(function( data ) {
                        var D = JSON.parse(data);
                        $("#total_page").val( D.total_page );
                        $("#total_item").html( D.total_item + " items");
                        $("#last_record_uid").val(D.last_record_uid);
                        setTimeout( refresh_data , 12000 , 1 );
                    })
            }

            function refresh_data( autoUpdate ) {
                $.ajax({
                    method: "POST",
                    url: ajaxurl,
                    data:  {
                        'action': 'mblg_ajax_total_activity_refresh_data',
                        'security' : '<?php echo wp_create_nonce( 'refresh_data' ) ?>',
                        'customers_per_page' : $( '#customers_per_page' ).val() ,
                        'current_page' : $( '#current_page' ).val() ,
                        'last_record_uid' : $('#last_record_uid').val(),
                        'autoUpdate' : autoUpdate
                    }
                })
                    .done(function( data ) {
                        if( data == "no update" ) {
                            setTimeout( refresh_data , 15000 , 1 );
                            var els = document.getElementsByTagName("td");
                            for(var i=0;i<els.length;i++){
                                els[i].style = "";
                            }
                        } else {
                            $("#mainTableDiv").html(data);
                            fetch_out_page_information();
                        }
                    })
                    .fail(function( data ) {
                        console.log('Failed AJAX Call :( /// Return Data: ' + data);
                    });
            }

            $( '#changefieldbtn' ).click( function() {
                //process_data();
            });

            $( '#updateDatabtn' ).click( function() {
                refresh_data(0);
            });

            $('#go_first').click( function() {
                $('#current_page').val( 1 );
                refresh_data(0);
            });

            $('#go_last').click( function() {
                $('#current_page').val( $('#total_page').val() );
                refresh_data(0);
            });

            $('#go_next').click( function() {
                var p = $('#current_page').val();
                if( p < $('#total_page').val() )
                {
                    $('#current_page').val( p * 1 + 1 );
                    refresh_data(0);
                }
            });

            $('#go_previous').click( function() {
                var p = $('#current_page').val();
                if(  p > 0 )
                {
                    $('#current_page').val( p * 1 - 1 );
                    refresh_data(0);
                }
            });
        });

        function view_company_detail( company ) {
            var regex = /&/gi;
            company = company.replace(regex,"%26");
            <?php
            
            $link = add_query_arg(
                array(
                    'page' => 'company_history'
                ),
                admin_url('admin.php')
            );
            echo "window.open('".$link."&company='+company,'_self');";
            ?>

        }
    </script>
    
    <?php
}

//add_action( 'wp_ajax_mblg_ajax_total_activity_refresh_data', 'mblg_ajax_total_activity_table_response' );
function mblg_ajax_total_activity_table_response() {

    if( ! isset( $_POST['customers_per_page'] ) ){
        wp_die();
    }

    check_admin_referer( 'refresh_data', 'security' );
    //check_ajax_referer( 'refresh_data', 'security' );

    if( ! current_user_can( 'edit_pages' ) ){
        wp_die();
    }
  
    $customers_per_page = isset( $_POST['customers_per_page'] ) ? (int) sanitize_text_field( $_POST['customers_per_page'] ) : 20;
    $current_page = isset( $_POST['current_page'] ) ? (int) sanitize_text_field( $_POST['current_page'] ) : 1;
    $dTable = mblg_get_list( $customers_per_page , $current_page  );

    $updated = 0;
    for( $i = 0 ; $i < count($dTable) ; $i++ )
        if( $dTable[$i]['UID'] > $_POST['last_record_uid'] )
            $updated = 1;

    if( $updated == 0 && $_POST['current_page'] == 1 && $_POST['autoUpdate'] == 1  )
    {
        die("no update");
    }
   
    $plugin_url = MBLG()->plugin_url();
   
    for( $i = 0 ; $i < count( $dTable ) ; $i++ ) {
        $td_style = " ";
        if( $_POST['last_record_uid'] > 0 && $dTable[$i]['UID'] > $_POST['last_record_uid'] )
            $td_style = " style='color:red'; ";

        if( isset( $dTable[$i]['Country'] ) && $dTable[$i]['Country'] )
            $ccode = $plugin_url . "/image/country/".strtolower($dTable[$i]['Country'])."_16.png";
        if( $dTable[$i]['Company'] != "" ){
            // add unlock it for empty values
            foreach ($dTable[$i] as $key=> $value) {
                if ($dTable[$i][$key]=='' && $key != 'Country'){
                    $dTable[$i][$key] = '<a href="https://miraget.com/pricing/" target="_blank">Upgrade to unlock it</a>';
                }
            }
            
            ?>
             
            <tr>
                <td <?php echo $td_style; ?>><img src="<?php echo $ccode; ?>" width=16 height=11>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $dTable[$i]['Country']; ?></td>
                
                <td  <?php echo $td_style; ?>  ><a style='cursor:pointer' onclick='view_company_detail("<?php echo $dTable[$i]['Company']; ?>");'  <?php echo $td_style; ?> class="blgStr"><?php echo $dTable[$i]['Company']; ?></a>
                <div class="tooltip"><?php echo $dTable[$i]['Company']; ?></div>
               </td>

                 <td <?php echo $td_style; ?>>
                    <span class="blgStr"><?php echo $dTable[$i]['website']; ?></span>
                    <div class="tooltip"><?php echo $dTable[$i]['website']; ?></div>
                 </td>

                <td <?php echo $td_style; ?> ><span class="blgStr"><?php echo $dTable[$i]['Page']; ?></span>
                <div class="tooltip"><?php echo $dTable[$i]['Page']; ?></div>
                </td>
                <td <?php echo $td_style; ?>><?php echo $dTable[$i]['Device']; ?>&nbsp;<br>
                    <span class="ml-split-string"><?php echo $dTable[$i]['OS']; ?> </span>
                </td>
                <td <?php echo $td_style; ?>><?php echo '<span class="ml-time2line">'.$dTable[$i]['time'].'</span>'; ?></td>
                <td <?php echo $td_style; ?>>
                    <span class="blgStr"><?php echo $dTable[$i]['Region']; ?></span>
                    <div class="tooltip"><?php echo $dTable[$i]['Region']; ?></div>
                    <br>
                    <?php echo $dTable[$i]['City']; ?>
                </td>
                <td <?php echo $td_style; ?>><?php echo $dTable[$i]['Tel']; ?></td>
                <td <?php echo $td_style; ?>><?php echo $dTable[$i]['email']; ?></td>
                <td <?php echo $td_style; ?>><?php echo $dTable[$i]['TotalVisit']; ?></td>
            </tr>
            <?php
        }
    }
    ?>
      <script>
         jQuery(document).ready(function($) {
            

            $('.blgStr' ).each(function( index ) {
               var elmTxt =  $( this ).text() ;
               if(elmTxt.length > 15) {
                    elmTxt = elmTxt.substring(0,15)+"..."; 
                }
                $( this ).text( elmTxt ) ;
    
            });
            
             $('.ml-split-string' ).each(function( index ) {
                var elmTxt =  $( this ).text() ;
                 // regular expression to find all browser name 
                var regex = /(Chrome|Internet Explorer|Browser|Firefox|Safari)/g;
             
                // put a line break befor each browser name
                var newTxt = elmTxt.replace(regex,'<br>' + '$&');
                 $( this ).html(newTxt)  ;
    
             });
             $('.ml-time2line' ).each(function( index ) {
               var elmTxt =  $( this ).text() ;
               
               var newTxt = elmTxt.replace(' ','<br>');
                 $( this ).html(newTxt)  ;
    
            });
            
          
            
         })
      </script>
    <?php
    wp_die();
}

//add_action( 'wp_ajax_mblg_ajax_total_activity_page_information', 'mblg_ajax_total_activity_page_info' );
function mblg_ajax_total_activity_page_info(){

    check_admin_referer( 'fetch_out_page_information', 'security' );
    //check_ajax_referer( 'fetch_out_page_information', 'security' );

    if( ! current_user_can( 'edit_pages' ) ){
        wp_die();
    }

    $total_items  = mblg_record_count();
    $last_record_uid  = mblg_last_record();

    if( ! isset( $_POST['customers_per_page'] ) || empty( $_POST['customers_per_page'] ) ){
        $customers_per_page = 25;
    }else{
        $customers_per_page = (int) $_POST['customers_per_page'];
    }

    $res['total_item'] 	= $total_items;
    $res['last_record_uid'] = $last_record_uid;
    $res['total_page'] = round($total_items /  $customers_per_page ) + ( $total_items % $customers_per_page > 0 ? 1 : 0 );
    echo json_encode($res);
    wp_die();
}

// From BackMain.php
function mblg_get_list( $per_page = 25, $page_number = 1 ){
    global $wpdb;
    $useractivity = $wpdb->prefix . "miragetgen_data";
    $useractivity_opt = $wpdb->prefix . "miragetgen_opt";
    $v = $wpdb->get_var( "select value from " . $useractivity_opt . " where metakey='keep_period'" );

    $wpdb->query( "delete from " . $useractivity . " where DATE_FORMAT(time,'%Y-%m-%d')<'".date('Y-m-d', strtotime('-' . $v . ' days'))."'"  );

    // fetch out ip list with all column

    $sql = "
		select UID,DNS,time,Country,Company,Tel,Device,Page,email,Region,OS,COUNT(*) as TotalVisit from " . $useractivity."  GROUP BY(Company) 
	"; 
 
    $display_limit = $wpdb->get_var( "select value from ".$useractivity_opt." where metakey='display_limit'" );

    if ( isset( $_REQUEST['orderby'] ) ) {
        $order_by = esc_sql( sanitize_text_field( $_REQUEST['orderby'] ) );
    } else {
        $order_by = 'time';
    }
    if($page_number==0)$per_page=0;
    $order = 'DESC';

    $sql .= ' ORDER BY ' . $order_by . ' ' . $order;
    $sql .= " LIMIT $per_page";
    $sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;
    $result = $wpdb->get_results( $sql, 'ARRAY_A' );
    //var_dump($result);
    return $result;
}

function mblg_record_count() {
    // get count of record
    global $wpdb;
    $useractivity = $wpdb->prefix."miragetgen_data";
    $useractivity_opt = $wpdb->prefix."miragetgen_opt";
    $sql = "
		select count(*) from ".$useractivity."
	";

    return $wpdb->get_var( $sql );
}

function mblg_last_record(){
    global $wpdb;
    $useractivity = $wpdb->prefix . "miragetgen_data";
    $useractivity_opt = $wpdb->prefix . "miragetgen_opt";
    $sql = "
		select max(UID) from " . $useractivity."
	";

    return $wpdb->get_var( $sql );
}

// From BackMainTab.php
function mblg_back_main_tab( $select ){
    $p[$select] = 'nav-tab-active';
    ?>
    <div class="miraget-no-curl update-nag" id="miraget-noCurl">Your PHP curl not working please click <a href="https://miraget.com/solved-php-curl-not-working/" target="_blank">here</a> to solve this problem</div>
    <h2 class="nav-tab-wrapper pkd-container">
        <a href="admin.php?page=mblg_main" class="nav-tab <?php echo $p[0]; ?>">
            Visitors page
        </a>
        <!-- <a href="admin.php?page=company_history" class="nav-tab  <?php echo $p[1]; ?>">
            Company Information
        </a> -->
        <a href="admin.php?page=MostVisistPage_history" class="nav-tab  <?php echo $p[2]; ?>">
            Most visited Pages
        </a>
        <a href="admin.php?page=ActiveCompany_history" class="nav-tab  <?php echo $p[3]; ?>">
            Most Active Companies
        </a>
        <a href="admin.php?page=Analytics_history" class="nav-tab  <?php echo $p[4]; ?>">
            Analytics
        </a>
    </h2>
    <?php
}

// From BackCompany.php mblg_ajax_total_activity_update_duration
//add_action( 'wp_ajax_nopriv_mblg_ajax_total_activity_update_duration', 'mblg_ajax_update_duration' );
function mblg_ajax_update_duration(){
    
    check_admin_referer( 'update_duration', 'security' );
    //check_ajax_referer( 'update_duration', 'security' );
    /**
     * why this condition 
     * the user mybe not admin only visitor
     */
    /*if( ! current_user_can( 'edit_pages' ) ){
        wp_die();
    }*/
    
     
    global $wpdb;
    if( ! isset( $_POST['duration']) || ! isset( $_POST['uid'] ) )
        return;
    $useractivity = $wpdb->prefix."miragetgen_data";

    $duration = (int) sanitize_text_field( $_POST['duration'] );
    $uid = (int) sanitize_text_field( $_POST['uid'] );
    $wpdb->query( "update ".$useractivity." set duration='" . $duration . "' where UID='" . $uid."'"  );

    echo $uid;
    die;
}

//add_action( 'wp_ajax_mblg_total_history_truncate', 'mblg_ajax_history_truncate' );
function mblg_ajax_history_truncate(){

    check_admin_referer( 'historydel', 'security' );
    //check_ajax_referer( 'historydel', 'security' );

    if( ! current_user_can( 'edit_pages' ) ){
        wp_die();
    }

    global $wpdb;
    $useractivity = $wpdb->prefix . 'miragetgen_data';
    $wpdb->query( 'truncate ' . $useractivity );
    echo 'success';
    die;
}

add_action( 'admin_menu', 'mblg_add_menu_items' );
function mblg_add_menu_items(){
    add_submenu_page( null, 'company history', 'company history', 'edit_pages' , 'company_history' , 'mblg_render_list_page' );
}

function mblg_render_list_page(){

    //Create an instance of our package class...
    $testListTable = new MBLG_Company_List_Table();

    global $wpdb;
    $useractivity = $wpdb->prefix . 'miragetgen_data';
    $company = isset( $_GET['company'] ) ? sanitize_text_field( $_GET['company'] ) : '';

    $sql = "
    select * from " . $useractivity . "
    where company='" . $company . "'
    group by company
    ";

    $result = $wpdb->get_results( $sql, 'ARRAY_A' );

    //Fetch, prepare, sort, and filter our data...
    $testListTable->prepare_items();
    wp_enqueue_style( 'mblg_style', MBLG()->plugin_url() . '/css/totalactivity.css', array(), MBLG_VERSION );

    ?>
    <div class="wrap pkd-container">

        <?php
        mblg_back_main_tab( 1 );
        ?>
        <br>
        <br>

        <div id="icon-users" class="icon32"><br/></div>
        <h3><?php echo $company; ?>  Informations
            <?php if($company =="")  echo "  (Please choose a company to display the details here)"; ?>
        </h3>
        <?php
        if($company !=""){
            ?>
            <div class="miraget-leads-flexbox">
            <div class="company_information_data" >
                <table class="widefat ">
                    <tbody>
                    <tr>
                        <td>Organization_name</td>
                        <td><?php echo $result[0]['Company'] ?></td>
                    </tr>
                    <tr>
                        <td>Phone</td>
                        <td><?php echo $result[0]['Tel'] ?></td>
                    </tr>
                    <tr>
                        <td>Website</td>
                        <td><?php echo $result[0]['website'] ?></td>
                    </tr>
                    <tr>
                        <td>Email</td>
                        <td><?php echo $result[0]['email'] ?></td>
                    </tr>
                    <tr>
                        <td>Address</td>
                        <td><?php echo $result[0]['address'] ?></td>
                    </tr>
                    <tr>
                        <td>Last_update</td>
                        <td><?php echo $result[0]['last_update'] ?></td>
                    </tr>
                    <tr>
                        <td>Number_employees</td>
                        <td><?php echo $result[0]['number_employees'] ?></td>
                    </tr>
                    <tr>
                        <td>Dateofincorp</td>
                        <td><?php echo $result[0]['dateofincorp'] ?></td>
                    </tr>
                    <tr>
                        <td>Nature_of_business</td>
                        <td><?php echo $result[0]['nature_of_business'] ?></td>
                    </tr>
                    <!-- add columns -->
                    <tr>
                        <td> Description</td>
                        <td><a href="https://miraget.com/pricing/" target="_blank">Upgrade to unlock it</a> </td>
                    </tr>
                    <tr>
                        <td> Company size</td>
                        <td><a href="https://miraget.com/pricing/" target="_blank">Upgrade to unlock it</a> </td>
                    </tr>
                    <tr>
                        <td> Company type</td>
                        <td><a href="https://miraget.com/pricing/" target="_blank">Upgrade to unlock it</a> </td>
                    </tr>
                    <tr>
                        <td> LinkedIn Page</td>
                        <td><a href="https://miraget.com/pricing/" target="_blank">Upgrade to unlock it</a> </td>
                    </tr>
                    <tr>
                        <td> Founded</td>
                        <td><a href="https://miraget.com/pricing/" target="_blank">Upgrade to unlock it</a> </td>
                    </tr>
                    </tbody>
                </table>
            </div>
  
            <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions --> 
            <form id="movies-filter" method="get" class="mrleads-small-form">
                <!-- For plugins, we also need to ensure that the form posts back to our current page -->
                <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
                <!-- Now we can render the completed list table -->
                <div id="mvp">
                    
                    <?php $testListTable->display() ?>
                </div>
            </form>
            </div>
            <?php
        
        }
        ?>

    </div>
    <?php
}

// From BackMostActiveCompany.php
add_action( 'admin_menu', 'mblg_active_company_add_menu_items' );
function mblg_active_company_add_menu_items(){
    add_submenu_page( null, 'ActiveCompany history', 'ActiveCompany history', 'edit_pages' , 'ActiveCompany_history' , 'mblg_active_company_render_list_page' );
}

function mblg_active_company_render_list_page(){

    //Create an instance of our package class...
    $testListTable = new MBLG_ActiveCompany_List_Table();
    //Fetch, prepare, sort, and filter our data...
    $testListTable->prepare_items();

    wp_enqueue_style( 'mblg_style', MBLG()->plugin_url() . '/css/totalactivity.css', array(), MBLG_VERSION );

    ?>
    <div class="wrap pkd-container">
        <?php
        mblg_back_main_tab( 3 );
        ?>
        <br>
        <br>

        <div id="icon-users" class="icon32"><br/></div>

        <h2>Most Active Companies</h2>
        <hr>
        <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
        <form id="movies-filter" method="get">
            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            <!-- Now we can render the completed list table -->
            <?php $testListTable->display() ?>
        </form>

    </div>
    <?php
}

// From BackMostVisitedPage.php
add_action( 'admin_menu', 'mblg_back_mostvisited_add_menu_items' );
function mblg_back_mostvisited_add_menu_items(){
    add_submenu_page( null, 'MostVisistPage history', 'MostVisistPage history', 'edit_pages' , 'MostVisistPage_history' , 'mblg_back_mostvisited_render_list_page' );

}

function mblg_back_mostvisited_render_list_page(){

    //Create an instance of our package class...
    $testListTable = new MBLG_MostVisistPage_List_Table();
    //Fetch, prepare, sort, and filter our data...
    $testListTable->prepare_items();

    wp_enqueue_style( 'mblg_style', MBLG()->plugin_url() . '/css/totalactivity.css', array(), MBLG_VERSION );

    ?>
    <div class="wrap pkd-container">
        <h2 class="nav-tab-wrapper">
            <?php
            mblg_back_main_tab( 2 );
            ?>
        </h2>
        <br>
        <br>

        <div id="icon-users" class="icon32"><br/></div>

        <h2>Most Visited pages</h2>
        
        <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
        <form id="movies-filter" method="get">
            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            <!-- Now we can render the completed list table -->
            <div id="mvp">
            <?php $testListTable->display() ?>
            </div>
        </form>

    </div>
    <?php
}
//analytics------------------------------------------------------------
add_action( 'admin_menu', 'mblg_back_analytics_add_menu_items' );
function mblg_back_analytics_add_menu_items(){
    add_submenu_page( null, 'Analytics history', 'Analytics history', 'edit_pages' , 'Analytics_history' , 'mblg_back_analytics_render_list_page' );

}

function mblg_back_analytics_render_list_page(){

    //Create an instance of our package class...
    $testListTable = new MBLG_MostVisistPage_List_Table();
    //Fetch, prepare, sort, and filter our data...
    $testListTable->prepare_items();

    wp_enqueue_style( 'mblg_style', MBLG()->plugin_url() . '/css/totalactivity.css', array(), MBLG_VERSION );

    ?>
    <!-- <script src="https://d3js.org/d3.v5.min.js"></script> -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.4.0/Chart.min.js"></script> 
    <div class="wrap pkd-container">
        <h2 class="nav-tab-wrapper">
            <?php
            mblg_back_main_tab( 4 );
           
            ?>
        </h2>
        <br>
        <br>

        <div id="icon-users" class="icon32"><br/></div>

    
        <br>
        
        <div id="chart-container">
            <div class="chart-child">
            <h4 class="text-center">Most visited pages</h4>
                <canvas id="myChart" width="400" height="300"></canvas>
            </div>
            <div class="chart-child">
                <h4 class="text-center">Visits per country</h4>
                <canvas id="myChart2" width="400" height="300"></canvas>
            </div>
            <div class="chart-child">
                <h4 class="text-center">Visits per Device</h4>
                <canvas id="myChart3" width="400" height="300"></canvas>
            </div>
            <div class="chart-child">
                <h4 class="text-center">Visits per OS/Browser</h4>
                <canvas id="myChart4" width="400" height="300"></canvas>
            </div>
        </div>
        <?php
            $alldatas =  $testListTable->get_rank_per_page();
            $dataset = array();
            $legends = array();
            foreach($alldatas as $data){
                array_push($dataset,$data['rank']);
                array_push($legends,$data['page']);
            }
            $alldatas_country =  $testListTable->get_rank_per_country();
            $dataset_country = array();
            $legends_country = array();
            foreach($alldatas_country as $data){
                array_push($dataset_country,$data['rank']);
                array_push($legends_country,$data['country']);
            }
            $alldatas_device =  $testListTable->get_rank_per_device();
            $dataset_device = array();
            $legends_device = array();
            foreach($alldatas_device as $data){
                array_push($dataset_device,$data['rank']);
                array_push($legends_device,$data['device']);
            }
            $alldatas_os =  $testListTable->get_rank_per_os();
            $dataset_os = array();
            $legends_os = array();
            foreach($alldatas_os as $data){
                array_push($dataset_os,$data['rank']);
                array_push($legends_os,$data['os']);
            }
        ?>
        
    
    
    <script type="text/javascript">

        
          var dataset = <?php echo json_encode($dataset); ?>;
          var legends = <?php echo json_encode($legends); ?>;
          var dataset_country = <?php echo json_encode($dataset_country); ?>;
          var legends_country = <?php echo json_encode($legends_country); ?>;
          var dataset_device = <?php echo json_encode($dataset_device); ?>;
          var legends_device = <?php echo json_encode($legends_device); ?>;
          legends_device = legends_device.map(x=>{
              return (x == '')?'unknown':x;
          });
          var dataset_os = <?php echo json_encode($dataset_os); ?>;
          var legends_os = <?php echo json_encode($legends_os); ?>;

        //   function reduceArray(tab){
        //       var tabsix = tab.slice(0,6);
        //       var rest = tab.slice(6,tab.length).reduce( (x,y)=>x+y );
        //       return tabsix.concat(rest);
        //   }

          var ctx = document.getElementById('myChart').getContext('2d');
               dataset_pages = dataset.slice(0,6);
               legends_pages = legends.slice(0,6);
                var chart = new Chart(ctx, {
                    // The type of chart we want to create
                    type: 'doughnut',

                    // The data for our dataset
                    data: {
                        labels: legends_pages,
                        datasets: [{
                            label: "Most Visited Page",
                            backgroundColor: [
                                    'rgba(117, 215, 164, 1)',
                                    'rgba(54, 162, 235, 0.2)',
                                    'rgba(255, 206, 86, 0.2)',
                                    'rgba(75, 192, 150, 0.2)',
                                    'rgba(255, 140, 0, 1)',
                                    'rgba(124, 116, 236, 1)',
                                    'rgba(239, 82, 77, 1)',
                                    'rgba(181, 130, 115, 1)',
                                    'rgba(180, 164, 19, 1)', 
                            ],
                            borderColor: 'rgba(179, 179, 179, 1)',
                            data: dataset_pages,
                        }]
                    },

                    // Configuration options go here
                    option:{}
                    }
                );
                var ctx2 = document.getElementById('myChart2').getContext('2d');
          
                var chart2 = new Chart(ctx2, {
                    // The type of chart we want to create
                    type: 'doughnut',

                    // The data for our dataset
                    data: {
                        labels: legends_country.slice(0.10),
                        datasets: [{
                            label: "Visits per country",
                            backgroundColor:  [
                                    'rgba(117, 215, 164, 1)',
                                    'rgba(54, 162, 235, 0.2)',
                                    'rgba(255, 206, 86, 0.2)',
                                    'rgba(75, 192, 150, 0.2)',
                                    'rgba(255, 140, 0, 1)',
                                    'rgba(124, 116, 236, 1)',
                                    'rgba(239, 82, 77, 1)',
                                    'rgba(181, 130, 115, 1)',
                                    'rgba(180, 164, 19, 1)',    
                                ],
                            borderColor: 'rgba(179, 179, 179, 1)',
                            data: dataset_country.slice(0,10),
                        }]
                    },

                    // Configuration options go here
                    option:{}
                    }
                );
                var ctx3 = document.getElementById('myChart3').getContext('2d');
                
                var chart3 = new Chart(ctx3, {
                        // The type of chart we want to create
                        type: 'doughnut',

                            // The data for our dataset
                            data: {
                                labels: legends_device,
                                datasets: [{
                                    label: "Visists per device",
                                    backgroundColor:   [
                                        'rgba(117, 215, 164, 1)',
                                    'rgba(54, 162, 235, 0.2)',
                                    'rgba(255, 206, 86, 0.2)',
                                    'rgba(75, 192, 150, 0.2)',
                                    'rgba(255, 140, 0, 1)',
                                    'rgba(124, 116, 236, 1)',
                                    'rgba(239, 82, 77, 1)',
                                    'rgba(181, 130, 115, 1)',
                                    'rgba(180, 164, 19, 1)',     
                                        ],
                                    borderColor: 'rgba(179, 179, 179, 1)',
                                    data: dataset_device,
                                }]
                            },

                    // Configuration options go here
                    option:{}
                    }
                );
                var ctx4 = document.getElementById('myChart4').getContext('2d');
                legends_os = legends_os.map(x=>x.replace('&nbsp;',' '));
                var chart3 = new Chart(ctx4, {
                        // The type of chart we want to create
                    type: 'doughnut',

                    // The data for our dataset
                    data: {
                        labels: legends_os.slice(0,10),
                        datasets: [{
                            label: "Visits per OS",
                            backgroundColor:   [
                                    'rgba(117, 215, 164, 1)',
                                    'rgba(54, 162, 235, 0.2)',
                                    'rgba(255, 206, 86, 0.2)',
                                    'rgba(75, 192, 150, 0.2)',
                                    'rgba(255, 140, 0, 1)',
                                    'rgba(124, 116, 236, 1)',
                                    'rgba(239, 82, 77, 1)',
                                    'rgba(181, 130, 115, 1)',
                                    'rgba(180, 164, 19, 1)',   
                                ],
                            borderColor: 'rgba(179, 179, 179, 1)',
                            data: dataset_os.slice(0,10),
                        }]
                    },


                    // Configuration options go here
                    option:{}
                    }
                );
              
    
    </script>
</div> 
    
    <?php
}

// From FrontMonitor.php
function mblgGetBrowser() {

    $user_agent     =   $_SERVER['HTTP_USER_AGENT'];

    $browser        =   "Unknown Browser";

    $browser_array  =   array(
        '/msie/i'       =>  'Internet Explorer',
        '/firefox/i'    =>  'Firefox',
        '/safari/i'     =>  'Safari',
        '/chrome/i'     =>  'Chrome',
        '/edge/i'       =>  'Edge',
        '/opera/i'      =>  'Opera',
        '/netscape/i'   =>  'Netscape',
        '/maxthon/i'    =>  'Maxthon',
        '/konqueror/i'  =>  'Konqueror',
        '/mobile/i'     =>  'Handheld Browser'
    );

    foreach ($browser_array as $regex => $value) {

        if (preg_match($regex, $user_agent)) {
            $browser    =    '&nbsp;' . $value ;
        }

    }

    return $browser;
}

function mblgGetOS() {

    $user_agent     =   $_SERVER['HTTP_USER_AGENT'];

    $os_platform    =   "Unknown OS Platform";

    $os_array       =   array(
        '/windows nt 10/i'     =>  'Windows 10',
        '/windows nt 6.3/i'     =>  'Windows 8.1',
        '/windows nt 6.2/i'     =>  'Windows 8',
        '/windows nt 6.1/i'     =>  'Windows 7',
        '/windows nt 6.0/i'     =>  'Windows Vista',
        '/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
        '/windows nt 5.1/i'     =>  'Windows XP',
        '/windows xp/i'         =>  'Windows XP',
        '/windows nt 5.0/i'     =>  'Windows 2000',
        '/windows me/i'         =>  'Windows ME',
        '/win98/i'              =>  'Windows 98',
        '/win95/i'              =>  'Windows 95',
        '/win16/i'              =>  'Windows 3.11',
        '/macintosh|mac os x/i' =>  'Mac OS X',
        '/mac_powerpc/i'        =>  'Mac OS 9',
        '/linux/i'              =>  'Linux',
        '/ubuntu/i'             =>  'Ubuntu',
        '/iphone/i'             =>  'iPhone',
        '/ipod/i'               =>  'iPod',
        '/ipad/i'               =>  'iPad',
        '/android/i'            =>  'Android',
        '/blackberry/i'         =>  'BlackBerry',
        '/webos/i'              =>  'Mobile'
    );

    foreach ($os_array as $regex => $value) {

        if (preg_match($regex, $user_agent)) {
            $os_platform    =   $value;
        }

    }

    return $os_platform;
}

function mblgDetectDevice(){

    $userAgent = $_SERVER["HTTP_USER_AGENT"];
    $devicesTypes = array(
        "computer" => array("msie 10", "msie 9", "msie 8", "windows.*firefox", "windows.*chrome", "x11.*chrome", "x11.*firefox", "macintosh.*chrome", "macintosh.*firefox", "opera"),
        "tablet"   => array("tablet", "android", "ipad", "tablet.*firefox"),
        "mobile"   => array("mobile ", "android.*mobile", "iphone", "ipod", "opera mobi", "opera mini"),
        "bot"      => array("googlebot", "mediapartners-google", "adsbot-google", "duckduckbot", "msnbot", "bingbot", "ask", "facebook", "yahoo", "addthis")
    );
    foreach($devicesTypes as $deviceType => $devices) {
        foreach($devices as $device) {
            if(preg_match("/" . $device . "/i", $userAgent)) {
                $deviceName = $deviceType;
            }
        }
    }
    return ucfirst( $deviceName );
}
//function Decruption for api_token  
function easyDecryption($args){
    $value = substr($args,1);
    return substr($value,0, -1);   
}
function mrgt_proprites_exist($class,$prop){
    return property_exists ( $class , $prop ) ? $class->$prop : '' ;
} 
function mblg_include_footer_script() {
    global $wpdb;
    $useractivity = $wpdb->prefix . 'miragetgen_data';
    $useractivity_opt = $wpdb->prefix . 'miragetgen_opt';
    
    // don't run on ajax calls
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
        return;
    }
    
    $exc = array( 'png','jpg','.js', 'bmp' , 'csv', 'pdf' , 'css' );
    $thr = strtolower( substr($_SERVER['REQUEST_URI'], -3 ) );

    if( in_array( $thr , $exc ) ) return;
    
    $result = $wpdb->get_results( "select value from ".$useractivity_opt." where metakey = 'fpattern'", 'ARRAY_A' );
    
    foreach ($result as $item)
        if( strpos( $_SERVER['REQUEST_URI'] , $item['value']) )
            return;

    // Main installation of database
    
    if( is_admin() ) {
        return;
    }
   // echo 'ddd' ;
    // Fetch user information

    if( $_SERVER['REMOTE_ADDR'] == '::1' )
        $_SERVER['REMOTE_ADDR'] = 'localhost';
    $ip = $_SERVER['REMOTE_ADDR'] ; //   7.9.1.2
    //$ip = '7.9.1.2';
 
    $daily_limit = array(1=>'100',2=>'200',3=>'400',4=>'800',5=>'2000');
    $subscribe_type = $wpdb->get_var( "select value from ".$useractivity_opt." where metakey='subscribe'" );
    $today_value =  date( DATE_RFC822 );
    $today_count = $wpdb->get_var( "select count(*) from ".$useractivity." where time='".$today_value."'" );
   
    if( filter_var($ip, FILTER_VALIDATE_IP) ) {
        
        $dns = gethostbyaddr($ip);
        if( strpos($dns, 'bot') !== false ) return;
        $user = wp_get_current_user();
        $useremail = $user->user_email;
        $userdomain = home_url();
        
       
        
        $output_token = easyDecryption($wpdb->get_var( "select value from ".$useractivity_opt." where metakey='key'" ));
        if( $output_token == "" ) return;
       

        $url = "https://api.miraget.com/plugin?IP=" . $ip;
 
        
        // create curl resource 
        $ch = curl_init(); 

        // set url 
        curl_setopt($ch, CURLOPT_URL, $url); 
         
        //header
        curl_setopt($ch, CURLOPT_HTTPHEADER,  ["X-Api-Key: ".$output_token]  );

        
        //return the transfer as a string 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        
        // $output contains the output string 
        
        $result_value_original = curl_exec($ch); 
        
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // close curl resource to free up system resources 
        curl_close($ch);  
        
        
        $result_value = json_decode($result_value_original);
 
        $country = mrgt_proprites_exist($result_value , 'country') ;
        $region = mrgt_proprites_exist($result_value , 'stateprov') ; 
        $city = mrgt_proprites_exist($result_value , 'city') ; 
        $company = mrgt_proprites_exist($result_value , 'organization_name') ;  
        $email = mrgt_proprites_exist($result_value , 'email') ; 
        $phone = mrgt_proprites_exist($result_value , 'phone') ; 
        $website = mrgt_proprites_exist($result_value , 'website') ;  
        //$address = $result_value->address;
        $address = $city . ' / ' . $country;
        $last_update = mrgt_proprites_exist($result_value , 'last_update') ;  
        $number_employees = mrgt_proprites_exist($result_value , 'number_employees') ;  
        $dateofincorp = mrgt_proprites_exist($result_value , 'dateofincorp') ;  
        $nature_of_business = mrgt_proprites_exist($result_value , 'nature_of_business') ; 

        $message = mrgt_proprites_exist($result_value , 'message') ;  
        $message_data = esc_sql( $http_status . $message );
 
    }else die('Invalid IP') ;
    
    if( $message || ( int ) $http_status > 0 )
        $wpdb->update( $useractivity_opt , array( "value"=>$message_data ) , array("metakey" => "limit_message" ) ); 

    if( $company != "" ){
        $wp_arr = array( 'DNS' => $dns ,
            'Page' =>  getCurentPage() , 'time' => date("Y-m-d H:i:s") , 'Device' => mblgDetectDevice(), 'OS' => mblgGetOS()."<br>".mblgGetBrowser()  ,
            'Country'=> isset($country) ? $country : '' , 'City'=>isset($city) ? $city : '' , 'Region'=> isset($region) ?  $region : '' ,
            'Company' => isset($company) ? $company : '' , 'email'=>isset($email) ? $email : '' , 'Tel'=>isset($phone) ? $phone : '',
            'website'=> isset($website) ? $website : '' , 'address'=>isset($address) ? $address : '' , 'last_update'=> isset($last_update) ?  $last_update : '' ,
            'number_employees' =>isset($number_employees) ? $number_employees : '' , 'dateofincorp'=>isset($dateofincorp) ? $dateofincorp : '' , 'nature_of_business'=>isset($nature_of_business) ? $nature_of_business : ''
        );//"$_SERVER['REQUEST_URI']"

        $wp_arr = array_map( 'sanitize_text_field', $wp_arr );

        $wpdb->insert( $useractivity, $wp_arr ) ;
        $lastid = $wpdb->insert_id;
        
         
        echo  $lastid ;
        die ;
    }else die('empty') ;
      

}
function getCurentPage(){
   
    $url = "http" . (($_SERVER['SERVER_PORT'] == 443) ? "s://" : "://") . $_SERVER['HTTP_HOST'] ;
    $http_ref =  $_SERVER['HTTP_REFERER']  ;
    return str_replace( $url, '', $http_ref ) ;
}
/**
 * update duration of visitor every 5 s 
 */
function updatDuration(){
    ?>
    <script type='text/javascript'>
        jQuery(document).ready(function($) {
            var startTime = new Date();
            <?php
            $link = admin_url('admin-ajax.php') ;
            echo "var ajaxurl = '$link';";
            ?>
            function mblg_update_duration(){
                var endTime = new Date();
                var timeDiff = endTime - startTime + 1000;
                timeDiff /= 1000;

                $.ajax({
                    method: "POST",
                    url: ajaxurl,
                    data: {
                        'action': 'mblg_ajax_total_activity_update_duration',
                        'security' : '<?php echo wp_create_nonce( 'update_duration' ) ?>',
                        'uid': '<?php echo $lastid; ?>',
                        'duration' : timeDiff
                    }
                })
                    .done(function( data ) {
                        setTimeout( mblg_update_duration, 5000 );
                    });
            }
            mblg_update_duration();
        });
    </script>
    <?php  
}

function mblg_curl( $url ){
    $ch = curl_init();

    $options = array();

    $options[CURLOPT_URL] = $url;
    $options[CURLOPT_SSL_VERIFYHOST] = false;
    $options[CURLOPT_RETURNTRANSFER] = true;

    curl_setopt_array($ch, $options);

    $object = curl_exec( $ch );
    $status = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
    $error_no = curl_errno( $ch );
    curl_close( $ch );

    if( $error_no ){
        return false;
    }

    $object = json_decode( $object );
    return array( 'status' => $status, 'object' => $object );
}
