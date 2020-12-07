<?php
defined('ABSPATH') or die('No script kiddies please!');
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
if (!class_exists('WP_List_Table')) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
class Quote_List extends WP_List_Table {
	/**
	 * Prepare the items for the table to process
	 *
	 * @return Void
	 */
	public function prepare_items() {
		$columns = $this->get_columns();

		$hidden = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();
		$data = $this->table_data();
		if (!empty($data)) {
			usort($data, array(&$this, 'sort_data'));
		}
		$perPage = 20;
		$currentPage = $this->get_pagenum();
		$totalItems = count($data);
		$this->set_pagination_args(array(
			'total_items' => $totalItems,
			'per_page' => $perPage,
		));
		if (!empty($data)) {
			$data = array_slice($data, (($currentPage - 1) * $perPage), $perPage);
		}
		$this->_column_headers = array($columns, $hidden, $sortable);
		$this->items = $data;
	}

	/**
	 * Override the parent columns method. Defines the columns to use in your listing table
	 *
	 * @return Array
	 */
	public function get_columns() {
		$columns = array(
			'name' => 'Name',
			'email' => 'Email',
			'phone' => 'Phone',
			'firm_name' => 'Firm',
			'created_on' => 'Date',
			'quote_csv' => 'Courts CSV',
		);
		return $columns;
	}

	/**
	 * Define which columns are hidden
	 *
	 * @return Array
	 */
	public function get_hidden_columns() {
		return array();
	}

	/**
	 * Define the sortable columns
	 *
	 * @return Array
	 */
	public function get_sortable_columns() {
		return array('name' => array('name', false), 'email' => array('email', false), 'phone' => array('phone', false), 'firm_name' => array('firm_name', false), 'created_on' => array('created_on', false));
	}

	/**
	 * Get the table data
	 *
	 * @return Array
	 */
	private function table_data($per_page = 20, $page_number = 1, $archive) {
		global $wpdb;

		$sql = "SELECT * FROM quote_user";

		if (!empty($_REQUEST['orderby'])) {
			$sql .= ' ORDER BY ' . esc_sql($_REQUEST['orderby']);
			$sql .= !empty($_REQUEST['order']) ? ' ' . esc_sql($_REQUEST['order']) : ' ASC';
		}

		$sql .= " LIMIT $per_page";

		$sql .= ' OFFSET ' . ($page_number - 1) * $per_page;

		$result = $wpdb->get_results($sql, 'ARRAY_A');

		return $result;
	}

	/**
	 * Define what data to show on each column of the table
	 *
	 * @param  Array $item        Data
	 * @param  String $column_name - Current column name
	 *
	 * @return Mixed
	 */
	public function column_default($item, $column_name) {
		switch ($column_name) {
		case 'name':
			return $item[$column_name];
			break;
		case 'email':
			return $item[$column_name];
			break;
		case 'phone':
			return $item[$column_name];
			break;
		case 'firm_name':
			return $item[$column_name];
			break;
		case 'created_on':
			$newDate = date("m/d/Y g:i:s A", strtotime($item[$column_name]));
			return $newDate;
			break;
		case 'quote_csv':
			return sprintf('<a href="../logs/' . $item[$column_name] . '">Download</a>' . '%1$s', $this->row_actions($actions));
			break;
		default:
			return print_r($item, true);
		}
	}

	/**
	 * Allows you to sort the data by the variables set in the $_GET
	 *
	 * @return Mixed
	 */
	private function sort_data($a, $b) {
		// Set defaults
		$orderby = 'bs_code';
		$order = 'asc';
		// If orderby is set, use this as the sort column
		if (!empty($_GET['orderby'])) {
			$orderby = $_GET['orderby'];
		}
		// If order is set use this as the order
		if (!empty($_GET['order'])) {
			$order = $_GET['order'];
		}
		$result = strcmp($a[$orderby], $b[$orderby]);
		if ($order === 'asc') {
			return $result;
		}
		return -$result;
	}
}
