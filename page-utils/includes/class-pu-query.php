<?php

defined( 'ABSPATH' ) || exit;

/**
 * PU_Query Class.
 */
class PU_Query {

	/**
	 * FAQ headers table instance.
	 *
	 * @var FAQ
	 */
	public $table_faq_headers = null;

	/**
	 * FAQ question_answer table instance.
	 *
	 * @var FAQ
	 */
	public $table_faq_question_answer = null;

	/**
	 * Constructor for the query class. Hooks in methods.
	 */
	public function __construct() {
		global $wpdb;
		$this->table_faq_headers = $wpdb->prefix . 'faq_headers'; 
		$this->table_faq_question_answer = $wpdb->prefix . 'faq_question_answer'; 
	}

	/**
	 * Insert data into faq_headers
	 */
	public function insert_faq( $data = array() ) {
		global $wpdb;
		if( empty( $data ) ) return false;
		$result = $wpdb->insert( $this->table_faq_headers, $data );
		return $wpdb->insert_id;
	}

	/**
	 * Update data into faq_headers
	 */
	public function update_faq( $data = array(), $id = 0 ) {
		global $wpdb;
		if( empty( $data ) ) return false;
		$result = $wpdb->update( $this->table_faq_headers, $data, array( 'id' => $id ) );
		return $result;
	}

	/**
	 * Delete data into faq_headers
	 */
	public function delete_faq( $id ) {
		global $wpdb;
		if( empty( $id ) ) return false;
		$result = $wpdb->delete( $this->table_faq_headers, array( 'id' => $id ) );
		return $result;
	}

	/**
	 * Get FAQ data from faq_headers
	 */
	public function get_faq( $id ) {
		global $wpdb;
		if( empty( $id ) ) return false;
		$result = $wpdb->get_row( "SELECT * FROM $this->table_faq_headers WHERE id = $id" );
		return $result;
	}

	/**
	 * Get all FAQ data from faq_headers
	 */
	public function get_all_faq( $sql = '', $output = OBJECT ) {
		global $wpdb;
		if( empty( $sql ) ) {
			$results = $wpdb->get_results( "SELECT * FROM $this->table_faq_headers", $output );
		}else{
			$results = $wpdb->get_results( "SELECT * FROM $this->table_faq_headers " . $sql, $output );
		}
		return $results;
	}

	/**
	 * Get total FAQ items counts from faq_headers
	 */
	public function get_total_faq_items() {
		global $wpdb;
		$result = $wpdb->get_var( "SELECT COUNT(id) FROM $this->table_faq_headers" );
		return $result;
	}

	/**
	 * Insert data into faq_question_answer
	 */
	public function insert_faq_qna( $data = array() ) {
		global $wpdb;
		if( empty( $data ) ) return false;
		$result = $wpdb->insert( $this->table_faq_question_answer, $data );
		if( $result && $wpdb->insert_id )
			$wpdb->update( $this->table_faq_question_answer, array( 'faq_order' => $wpdb->insert_id ), array( 'id' => $wpdb->insert_id ) );
		return $wpdb->insert_id;
	}

	/**
	 * Update data into faq_question_answer
	 */
	public function update_faq_qna( $data = array(), $id = 0 ) {
		global $wpdb;
		if( empty( $data ) ) return false;
		$result = $wpdb->update( $this->table_faq_question_answer, $data, array( 'id' => $id ) );
		return $result;
	}

	/**
	 * Delete data into faq_question_answer
	 */
	public function delete_faq_qna( $id ) {
		global $wpdb;
		if( empty( $id ) ) return false;
		$result = $wpdb->delete( $this->table_faq_question_answer, array( 'id' => $id ) );
		return $result;
	}

	/**
	 * Get FAQ data from faq_question_answer
	 */
	public function get_faq_qna( $id, $where = '' ) {
		global $wpdb;
		if( !empty( $id ) ) {
			$result = $wpdb->get_row( "SELECT * FROM $this->table_faq_question_answer WHERE id = $id" );
		}elseif( !empty( $where ) ) {
			$result = $wpdb->get_row( "SELECT * FROM $this->table_faq_question_answer $where" );
		}else{
			return false;
		}
		
		return $result;
	}

	/**
	 * Get all QNA data from faq_question_answer
	 */
	public function get_all_qna( $sql = '', $output = OBJECT ) {
		global $wpdb;
		if( empty( $sql ) ) {
			$results = $wpdb->get_results( "SELECT * FROM $this->table_faq_question_answer", $output );
		}else{
			$results = $wpdb->get_results( "SELECT * FROM $this->table_faq_question_answer " . $sql, $output );
		}
		return $results;
	}

	/**
	 * Get total QNA items counts from faq_question_answer
	 */
	public function get_total_qna_items( $where = '' ) {
		global $wpdb;
		$result = $wpdb->get_var( "SELECT COUNT(id) FROM $this->table_faq_question_answer $where" );
		return $result;
	}

}