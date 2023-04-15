<?php
/**
 * List tables: FAQ.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * WC_Admin_List_Table_Coupons Class.
 */
class FAQ_Admin_List_Table extends WP_List_Table {

	public function __construct() {
        $screen = get_current_screen();
        parent::__construct( array(
			'singular' => 'faq_list',      			// Singular name of the listed records.
			'plural'   => 'faqs_list', 				// Plural name of the listed records.
			//'ajax'     => false,                   	// Does this list table support AJAX?
			'screen'   => $screen,                 	// WP_Screen object.
		) );

		$this->action_handlers();
    }

    /**
     * [REQUIRED] this is a default column renderer
     *
     * @param $item - row (key, value array)
     * @param $column_name - string (key)
     * @return HTML
     */
    public function column_default($item, $column_name) {
        return $item[$column_name];
    }

    /**
     * [OPTIONAL] this is example, how to render specific column
     *
     * method name must be like this: "column_[column_name]"
     *
     * @param $item - row (key, value array)
     * @return HTML
     */
    public function column_faq_id($item) {
        //return $item['id'];
        $actions = array(
            'copy-shortcode' => sprintf('<a href="javascript:void(0);" class="copy-faq-shortcode" title="%s" data-text_copied="%s">[faq id="%s"]</a><span class="copied-faq-shortcode"></span>', __('Click to copy shortcode', 'page-utils'), __('Copied to clipboard', 'page-utils'), $item['id'], __('Edit', 'page-utils')),
        );
        return sprintf('%s %s',
            $item['id'],
            $this->row_actions($actions)
        );
    }

    /**
     * [OPTIONAL] this is example, how to render column with actions,
     * when you hover row "Edit | Delete" links showed
     *
     * @param $item - row (key, value array)
     * @return HTML
     */
    public function column_faq_name($item) {
        // links going to /admin.php?page=[your_plugin_page][&other_params]
        // notice how we used $_REQUEST['page'], so action will be done on curren page
        // also notice how we use $this->_args['singular'] so in this example it will
        // be something like &person=2
        $actions = array(
            'edit' => sprintf('<a href="?page=faq&id=%s">%s</a>', $item['id'], __('Edit', 'page-utils')),
            'delete' => sprintf('<a href="?page=faq&action=delete-faq&id=%s">%s</a>', $item['id'], __('Delete', 'page-utils')),
        );
        return sprintf('%s %s',
            $item['faq_name'],
            $this->row_actions($actions)
        );
    }

    /**
     * [OPTIONAL] this is example, how to render specific column
     *
     * method name must be like this: "column_[column_name]"
     *
     * @param $item - row (key, value array)
     * @return HTML
     */
    public function column_faq_action($item) {
        return sprintf('<a href="?page=faq-qna&faq_id=%s">%s</a>', $item['id'], __('Questions & Answers', 'page-utils'));
    }

