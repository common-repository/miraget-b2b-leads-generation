<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class MBLG_ActiveCompany_List_Table extends WP_List_Table {

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
        $useractivity = $wpdb->prefix . "miragetgen_data";

		$sql = "
		  select * from
            (
                select * , count(*) as rank from " . $useractivity . "
                group by Company
            ) as tbl
            order by tbl.rank desc
		";

		$sql .= " LIMIT $per_page";
		$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;


		$result = $wpdb->get_results( $sql, 'ARRAY_A' );

		return $result;
	}

	public static function row_count() {

		global $wpdb;
        $useractivity = $wpdb->prefix."miragetgen_data";

		$sql = "
		  select count(*) from
            (
                select * , count(*) as rank from " . $useractivity . "
                group by Company
            ) as tbl
		";

		return $wpdb->get_var( $sql );
	}

	function column_default( $item, $column_name ){
		switch( $column_name ){
			// case 'Page':
			// case 'time':
			case 'Country':
			case 'Company':
            case 'rank':
			    return esc_html( $item[$column_name] );
			default:
			    return print_r( $item,true ); //Show the whole array for troubleshooting purposes
		}
	}

    function get_columns(){
        $columns = array(
            // 'time'     => 'Time',
            // 'Page'    => 'Page',
            'Country'  => 'Country',
            'Company'    => 'Company',
            'rank'  => 'Visit Count'
        );
        return $columns;
    }

    function prepare_items() {
        $per_page = 20;
        $columns = $this->get_columns();

        $this->_column_headers = array($columns );
        $current_page = $this->get_pagenum();
        $this->items = $this->get_history( $per_page , $current_page );
        $total_items = $this->row_count();

        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items/$per_page)
        ) );

    }
}