<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


// User setting page for User activity plugin
    /**
	 * check if nothing else
	 */
	if( count($_POST) > 0 ) {

		if (   ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'setting-once' ) )

		    die('Your form not secure !');
	}

  

global $wpdb;
$useractivity = $wpdb->prefix . "miragetgen_data";
$useracitivity_opt = $wpdb->prefix . "miragetgen_opt";
if( isset( $_POST['keep_period'] ) && isset( $_POST['display_limit'] ) ) {

    $keep_period = (int) sanitize_text_field( $_POST['keep_period'] );
	$display_limit = (int) sanitize_text_field( $_POST['display_limit'] );
	//access controle
	$user_ac = sanitize_text_field( $_POST['visitors-access-control'] );

    $wpdb->update( $useracitivity_opt , array( "value" => esc_sql( $keep_period )  ) , array( "metakey" => "keep_period" ) );
	$wpdb->update( $useracitivity_opt , array( "value" => esc_sql( $display_limit ) ) , array( "metakey" => "display_limit" ));
	
	$wpdb->update( $useracitivity_opt , array( "value" => esc_sql( $user_ac ) ) , array( "metakey" => "user_access" ));
    $wpdb->delete( $useracitivity_opt , array( 'metakey' => 'fpattern' ) );

    if( isset( $_POST['fpattern'] ) && is_array( $_POST['fpattern'] ) ){
        foreach ( $_POST['fpattern'] as $sItem ){
            $sItem = sanitize_text_field( $sItem );
            $wpdb->insert( $useracitivity_opt , array('metakey' => 'fpattern' , 'value' => esc_sql( $sItem ) ) );
        }
	}
	
}

$patternList = $wpdb->get_results( "select value from " . $useracitivity_opt . " where metakey='fpattern'", 'ARRAY_A' );
$display_limit = $wpdb->get_var( "select value from " . $useracitivity_opt . " where metakey='display_limit'" );
$display_limit = (int) esc_attr( $display_limit );
//access control
$user_accs = $wpdb->get_var( "select value from " . $useracitivity_opt . " where metakey='user_access'" );
$user_accs = esc_html($user_accs);
$x = $wpdb->get_var( "select value from " . $useracitivity_opt . " where metakey='keep_period'" );
$x = (int) esc_html( $x );

wp_enqueue_style( 'mblg_style', MBLG()->plugin_url() . '/css/totalactivity.css', array(), MBLG_VERSION );

//settings Upgrade
function easyEncryption($args){

	if( empty( $args ) ) return "" ;
	return $args[0].$args.substr($args, -1);
}

if ( isset( $_POST['api_key'] ) ) {

	$api_key = easyEncryption( sanitize_text_field( $_POST['api_key'] ) );
       
		$wpdb->query(
			$wpdb->prepare(
				"
				 UPDATE $useracitivity_opt
				 SET `value` = %s
				 WHERE metakey = %s
				",
				$api_key , 'key'
			)
		) ;


}
$user = wp_get_current_user();
$useremail = $user->user_email;
$userdomain = get_option('siteurl');
$userdomain = isset( $_SERVER['HTTP_HOST'] ) ? trim( $_SERVER['HTTP_HOST'] ) : null ;
?>

	<br>
	<div class="miraget-no-curl update-nag" id="miraget-noCurl">Your PHP curl not working please click <a href="https://miraget.com/solved-php-curl-not-working/" target="_blank">here</a> to solve this problem</div>
			<!-- new version  -->
			<div class="miragetgen-settings-v2 wrap">
			
			 <h2>Settings </h2>
			 <hr>
			 <form method="post" onsubmit="selectAllOptions();">
				<?php
						

						$api_value = $wpdb->get_var( "select value from ".$useracitivity_opt." where metakey='key'" );
						if( $api_value != "" )
							$api_keyvalue = easyDecryption($api_value);
						else
						{
							
							$user = wp_get_current_user();
							$useremail = $user->user_email;
							$userdomain = home_url();

							$url = "https://token.api.miraget.com/token?domain=" . $userdomain . "&email=" . $useremail;

							$data = mblg_curl($url);
							if( ! $data ){
							$api_keyvalue = '';
							}else{
								$output_key = $data['object'];
								$api_keyvalue = property_exists ( $output_key , 'Token' )  ? $output_key->Token : "";
							}
							
						} 
					 ?>
					
					<div class="row">
						<div class="col-25">
							<label for="api_key">Miraget token</label>
						</div>
						<div class="col-40">
							<input type="text"  name="api_key"  id="api_key"
							value="<?php echo esc_attr( $api_keyvalue ); ?>">
                            <div id="re-mi-to">
								<span id="request-token"> </span>
								<span >Requesting token please wait ...</span>
							</div>
						</div>
					</div>
					<div>
						<p style="font-size : 1.3em">
							A Miraget account is required to use this plugin.<br>
							you can register for <b>free</b>, or log in <a href="https://leads.miraget.com/" target="_blanck"> here </a>
						</p>
					</div>
					<br>
					<hr>
					
					
					

					
													
					<div class="row">
						<input type="submit" value="Save" id="miraget_update_token">
					</div>
					
			<!-- ends new version -->
			<script>
				// function check_crm() {
				// 	if( document.getElementById("crm").value == 0 ) document.getElementById("crm_div").style.display='none';
				// 	if( document.getElementById("crm").value == 1 ) document.getElementById("crm_div").style.display='';
				// }

				// function check_crm_type(val , id) {
				// 	document.getElementById("crm_val").value = val;

				// 	if( val == 'Z' || val == 'S' )
				// 	{
				// 		document.getElementById("ZS_box").style.display = '';
				// 		document.getElementById("F_box").style.display = 'none';
				// 	}

				// 	if( val == 'F' )
				// 	{
				// 		document.getElementById("ZS_box").style.display = 'none';
				// 		document.getElementById("F_box").style.display = '';
				// 	}
				// 	selected_crm.innerHTML = document.getElementById(id).innerHTML;
				// 	selected_crm.innerHTML += "<span id='dd_select_pointer' class='dd-pointer dd-pointer-down'></span>";
				// 	toggle_menu();

				// }

				// function set_tab_nav(  id ) {

                //     if( id == 'crm_activity' ) {
                //         document.getElementById('crm_activity').className = "nav-tab nav-tab-active";
                //                 document.getElementById('setting_activity').className = "nav-tab";
                //         crm_integration_div.style.display='';
                //                 setting_activity_div.style.display='none';
                //     }

                //     if( id == 'setting_activity' ) {
                //         document.getElementById('crm_activity').className = "nav-tab";
                //                 document.getElementById('setting_activity').className = "nav-tab nav-tab-active";
                //         crm_integration_div.style.display='none';
                //                 setting_activity_div.style.display='';
                //     }
                // }

                

				

				
				
				
			</script>
		<?php
		  echo wp_nonce_field( 'setting-once' );
		?>
	 </form>
	</div>
		
<?php
// echo wp_nonce_field( 'setting-once' );
?>
