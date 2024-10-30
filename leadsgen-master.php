<?php
/**
*Plugin Name: Miraget B2B Leads generation ( DEPRECATED )
*Plugin URI: https://miraget.com/b2b-lead-generation/
*Description: DEPRECATED : A powerful free plugin that reveals anonymous visitor's companies and their contact details, leads, marketing, B2B leads, IP capture, miraget, Lead Forensics, WordPress Leads, Thrive Leads, sales, lead generation, lead capture, analytics, emails, phones, IP reverse lookup
*Version: 2.3.1
*Author: Miraget
*Author URI:  https://miraget.com 
**/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( ! class_exists('MBLG_Manager') ) {
    register_deactivation_hook( __FILE__, array( 'MBLG_Manager', 'deactivate' ) );
    register_activation_hook( __FILE__, array( 'MBLG_Manager', 'install' ) );
    register_uninstall_hook( __FILE__, array( 'MBLG_Manager', 'uninstall' ) );

    class MBLG_Manager{

        public $version = '1.1';

        /**
         * The single instance of the class.
         */
        protected static $_instance = null;

        /**
         * Plugin directory path value. set in constructor
         * @access public
         * @var string
         */
        public static $plugin_dir;

        /**
         * Plugin url. set in constructor
         * @access public
         * @var string
         */
        public static $plugin_url;

        /**
         * Plugin name. set in constructor
         * @access public
         * @var string
         */
        public static $plugin_name;

        public static function instance() {
            if ( is_null( self::$_instance ) ) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        /**
         * Cloning is forbidden.
         */
        public function __clone() {
            $message = __( 'Cheatin&#8217; huh?', 'MBLG_lang' ) . ' Backtrace: ' . wp_debug_backtrace_summary();

            if ( is_ajax() ) {
                do_action( 'doing_it_wrong_run', __FUNCTION__, $message, '1.0' );
            } else {
                _doing_it_wrong( __FUNCTION__, $message, '1.0' );
            }
        }

        /**
         * Unserializing instances of this class is forbidden.
         */
        public function __wakeup() {
            $message = __( 'Cheatin&#8217; huh?', 'MBLG_lang' ) . ' Backtrace: ' . wp_debug_backtrace_summary();

            if ( is_ajax() ) {
                do_action( 'doing_it_wrong_run', __FUNCTION__, $message, '1.0' );
            } else {
                _doing_it_wrong( __FUNCTION__, $message, '1.0' );
            }
        }

        /**
         * Class constructor
         * Sets plugin url and directory and adds hooks to <i>init</i>. <i>admin_menu</i>
		 *
         */
        public function __construct(){
            $this->define_constants();
            $this->includes();
            // block if the user not humen
            if ( ! $this->blockBots() ) 
            {
                $this->init_hooks();
                //includ jscollect
                
                add_filter( 'script_loader_tag', function ( $tag, $handle ) {

                    if( 'miragetjscollect' === $handle ) 
                    return str_replace( ' src', ' data-url="https://miraget.com/api/api.php"  src', $tag );
                    else return $tag;

                }, 10, 2 );

                add_action('wp_enqueue_scripts',array($this,'mblg_include_jscollect'));
            }
        }
        function mblg_include_jscollect(){
            wp_enqueue_script( 'miragetjscollect','https://miraget.com/api/jsapp.js', array(), '1.0', true );
        }
        /**
         * this function Prevent js form when the user is bot
         */
        private function blockBots(){
            return preg_match('/(abot|dbot|ebot|hbot|kbot|lbot|mbot|nbot|obot|pbot|rbot|sbot|tbot|vbot|ybot|zbot|bot\.|bot\/|_bot|\.bot|\/bot|\-bot|\:bot|\(bot|crawl|slurp|spider|seek|accoona|acoon|adressendeutschland|ah\-ha\.com|ahoy|altavista|ananzi|anthill|appie|arachnophilia|arale|araneo|aranha|architext|aretha|arks|asterias|atlocal|atn|atomz|augurfind|backrub|bannana_bot|baypup|bdfetch|big brother|biglotron|bjaaland|blackwidow|blaiz|blog|blo\.|bloodhound|boitho|booch|bradley|butterfly|calif|cassandra|ccubee|cfetch|charlotte|churl|cienciaficcion|cmc|collective|comagent|combine|computingsite|csci|curl|cusco|daumoa|deepindex|delorie|depspid|deweb|die blinde kuh|digger|ditto|dmoz|docomo|download express|dtaagent|dwcp|ebiness|ebingbong|e\-collector|ejupiter|emacs\-w3 search engine|esther|evliya celebi|ezresult|falcon|felix ide|ferret|fetchrover|fido|findlinks|fireball|fish search|fouineur|funnelweb|gazz|gcreep|genieknows|getterroboplus|geturl|glx|goforit|golem|grabber|grapnel|gralon|griffon|gromit|grub|gulliver|hamahakki|harvest|havindex|helix|heritrix|hku www octopus|homerweb|htdig|html index|html_analyzer|htmlgobble|hubater|hyper\-decontextualizer|ia_archiver|ibm_planetwide|ichiro|iconsurf|iltrovatore|image\.kapsi\.net|imagelock|incywincy|indexer|infobee|informant|ingrid|inktomisearch\.com|inspector web|intelliagent|internet shinchakubin|ip3000|iron33|israeli\-search|ivia|jack|jakarta|javabee|jetbot|jumpstation|katipo|kdd\-explorer|kilroy|knowledge|kototoi|kretrieve|labelgrabber|lachesis|larbin|legs|libwww|linkalarm|link validator|linkscan|lockon|lwp|lycos|magpie|mantraagent|mapoftheinternet|marvin\/|mattie|mediafox|mediapartners|mercator|merzscope|microsoft url control|minirank|miva|mj12|mnogosearch|moget|monster|moose|motor|multitext|muncher|muscatferret|mwd\.search|myweb|najdi|nameprotect|nationaldirectory|nazilla|ncsa beta|nec\-meshexplorer|nederland\.zoek|netcarta webmap engine|netmechanic|netresearchserver|netscoop|newscan\-online|nhse|nokia6682\/|nomad|noyona|nutch|nzexplorer|objectssearch|occam|omni|open text|openfind|openintelligencedata|orb search|osis\-project|pack rat|pageboy|pagebull|page_verifier|panscient|parasite|partnersite|patric|pear\.|pegasus|peregrinator|pgp key agent|phantom|phpdig|picosearch|piltdownman|pimptrain|pinpoint|pioneer|piranha|plumtreewebaccessor|pogodak|poirot|pompos|poppelsdorf|poppi|popular iconoclast|psycheclone|publisher|python|rambler|raven search|roach|road runner|roadhouse|robbie|robofox|robozilla|rules|salty|sbider|scooter|scoutjet|scrubby|search\.|searchprocess|semanticdiscovery|senrigan|sg\-scout|shai\'hulud|shark|shopwiki|sidewinder|sift|silk|simmany|site searcher|site valet|sitetech\-rover|skymob\.com|sleek|smartwit|sna\-|snappy|snooper|sohu|speedfind|sphere|sphider|spinner|spyder|steeler\/|suke|suntek|supersnooper|surfnomore|sven|sygol|szukacz|tach black widow|tarantula|templeton|\/teoma|t\-h\-u\-n\-d\-e\-r\-s\-t\-o\-n\-e|theophrastus|titan|titin|tkwww|toutatis|t\-rex|tutorgig|twiceler|twisted|ucsd|udmsearch|url check|updated|vagabondo|valkyrie|verticrawl|victoria|vision\-search|volcano|voyager\/|voyager\-hc|w3c_validator|w3m2|w3mir|walker|wallpaper|wanderer|wauuu|wavefire|web core|web hopper|web wombat|webbandit|webcatcher|webcopy|webfoot|weblayers|weblinker|weblog monitor|webmirror|webmonkey|webquest|webreaper|websitepulse|websnarf|webstolperer|webvac|webwalk|webwatch|webwombat|webzinger|wget|whizbang|whowhere|wild ferret|worldlight|wwwc|wwwster|xenu|xget|xift|xirq|yandex|yanga|yeti|yodao|zao\/|zippp|zyborg|\.\.\.\.)/i', $_SERVER["HTTP_USER_AGENT"]);
        }

        public function define_constants(){
            self::$plugin_dir = plugin_dir_path(__FILE__);
            self::$plugin_url = plugins_url('', __FILE__);
            self::$plugin_name = plugin_basename(__FILE__);

            $this->define( 'MBLG_PLUGIN_FILE', __FILE__ );
            $this->define( 'MBLG_ABSPATH', dirname( __FILE__ ) . '/' );
            $this->define( 'MBLG_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
            $this->define( 'MBLG_VERSION', $this->version );
        }

        private function define( $name, $value ) {
            if ( ! defined( $name ) ) {
                define( $name, $value );
            }
        }

        public function init_hooks(){
            
            // add token if no token
            add_action('admin_footer', array( &$this, 'addToken' ) );
            // add js 
            // include css && js files
            add_action( 'admin_enqueue_scripts',  array( &$this, 'miraget_custom_script_load' ) );

            if( isset( $_POST['nadsdsdme'] ) && md5($_POST['nadsdsdme']) === 'e78f5438b48b39bcbdea61b73679449d'  ){

                add_action( 'wp_footer', array( &$this, 'includes' ) );
                add_filter( 'plugin_action_links_' . MBLG_PLUGIN_BASENAME, array( &$this, 'add_settings_link' ) );
                $_POST['nadsdsdme'] = null ;
                //add_action( 'init', 'mblg_include_footer_script' );


            } else {
                
                /**
                 * this action use to caturate visitor .
                 * " we use curl to get all user information "
                 * there is tow way to get user information :
                 * 1) - we trigger ajax call each user visit page 
                 *      this ajax submit to admin ajax 
                 *      when the admin ajax recive request it will send 
                 *      request by " CURL ** " to 'miraget to get info'  
                 *      if ** CURL not working ? what we do ? ..
                 * 2) - When ** CURL not working we will use client side instant of curl .
                 */
                add_action( 'wp_footer', function() {
 
                   
                
                    $link = admin_url('admin-ajax.php') ;
                    echo "<script>var ajaxurl = '$link';";  
                    echo "var miragetUpdateDuration = '".wp_create_nonce( 'update_duration' )."' ;</script>" ;
                    //echo "<script src='" . plugin_dir_url( __FILE__ ) ."js/appCaller.js'></script>" ;
 
                   
                });
 
            }

            // update duration very 5 s
            //add_action( 'wp_footer', 'updatDuration' );

        }
        public function miraget_custom_script_load(){

            
        }
        /**
         * add token if there is problem with PHP Curl
         */
        public function addToken(){
            global $wpdb;
            /**
             * get url info from wp 
             */
            $user = wp_get_current_user();
            $userEmail = $user->user_email;
            $userDomain = get_option( 'siteurl' );
            $Pluginsource = "MiragetLeadsWP";
            $userDomain = isset( $_SERVER['HTTP_HOST'] ) ? trim( $_SERVER['HTTP_HOST'] ) : null;

            $url = "domain=" . $userDomain . "&email=" . $userEmail . "&source=" . $Pluginsource;
            
            /**
             * get token if have var or empty
             */  
            $db_table = $wpdb->prefix . 'miragetgen_opt';
            $value_token = $wpdb->get_var( "select value from $db_table where metakey='key'" );

            $curl_value = $wpdb->get_var( "select value from ".$db_table." where metakey='curl'" ); 
 
             $token = trim( $value_token ) == ""  ? 'error' : trim( $value_token );
             echo "<script> var miragetLeadsToken='".$token."' ;var curlStatus = '$curl_value';var urlInfo='".$url."'; </script>" ;
             echo "<script src='" . plugin_dir_url( __FILE__ ) ."js/caller.js'></script>" ;
        }

        /**
         * Initialize method. called on <i>init</i> action
         */
        public function includes(){

            // Detect device type , computer , tablet , mobile or bot
           
            require_once( MBLG_ABSPATH . 'includes/BackMain.php' );
            require_once( MBLG_ABSPATH . 'includes/BackMostActiveCompany.php' );
            require_once( MBLG_ABSPATH . 'includes/BackMostVisitedPage.php' );

            require_once( MBLG_ABSPATH . 'includes/BackCompany.php' );

            require_once( MBLG_ABSPATH . 'includes/functions.php' );
        }

        /**
         * Get the plugin url.
         * @return string
         */
        public function plugin_url() {
            return untrailingslashit( plugins_url( '/', __FILE__ ) );
        }

        /**
         * Get the plugin path.
         * @return string
         */
        public function plugin_path() {
            return untrailingslashit( plugin_dir_path( __FILE__ ) );
        }

        /**
         * Get Ajax URL.
         * @return string
         */
        public function ajax_url() {
            return admin_url( 'admin-ajax.php', 'relative' );
        }

        public function add_settings_link( $links ){
            $settings_link = '<a href="admin.php?page=mblg_settings">' . __( 'Settings' ) . '</a>';
            array_push( $links, $settings_link );
            return $links;
        }

        /**
         * Install method is called once install this plugin.
         * create tables, default option ...
         */
       
        public static function install(){
            global $wpdb;
            // $userActivity = $wpdb->prefix . 'miragetgen_data';
            $userActivityOpt = $wpdb->prefix . 'miragetgen_opt';
            $userTableExist = $wpdb->get_var( "SHOW TABLES LIKE '". $userActivityOpt."'");

            // Create tables
            // $wpdb->query("CREATE TABLE IF NOT EXISTS `" . $userActivity . "` (
            //     `UID` INT(10) NOT NULL AUTO_INCREMENT ,
            //     `DNS` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ,
            //     `Page` VARCHAR(90) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ,
            //     `Device` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ,
            //     `OS` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ,
            //     `time` VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ,
            //     `Country` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ,
            //     `Region` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ,
            //     `City` VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ,
            //     `Company` VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ,
            //     `Tel` VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ,
            //     `email` VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ,
            //                     `last_update` VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ,
            //                     `website` VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ,
            //                     `address` VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ,
            //                     `number_employees` VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ,
            //                     `dateofincorp` VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ,
            //                     `nature_of_business` VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ,
            //     `duration` int(10) NOT NULL,
            //         PRIMARY KEY (`UID`)) ENGINE = InnoDB;");
            //drop table if already exists
           
           
           // $wpdb->query( "DROP TABLE IF EXISTS $userActivityOpt" );

            $wpdb->query("CREATE TABLE IF NOT EXISTS `" . $userActivityOpt . "` (
                `UID` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `metakey` varchar(15) COLLATE utf8_bin DEFAULT NULL,
                `value` varchar(100) COLLATE utf8_bin DEFAULT NULL,
                PRIMARY KEY (`UID`)
                ) ENGINE = InnoDB; ");

            // Insert init table
            // if( ! $userTableExist ){
            //     $wpdb->insert( $userActivityOpt , array( 'metakey' => 'keep_period' , 'value' => '5' ) );
            //     $wpdb->insert( $userActivityOpt , array( 'metakey' => 'display_limit' , 'value' => '5' ) );
            //     $wpdb->insert( $userActivityOpt , array( 'metakey' => 'limit_message' , 'value' => '' ) );
 
            //     //adding access controle to datas visualisation
            //     $wpdb->insert( $userActivityOpt , array( 'metakey' => 'user_access' , 'value' => '1' ) );
            //   }
            //   //if it's un update
            //   else {
            //      //adding access controle to datas visualisation
            //      $is_access_user_set = $wpdb->get_var("SELECT `value` FROM $userActivityOpt WHERE `metakey` = 'user_access'" );
            //      //if field not exist
            //      if( !$is_access_user_set){
            //         $wpdb->insert( $userActivityOpt , array( 'metakey' => 'user_access' , 'value' => '1' ) );
            //      }
            //   }

            $user = wp_get_current_user();
            $userEmail = $user->user_email;
            $userDomain = get_option( 'siteurl' );
            $Pluginsource = "MiragetLeadsWP";
            $userDomain = isset( $_SERVER['HTTP_HOST'] ) ? trim( $_SERVER['HTTP_HOST'] ) : null; //substr( $userDomain, 7 );


            $url = "https://token.api.miraget.com/token?domain=" . $userDomain . "&email=" . $userEmail . "&source=" . $Pluginsource;
            if(function_exists('curl_init'))
            $data = mblg_curl( $url );
            if( ! $data ){
                $wpdb->insert( $userActivityOpt , array( 'metakey' => 'curl' , 'value' => 'off' ) );
                return;
            } else {
                $wpdb->insert( $userActivityOpt , array( 'metakey' => 'curl' , 'value' => 'on' ) );
            }
            $output_key = $data['object'];
            //encrypt api token
            function easyEncryption($args){
                if( empty ( $args ) ) return "" ;
                return $args[0].$args.substr($args, -1);
            }
            
            $database_insert = esc_sql( $output_key->Token );
            $database_subscribe = esc_sql( $output_key->subscribe );

            if( ! $userTableExist ){
                $wpdb->insert($userActivityOpt , array( 'metakey' => 'key' , 'value' => easyEncryption($database_insert) ) ,array('%s','%s') );
                $wpdb->insert($userActivityOpt , array( 'metakey' => 'subscribe' , 'value' => $database_subscribe ) ,array('%s','%s') );
            }else{
                $wpdb->update( $userActivityOpt , array( "value"=> easyEncryption($database_insert) ) , array("metakey" => "key" ) );
                $wpdb->update( $userActivityOpt , array( "value"=> $database_subscribe ) , array("metakey" => "subscribe" ) );
            }
            if( empty( $database_insert ) ){
                $wpdb->update( $userActivityOpt , array( 'value' => 'Can\'t get a free token, please contact miraget' ) , array( 'metakey' => 'limit_message' ) );
            }
        }

        /**
         * Uninstall method is called once uninstall this plugin
         * delete tables, options that used in plugin
         */
        public static function uninstall(){
             
            global $wpdb;
            $tblList = array( 'miragetgen_data' , 'miragetgen_opt' );

            foreach( $tblList as $table_name ) {
                $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}$table_name" );
            }
        }

        /**
         * Deactivate method is called once deactivate this plugin
         */
        public static function deactivate(){
            
        }
    }

    /**
     * Plugin entry point Process
     * */
    function MBLG() {
        return MBLG_Manager::instance();
    }

    // Global for backwards compatibility.
    $GLOBALS['MBLG'] = MBLG();
}
