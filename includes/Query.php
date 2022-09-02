<?php

namespace Beapi\MultisiteSharedBlocks;

/**
 * Class Query is used to run custom queries against the shared blocks index table.
 *
 * @package Beapi\MultisiteSharedBlocks
 */
final class Query {

	/**
	 * @var string
	 */
	private $request = '';

	/**
	 * @var int
	 */
	private $total = 0;

	/**
	 * @var array
	 */
	private $results = [];

	/**
	 * Query constructor.
	 *
	 * @param array $query
	 */
	public function __construct( array $query ) {
		if ( ! empty( $query ) ) {
			$this->query( $query );
		}
	}

	/**
	 * Execute the query.
	 *
	 * @since 1.0.0
	 *
	 * @param array $query {
	 *     Optional. Array or string of Query parameters.
	 *
	 *     @type string $s                Search keyword(s).
	 *     @type array|string  $site__in  An array of site IDs.
	 *     @type array|string  $post__in  An array of post IDs.
	 *     @type array|string  $post_type An array of post_type slugs.
	 *     @type int    $per_page         The number of results to query for.
	 *     @type int    $paged            The number of the current page.
	 *     @type bool   $nopaging         Get all results (true) or paginate (false). Default false.
	 *     @type string $fields           Fields to query for. Accepts:
	 *                                        - '' Returns an array of objects containing the record data (`stdClass[]`).
	 *                                        - 'block_ids' Returns an array of records IDs (`string[]`).
	 *                                    Default ''.
	 *     @type string $no_found_rows    Whether to skip counting the total rows found. Default false.
	 * }
	 *
	 * @global \wpdb $wpdb WordPress database abstraction object.
	 *
	 * @return void
	 */
	public function query( array $query ): void {
		global $wpdb;

		$table_name = Database::SHARED_BLOCKS_INDEX_TABLE_NAME;

		$query = wp_parse_args(
			$query,
			[
				's'             => '',
				'site__in'      => [],
				'post__in'      => [],
				'post_type'     => 'any',
				'per_page'      => 10,
				'paged'         => 1,
				'nopaging'      => false,
				'fields'        => '',
				'no_found_rows' => false,
			]
		);

		$query_fields = "SELECT {$wpdb->$table_name}.*";
		/** @var ''|'block_ids' $query['fields'] */
		if ( 'block_ids' === $query['fields'] ) {
			$query_fields = "SELECT {$wpdb->$table_name}.block_id";
		}

		$query_from = "FROM {$wpdb->$table_name}";

		$query_where = 'WHERE 1=1';

		// Generate WHERE condition from search query param.
		if ( ! empty( $query['s'] ) ) {

			/**
			 * Search parsing modified from \WP_Query method.
			 *
			 * @see \WP_Query::parse_search()
			 */
			$query['s'] = str_replace( [ "\r", "\n" ], '', $query['s'] );
			if ( preg_match_all( '/".*?("|$)|((?<=[\t ",+])|^)[^\t ",+]+/', $query['s'], $matches ) ) {
				$search_terms = $this->parse_search_terms( $matches[0] );
				// If the search string has only short terms or stopwords, or is 10+ terms long, match it as sentence.
				if ( empty( $search_terms ) || count( $search_terms ) > 9 ) {
					$search_terms = [ $query['s'] ];
				}
			} else {
				$search_terms = [ $query['s'] ];
			}

			$search_condition = '';
			foreach ( $search_terms as $term ) {
				if ( ! empty( $search_condition ) ) {
					$search_condition .= ' OR';
				}

				//phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$search_condition .= (string) $wpdb->prepare(
					" (({$wpdb->$table_name}.post_title LIKE %s) OR ({$wpdb->$table_name}.block_title LIKE %s))",
					'%' . $wpdb->esc_like( $term ) . '%',
					'%' . $wpdb->esc_like( $term ) . '%'
				);
				//phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			}

			if ( ! empty( $search_condition ) ) {
				$query_where .= " AND ($search_condition)";
			}
		}

		// Generate WHERE condition from site__in query param.
		if ( ! empty( $query['site__in'] ) ) {
			$site_ids = $query['site__in'];
			if ( ! is_array( $site_ids ) ) {
				$site_ids = [ $site_ids ];
			}

			/*
			 * \wpdb::prepare can't be used for MySQL `IN` condition, so we prepare the data beforehand and include it
			 * directly in the SQL statement.
			 *
			 * Output will be : `value1, value2, ... valueN`
			 */
			$site_in      = "'";
			$site_in     .= implode( "', '", array_map( 'esc_sql', $site_ids ) ); // escape values and join them
			$site_in     .= "'";
			$query_where .= " AND {$wpdb->$table_name}.site_id IN ($site_in)";
		}

		// Generate WHERE condition from post__in query param.
		if ( ! empty( $query['post__in'] ) ) {
			$post_ids = $query['post__in'];
			if ( ! is_array( $post_ids ) ) {
				$post_ids = [ $post_ids ];
			}

			/*
			 * \wpdb::prepare can't be used for MySQL `IN` condition, so we prepare the data beforehand and include it
			 * directly in the SQL statement.
			 *
			 * Output will be : `value1, value2, ... valueN`
			 */
			$post_in      = "'";
			$post_in     .= implode( "', '", array_map( 'esc_sql', $post_ids ) ); // escape values and join them
			$post_in     .= "'";
			$query_where .= " AND {$wpdb->$table_name}.post_id IN ($post_in)";
		}

		// Generate WHERE condition from post_type query param.
		if ( ! empty( $query['post_type'] ) && 'any' !== $query['post_type'] ) {
			$ptype = wp_parse_list( $query['post_type'] );

			/*
			 * \wpdb::prepare can't be used for MySQL `IN` condition, so we prepare the data beforehand and include it
			 * directly in the SQL statement.
			 *
			 * Output will be : `value1, value2, ... valueN`
			 */
			$ptype_in     = "'";
			$ptype_in    .= implode( "', '", array_map( 'esc_sql', $ptype ) ); // escape values and join them
			$ptype_in    .= "'";
			$query_where .= " AND {$wpdb->$table_name}.post_type IN ($ptype_in)";
		}

		$query_order = 'ORDER BY id ASC';

		// Generate LIMIT condition from per_page and paged query params.
		$query_limit = '';
		if ( false === $query['nopaging'] ) {
			$page = absint( $query['paged'] );
			if ( ! $page ) {
				$page = 1;
			}

			$per_page = absint( $query['per_page'] );
			if ( ! $per_page ) {
				$page = 10;
			}

			$calculate_page = absint( ( $page - 1 ) * $per_page );
			/** @psalm-suppress TooManyArguments */
			$query_limit = $wpdb->prepare( 'LIMIT %d, %d', $calculate_page, $per_page );
		}

		$method = 'get_results';
		if ( 'block_ids' === $query['fields'] ) {
			$method = 'get_col';
		}
		$this->request = "$query_fields $query_from $query_where $query_order $query_limit";
		/** @var array $this->results */
		$this->results = $wpdb->$method( $this->request );

		$this->total = count( $this->results );
		if ( false === (bool) $query['no_found_rows'] ) {
			//phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$this->total = (int) $wpdb->get_var( "SELECT count(*) $query_from $query_where" );
			//phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}
	}

	/**
	 * Get the SQL request executed by the query.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_request(): string {
		return $this->request;
	}

	/**
	 * Get results from the query.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_results(): array {
		return $this->results;
	}

	/**
	 * Check if the query return any results.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function has_results(): bool {
		return ! empty( $this->results );
	}

	/**
	 * Get total results for the query.
	 *
	 * @since 1.0.0
	 *
	 * @return int
	 */
	public function get_total(): int {
		return $this->total;
	}

	/**
	 * Check if the terms are suitable for searching.
	 *
	 * Uses an array of stopwords (terms) that are excluded from the separate
	 * term matching when searching for posts. The list of English stopwords is
	 * the approximate search engines list, and is translatable.
	 *
	 * @see \WP_Query::parse_search()
	 *
	 * @since 1.0.0
	 *
	 * @param string[] $terms Array of terms to check.
	 *
	 * @return string[] Terms that are not stopwords.
	 */
	private function parse_search_terms( array $terms ): array {
		$strtolower = function_exists( 'mb_strtolower' ) ? 'mb_strtolower' : 'strtolower';
		$checked    = [];

		$stopwords = $this->get_search_stopwords();

		foreach ( $terms as $term ) {
			// Keep before/after spaces when term is for exact match.
			if ( preg_match( '/^".+"$/', $term ) ) {
				$term = trim( $term, "\"'" );
			} else {
				$term = trim( $term, "\"' " );
			}

			// Avoid single A-Z and single dashes.
			if ( ! $term || ( 1 === strlen( $term ) && preg_match( '/^[a-z\-]$/i', $term ) ) ) {
				continue;
			}

			if ( in_array( call_user_func( $strtolower, $term ), $stopwords, true ) ) {
				continue;
			}

			$checked[] = $term;
		}

		return $checked;
	}

	/**
	 * Retrieve stopwords used when parsing search terms.
	 *
	 * @see \WP_Query::get_search_stopwords()
	 *
	 * @since 1.0.0
	 *
	 * @return string[] Stopwords.
	 */
	private function get_search_stopwords(): array {
		/** @var string[] $stopwords */
		static $stopwords;

		if ( ! empty( $stopwords ) ) {
			return $stopwords;
		}

		/*
		 * translators: This is a comma-separated list of very common words that should be excluded from a search,
		 * like a, an, and the. These are usually called "stopwords". You should not simply translate these individual
		 * words into your language. Instead, look for and provide commonly accepted stopwords in your language.
		 */
		$words = explode(
			',',
			_x(
				'about,an,are,as,at,be,by,com,for,from,how,in,is,it,of,on,or,that,the,this,to,was,what,when,where,who,will,with,www',
				'Comma-separated list of search stopwords in your language',
				'default'
			)
		);

		$stopwords = [];
		foreach ( $words as $word ) {
			$word = trim( $word, "\r\n\t " );
			if ( $word ) {
				$stopwords[] = $word;
			}
		}

		/** This filter is documented in wp-includes/class-wp-query.php */
		$stopwords = apply_filters( 'wp_search_stopwords', $stopwords );

		return $stopwords;
	}
}
