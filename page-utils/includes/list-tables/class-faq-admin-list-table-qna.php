<?php
/**
 * List tables: FAQ QNA.
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
class FAQ_Admin_List_Table_QNA extends WP_List_Table {

	public function __construct() {
        $screen = get_current_screen();
        parent::__construct( array(
			'singular' => 'faq_qna_list',      			// Singular name of the listed records.
			'plural'   => 'faqs_qnas_list', 				// Plural name of the listed records.
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
        return $item['faq_id'];
    }

    /**
     * [OPTIONAL] this is example, how to render column with actions,
     * when you hover row "Edit | Delete" links showed
     *
     * @param $item - row (key, value array)
     * @return HTML
     */
    public function column_faq_question($item) {
        // links going to /admin.php?page=[your_plugin_page][&other_params]
        // notice how we used $_REQUEST['page'], so action will be done on curren page
        // also notice how we use $this->_args['singular'] so in this example it will
        // be something like &person=2
        $actions = array(
            'edit' => sprintf('<a href="?page=faq-qna&faq_id=%s&qna_id=%s">%s</a>', $item['faq_id'], $item['id'], __('Edit', 'faq')),
            'delete' => sprintf('<a href="?page=faq-qna&action=delete-faq-qna&faq_id=%s&qna_id=%s">%s</a>', $item['faq_id'], $item['id'], __('Delete', 'faq')),
        );
        return sprintf('%s %s',
            $item['faq_question'],
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
    public function column_faq_answer($item) {
        return $item['faq_answer'];
    }

    /**
     * [OPTIONAL] this is example, how to render specific column
     *
     * method name must be like this: "column_[column_name]"
     *
     * @param $item - row (key, value array)
     * @return HTML
     */
    public function column_faq_order($item) {
        return $item['faq_order'];
    }

    /**
     * [REQUIRED] this is how checkbox column renders
     *
     * @param $item - row (key, value array)
     * @return HTML
     */
    public function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="faq_qna_ids[]" value="%s" />',
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
            'faq_id' => __('FAQ ID', 'faq'),
            'faq_question' => __('Question', 'faq'),
            'faq_answer' => __('Answer', 'faq'),
            'faq_order' => __('Order', 'faq'),
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
            'faq_order' => array('faq_order', true),
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
            'bulk-delete-faq-qna' => __( 'Delete', 'faq' )
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
        $faq_id = ( isset($_REQUEST['faq_id']) ) ? absint( $_REQUEST['faq_id'] ) : 0;

        // will be used in pagination settings
        $total_items = PU()->query->get_total_qna_items( "WHERE faq_id=$faq_id" );

        // prepare query params, as usual current page, order by and order direction
        $paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged'] - 1) * $per_page) : 0;
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'faq_order';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'asc';

        // [REQUIRED] define $items array
        // notice that last argument is ARRAY_A, so we will retrieve array
        $search_keyword = ( isset( $_REQUEST['s'] ) && !empty( $_REQUEST['s'] ) ) ? sanitize_text_field( $_REQUEST['s'] ) : '';
        $sql = '';
        if( $faq_id ){
            $sql .= "WHERE faq_id=$faq_id ";
        }
        if( $search_keyword ){
            $sql .= "AND (faq_question LIKE '%{$search_keyword}%' OR faq_answer LIKE '%{$search_keyword}%') ";
        }
        $sql .= $wpdb->prepare( "ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $paged );
        $this->items = PU()->query->get_all_qna( $sql, ARRAY_A );
        // $this->items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_faq_question_answer WHERE faq_id=$faq_id ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $paged), ARRAY_A);

        // [REQUIRED] configure pagination
        $this->set_pagination_args( array(
            'total_items'   => $total_items, // total items defined above
            'per_page'      => $per_page, // per page constant defined at top of method
            'total_pages'   => ceil( $total_items / $per_page ) // calculate pages count
        ) );
    }

    public function action_handlers(){
        global $wpdb;

    	$faq_qna_page_url = admin_url(). 'admin.php?page=faq-qna&faq_id='.$_REQUEST['faq_id'];
        $action = ( isset( $_REQUEST['action'] ) ) ? $_REQUEST['action'] : '';
    	// if this fails, check_admin_referer() will automatically print a "failed" page and die.
		if ( isset( $_POST['_wpnonce_add-faq-qna'] ) && wp_verify_nonce( $_POST['_wpnonce_add-faq-qna'], 'add-faq-qna' ) ) { 
		   	
            $faq_id = ( isset( $_POST['faq-id'] ) ) ? absint( $_POST['faq-id'] ) : 0;
		   	$faq_question = ( isset( $_POST['faq-question'] ) && !empty( $_POST['faq-question'] ) ) ? stripslashes( $_POST['faq-question'] ) : '';
            $faq_answer = ( isset( $_POST['faq-answer'] ) && !empty( $_POST['faq-answer'] ) ) ? stripslashes( $_POST['faq-answer'] ) : '';	

		   	if( $action == 'add-faq-qna' && $faq_id && $faq_question ){
		   		$data = array(
                    'faq_id'        => $faq_id,
		   			'faq_question'  => $faq_question,
                    'faq_answer'    => $faq_answer,
		   		);

		   		$result = PU()->query->insert_faq_qna( $data );
		   		if( $result ) {
		   			$location = add_query_arg( 'message', 4, $faq_qna_page_url );
		   		}else{
		   			$location = add_query_arg(
						array(
							'error'   => true,
							'message' => 4,
						),
						$faq_qna_page_url
					);
		   		}
		   		wp_redirect( $location );
				exit;
		   	}
		}elseif ( isset( $_POST['_wpnonce_edit-faq-qna'] ) && wp_verify_nonce( $_POST['_wpnonce_edit-faq-qna'], 'edit-faq-qna' ) ) { 
		   	
            $qna_id = ( isset( $_POST['qna-id'] ) ) ? absint( $_POST['qna-id'] ) : 0;
		   	$faq_id = ( isset( $_POST['faq-id'] ) ) ? absint( $_POST['faq-id'] ) : 0;
		   	$faq_question = ( isset( $_POST['faq-question'] ) && !empty( $_POST['faq-question'] ) ) ? stripslashes( $_POST['faq-question'] ) : '';
            $faq_answer = ( isset( $_POST['faq-answer'] ) && !empty( $_POST['faq-answer'] ) ) ? stripslashes( $_POST['faq-answer'] ) : '';
            $faq_order = ( isset( $_POST['faq-order'] ) && !empty( $_POST['faq-order'] ) ) ? $_POST['faq-order'] : '';
		   	
		   	if( $action == 'edit-faq-qna' && $faq_id && $faq_question ){
		   		$data = array(
                    'faq_id'        => $faq_id,
                    'faq_question'  => $faq_question,
                    'faq_answer'    => $faq_answer,
                );
                $table_faq_question_answer = $wpdb->prefix . 'faq_question_answer';
                $sql = "WHERE faq_id=$faq_id AND faq_order=$faq_order";
                $check_faq_order = PU()->query->get_faq_qna( '', $sql );
                if( !$check_faq_order ){
                    $data['faq_order'] = $faq_order;
                }else{
                    $actual_qna_data = PU()->query->get_faq_qna( $qna_id );
                    PU()->query->update_faq_qna( array( 'faq_order' => $actual_qna_data->faq_order ), $check_faq_order->id );
                    $data['faq_order'] = $faq_order;
                }
		   		$result = PU()->query->update_faq_qna( $data, $qna_id );
		   		if( $result ) {
		   			$location = add_query_arg( 'message', 5, $faq_qna_page_url );
		   		}else{
		   			$location = add_query_arg(
						array(
							'error'   => true,
							'message' => 5,
						),
						$faq_qna_page_url
					);
		   		}
		   		wp_redirect( $location );
				exit;
		   	}
		}elseif ( isset( $_POST['_wpnonce_list-faq-qna'] ) && wp_verify_nonce( $_POST['_wpnonce_list-faq-qna'], 'list-faq-qna' ) ) { 

            $faq_qna_ids = ( isset( $_POST['faq_qna_ids'] ) && !empty( $_POST['faq_qna_ids'] ) ) ? $_POST['faq_qna_ids'] : array();
            if( ( $action == 'bulk-delete-faq-qna' || isset( $_REQUEST['action2'] ) && $_REQUEST['action2'] == 'bulk-delete-faq-qna' ) && $faq_qna_ids ){
                $result = '';
                foreach ( $faq_qna_ids as $id ) {
                    $result = PU()->query->delete_faq_qna( $id );
                }

                if( $result ) {
                    $location = add_query_arg( 'message', 6, $faq_qna_page_url );
                }else{
                    $location = add_query_arg(
                        array(
                            'error'   => true,
                            'message' => 6,
                        ),
                        $faq_qna_page_url
                    );
                }
                wp_redirect( $location );
                exit;
            }
            
        }elseif( $action == 'delete-faq-qna'){
            $faq_id = ( isset( $_REQUEST['faq_id'] ) ) ? absint( $_REQUEST['faq_id'] ) : 0;
            $qna_id = ( isset( $_REQUEST['qna_id'] ) ) ? absint( $_REQUEST['qna_id'] ) : 0;

            $result = PU()->query->delete_faq_qna( $qna_id );
            if( $result ) {
                $location = add_query_arg( 'message', 6, $faq_qna_page_url );
            }else{
                $location = add_query_arg(
                    array(
                        'error'   => true,
                        'message' => 6,
                    ),
                    $faq_qna_page_url
                );
            }
            wp_redirect( $location );
            exit;
        }
    }

    
}