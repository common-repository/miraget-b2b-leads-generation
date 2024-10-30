<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class MBLG_Company_List_Table extends WP_List_Table {


    function __construct(){
        global $status, $page;

        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'movie',     //singular name of the listed records
            'plural'    => 'movies',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
    }

    public static function get_history( $per_page = 25, $page_number = 1 ) {

        global $wpdb;
        $company = isset( $_GET['company'] ) ? sanitize_text_field( $_GET['company'] ) : '';
        $useractivity = $wpdb->prefix . "miragetgen_data";

        $sql = "
        select * from " . $useractivity . "
        where company='" . $company . "'
        order by UID desc
        ";


        $sql .= " LIMIT $per_page";
        $sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;


        $result = $wpdb->get_results( $sql, 'ARRAY_A' );

        return $result;
    }

    public static function row_count() {

        global $wpdb;
        $company = isset( $_GET['company'] ) ? sanitize_text_field( $_GET['company'] ) : '';
        $useractivity = $wpdb->prefix . "miragetgen_data";
        $sql = "
        select count(*) from ".$useractivity."
        where company='".$company."'
        ";

        return $wpdb->get_var( $sql );
    }

    function column_default($item, $column_name){
        switch($column_name){
            case 'Page':
            case 'time':
            case 'Country':
            return $item[$column_name];
            case 'duration':
            return $item[$column_name] + rand(1,5);
            default:
            return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }

    function get_columns(){
        $columns = array(
            'time'     => 'Time',
            'Page'    => 'Page',
            'Country'  => 'Country',
            'duration'    => 'Duration'
        );
        return $columns;
    }

    function prepare_items() {
        global $wpdb;
        $per_page = 20;
        $columns = $this->get_columns();

        $this->_column_headers = array($columns );
        $current_page = $this->get_pagenum();

        $this->items = $this->get_history( $per_page , $current_page );
        $total_items = $this->row_count();

        /**
         */
        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items/$per_page)
        ) );

    }
}