    /**
     * [REQUIRED] this is how checkbox column renders
     *
     * @param $item - row (key, value array)
     * @return HTML
     */
    public function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="faq_ids[]" value="%s" />',
            $item['id']
        );
    }

    /**
     * [REQUIRED] This method return columns to display in table
     * you can skip columns that you do not want to show
     * like content, or description
     *
     * @return array
     */
    public function get_columns() {
        $columns = array(
            'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
            'faq_id' => __('ID', 'page-utils'),
            'faq_name' => __('Name', 'page-utils'),
            'faq_action' => __('Action', 'page-utils'),
        );
        return $columns;
    }

    /**
     * [OPTIONAL] This method return columns that may be used to sort table
     * all strings in array - is column names
     * notice that true on name column means that its default sort
     *
     * @return array
     */
    public function get_sortable_columns() {
        $sortable_columns = array(
            'faq_id' => array('id', true),
        );
        return $sortable_columns;
    }

    /**
     * [OPTIONAL] Return array of bult actions if has any
     *
     * @return array
     */
    public function get_bulk_actions() {
        $actions = array(
            'bulk-delete-faq' => __('Delete', 'page-utils' )
        );
        return $actions;
    }

    /**
     * [OPTIONAL] This method processes bulk actions
     * it can be outside of class
     * it can not use wp_redirect coz there is output already
     * in this example we are processing delete action
     * message about successful deletion will be shown on page in next part
     */
    public function process_bulk_action() {

    }

    /**
     * [REQUIRED] This is the most important method
     *
     * It will get rows from database and prepare them to be showed in table
     */
    public function prepare_items() {
        global $wpdb;

        $per_page = 10; // constant, how much records will be shown per page
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        // here we configure table headers, defined in our methods
        $this->_column_headers = array( $columns, $hidden, $sortable );

        // [OPTIONAL] process bulk action if any
        $this->process_bulk_action();

        // will be used in pagination settings
        $total_items = PU()->query->get_total_faq_items();

        // prepare query params, as usual current page, order by and order direction
        $paged = isset( $_REQUEST['paged'] ) ? max( 0, intval( $_REQUEST['paged'] - 1 ) * $per_page ) : 0;
        $orderby = ( isset( $_REQUEST['orderby'] ) && in_array( $_REQUEST['orderby'], array_keys( $this->get_sortable_columns() ) ) ) ? $_REQUEST['orderby'] : 'id';
        $order = ( isset( $_REQUEST['order'] ) && in_array( $_REQUEST['order'], array( 'asc', 'desc' ) ) ) ? $_REQUEST['order'] : 'asc';

        // [REQUIRED] define $items array
        // notice that last argument is ARRAY_A, so we will retrieve array
        $search_keyword = ( isset( $_REQUEST['s'] ) && !empty( $_REQUEST['s'] ) ) ? sanitize_text_field( $_REQUEST['s'] ) : '';
        $sql = '';
        if( $search_keyword ){
        	$sql .= "WHERE faq_name LIKE '%{$search_keyword}%' ";
        }
        $sql .= $wpdb->prepare( "ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $paged );
        $this->items = PU()->query->get_all_faq( $sql, ARRAY_A );

        // [REQUIRED] configure pagination
        $this->set_pagination_args( array(
            'total_items' 	=> $total_items, // total items defined above
            'per_page' 		=> $per_page, // per page constant defined at top of method
            'total_pages' 	=> ceil( $total_items / $per_page ) // calculate pages count
        ) );
    }

    public function action_handlers(){
    	$faq_page_url = admin_url(). 'admin.php?page=faq';
    	
    	$action = ( isset( $_REQUEST['action'] ) ) ? $_REQUEST['action'] : '';
    	// if this fails, check_admin_referer() will automatically print a "failed" page and die.
		if ( isset( $_POST['_wpnonce_add-faq'] ) && wp_verify_nonce( $_POST['_wpnonce_add-faq'], 'add-faq' ) ) {

		   	$faq_name = ( isset( $_POST['faq-name'] ) && !empty( $_POST['faq-name'] ) ) ? $_POST['faq-name'] : '';
		   
		   	if( $action == 'add-faq' && $faq_name ){
		   		$data = array(
		   			'faq_name' => $faq_name
		   		);

		   		$result = PU()->query->insert_faq( $data );
		   		if( $result ) {
		   			$location = add_query_arg( 'message', 1, $faq_page_url );
		   		}else{
		   			$location = add_query_arg(
						array(
							'error'   => true,
							'message' => 1,
						),
						$faq_page_url
					);
		   		}
		   		wp_redirect( $location );
				exit;
		   	}
		}elseif ( isset( $_POST['_wpnonce_edit-faq'] ) && wp_verify_nonce( $_POST['_wpnonce_edit-faq'], 'edit-faq' ) ) { 
		   	
		   	$faq_id = ( isset( $_POST['faq-id'] ) ) ? absint( $_POST['faq-id'] ) : 0;
		   	$faq_name = ( isset( $_POST['faq-name'] ) && !empty( $_POST['faq-name'] ) ) ? $_POST['faq-name'] : '';
		   	
		   	if( $action == 'edit-faq' && $faq_name ){
		   		$data = array(
		   			'faq_name' => $faq_name
		   		);

		   		$result = PU()->query->update_faq( $data, $faq_id );
		   		if( $result ) {
		   			$location = add_query_arg( 'message', 2, $faq_page_url );
		   		}else{
		   			$location = add_query_arg(
						array(
							'error'   => true,
							'message' => 2,
						),
						$faq_page_url
					);
		   		}
		   		wp_redirect( $location );
				exit;
		   	}
		}elseif ( isset( $_POST['_wpnonce_list-faq'] ) && wp_verify_nonce( $_POST['_wpnonce_list-faq'], 'list-faq' ) ) { 
			$faq_ids = ( isset( $_POST['faq_ids'] ) && !empty( $_POST['faq_ids'] ) ) ? $_POST['faq_ids'] : array();
			if( $action == 'bulk-delete-faq' && $faq_ids ){
				$result = '';
				foreach ( $faq_ids as $id ) {
					$result = PU()->query->delete_faq( $id );
				}

				if( $result ) {
		   			$location = add_query_arg( 'message', 3, $faq_page_url );
		   		}else{
		   			$location = add_query_arg(
						array(
							'error'   => true,
							'message' => 3,
						),
						$faq_page_url
					);
		   		}
		   		wp_redirect( $location );
				exit;
			}
		   	
		}elseif( $action == 'delete-faq'){
			$faq_id = ( isset( $_REQUEST['id'] ) ) ? absint( $_REQUEST['id'] ) : 0;
			$result = PU()->query->delete_faq( $faq_id );
	   		if( $result ) {
	   			$location = add_query_arg( 'message', 3, $faq_page_url );
	   		}else{
	   			$location = add_query_arg(
					array(
						'error'   => true,
						'message' => 3,
					),
					$faq_page_url
				);
	   		}
	   		wp_redirect( $location );
			exit;
		}
    }

    
}