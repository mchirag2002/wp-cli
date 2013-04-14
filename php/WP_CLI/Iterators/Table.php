<?php

namespace WP_CLI\Iterators;

/**
 * @source https://gist.github.com/4060005
 */
class Table extends Query {

	/**
	 * Creates an iterator over a database table
	 *
	 * <code>
	 * foreach( new Iterators\Table( array( 'table' => $wpdb->posts, 'fields' => array( 'ID', 'post_content' ) ) ) as $post ) {
	 *     count_words_for( $post->ID, $post->post_content );
	 * }
	 * </code>
	 *
	 * <code>
	 * foreach( new Iterators\Table( array( 'table' => $wpdb->posts, 'where' => 'ID = 8 OR post_status = "publish"' ) ) as $post ) {
	 *     …
	 * }
	 * </code>
	 *
	 * <code>
	 * foreach( new PostIterator( array( 'table' => $wpdb->posts, 'where' => array( 'post_status' => 'publish', 'post_date_gmt BETWEEN x AND y' ) ) ) as $post ) {
	 *     …
	 * }
	 * </code>
	 *
	 *
	 * @param array $args Supported arguments:
	 *		table – the name of the database table
	 *		fields – an array of columns to get from the posst table, * is a valid value and the default
	 *		where – conditions for filtering rows. Supports two formats:
	 *			= string – this will be the where clause
	 *			= array – each element is treated as a condition if it's positional, or as column => value if
	 *				it's a key/value pair. In the latter case the value is automatically quoted and escaped
	 */
	function __construct( $args = array() ) {
		global $wpdb;

		$defaults = array(
			'fields' => array( '*' ),
			'where' => array(),
			'table' => null,
		);
		$table = $args['table'];
		$args = array_merge( $defaults, $args );

		$fields = self::build_fields( $args['fields'] );
		$conditions = self::build_where_conditions( $args['where'] );
		$where_sql = $conditions? " WHERE $conditions" : '';
		$query = "SELECT $fields FROM $table $where_sql";

		$limit = isset( $args['limit'] ) ? $args['limit'] : 500;

		parent::__construct( $query, $limit );
	}

	private static function build_fields( $fields ) {
		return implode( ', ', $fields );
	}

	private static function build_where_conditions( $where ) {
		global $wpdb;
		if ( is_array( $where ) ) {
			$conditions = array();
			foreach( $where as $key => $value ) {
				if ( is_numeric( $key ) )
					$conditions[] = $value;
				else
					$conditions[] = $key . ' = "' . $wpdb->escape( $value ) .'"';
			}
			$where = implode( ' AND ', $conditions );
		}
		return $where;
	}
}

