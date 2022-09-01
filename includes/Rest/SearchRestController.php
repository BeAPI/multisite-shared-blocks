<?php

namespace Beapi\MultisiteSharedBlocks\Rest;

use Beapi\MultisiteSharedBlocks\Query;
use Beapi\MultisiteSharedBlocks\SharedBlockDto;

/**
 * Class SearchRestController handle search requests for the shared block's block selector.
 *
 * @package Beapi\MultisiteSharedBlocks\Rest
 */
class SearchRestController extends \WP_REST_Controller {

	/**
	 * SearchRestController constructor.
	 */
	public function __construct() {
		$this->namespace = 'multisite-shared-blocks/v1';
		$this->rest_base = 'search';
	}

	/**
	 * @inheritDoc
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			[
				[
					'methods'             => [ \WP_REST_Server::READABLE ],
					'callback'            => [ $this, 'get_items' ],
					'permission_callback' => [ $this, 'get_items_permissions_check' ],
					'args'                => $this->get_collection_params(),
				],
				'schema' => [ $this, 'get_public_item_schema' ],
			]
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_items( $request ) {
		$args = [
			's'         => $request->get_param( 'search' ),
			'post_type' => $request->get_param( 'post_type' ),
			'site__in'  => $request->get_param( 'site_in' ),
			'post__in'  => $request->get_param( 'post_in' ),
			'per_page'  => $request->get_param( 'per_page' ),
			'paged'     => $request->get_param( 'page' ),
		];

		$query = new Query( $args );

		$results = [];

		foreach ( $query->get_results() as $result ) {
			$dto  = SharedBlockDto::from_record( $result );
			$data = $dto->to_array();
			unset( $data['id'] );

			// Build custom title for the admin.
			$site       = \get_blog_details( (int) $data['site_id'] );
			$post_title = $data['post_title'];
			$post_type  = \get_post_type_object( $data['post_type'] );
			if ( null !== $post_type ) {
				$post_title = $post_type->labels->singular_name . ' "' . $post_title . '"';
			}

			$data['full_block_title'] = sprintf(
				// translators: 1: is the shared block title, 2: is the post title, 3: is the blog name
				__( '%1$s - %2$s (Site %3$s)', 'multisite-shared-blocks' ),
				$data['block_title'],
				$post_title,
				( false !== $site ) ? $site->blogname : __( 'Unknown', 'multisite-shared-blocks' )
			);

			$results[] = $data;
		}

		$total     = $query->get_total();
		$page      = (int) $request['page'];
		$per_page  = (int) $request['per_page'];
		$max_pages = ceil( $total / $per_page );

		if ( $page > $max_pages && $total > 0 ) {
			return new \WP_Error(
				'rest_search_invalid_page_number',
				__( 'The page number requested is larger than the number of pages available.', 'default' ),
				[ 'status' => 400 ]
			);
		}

		$response = rest_ensure_response( $results );
		$response->header( 'X-WP-Total', $total );
		$response->header( 'X-WP-TotalPages', $max_pages );

		$request_params = $request->get_query_params();
		$base           = add_query_arg( urlencode_deep( $request_params ), rest_url( sprintf( '%s/%s', $this->namespace, $this->rest_base ) ) );

		if ( $page > 1 ) {
			$prev_link = add_query_arg( 'page', $page - 1, $base );
			$response->link_header( 'prev', $prev_link );
		}
		if ( $page < $max_pages ) {
			$next_link = add_query_arg( 'page', $page + 1, $base );
			$response->link_header( 'next', $next_link );
		}

		return $response;
	}

	/**
	 * @inheritDoc
	 */
	public function get_items_permissions_check( $request ) {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * @inheritDoc
	 */
	public function get_collection_params() {
		$query_params                       = parent::get_collection_params();
		$query_params['context']['default'] = 'view';

		$query_params['site_in'] = [
			'description'       => __( 'Limit results to shared blocks coming from specific sites.', 'multisite-shared-blocks' ),
			'type'              => 'array',
			'default'           => [],
			'sanitize_callback' => [ $this, 'sanitize_integer_array' ],
			'validate_callback' => 'rest_validate_request_arg',
		];

		$query_params['post_in'] = [
			'description'       => __( 'Limit results to shared block contain in specific posts.', 'multisite-shared-blocks' ),
			'type'              => 'array',
			'default'           => [],
			'sanitize_callback' => [ $this, 'sanitize_integer_array' ],
			'validate_callback' => 'rest_validate_request_arg',
		];

		$query_params['post_type'] = [
			'description' => __( 'Limit results to shared block contain in specific post types.', 'multisite-shared-blocks' ),
			'type'        => [ 'string', 'array' ],
			'default'     => 'any',
		];

		return $query_params;
	}

	/**
	 * Convert an array or a string into an array of integer.
	 *
	 * @since 1.0.0
	 *
	 * @param array|string $value
	 *
	 * @return int[]
	 */
	public function sanitize_integer_array( $value ): array {
		if ( ! is_array( $value ) ) {
			$value = [ $value ];
		}

		return array_filter(
			array_map(
				'absint',
				$value
			)
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_item_schema() {
		if ( $this->schema ) {
			return $this->add_additional_fields_schema( $this->schema );
		}

		$schema = [
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'search-result',
			'type'       => 'object',
			'properties' => [
				'site_id'          => [
					'description' => __( 'Unique identifier of the site where the shared block originated.', 'multisite-shared-blocks' ),
					'type'        => [ 'integer', 'string' ],
					'context'     => [ 'view' ],
					'readonly'    => true,
				],
				'post_id'          => [
					'description' => __( 'Unique identifier of the post containing the shared block.', 'multisite-shared-blocks' ),
					'type'        => [ 'integer', 'string' ],
					'context'     => [ 'view' ],
					'readonly'    => true,
				],
				'post_type'        => [
					'description' => __( 'Type of post where the shared block is contain.', 'multisite-shared-blocks' ),
					'type'        => [ 'string' ],
					'context'     => [ 'view' ],
					'readonly'    => true,
				],
				'post_title'       => [
					'description' => __( 'Title of post where the shared block is contain.', 'multisite-shared-blocks' ),
					'type'        => [ 'string' ],
					'context'     => [ 'view' ],
					'readonly'    => true,
				],
				'block_id'         => [
					'description' => __( 'Unique identifier of the shared block.', 'multisite-shared-blocks' ),
					'type'        => 'string',
					'context'     => [ 'view' ],
					'readonly'    => true,
				],
				'block_title'      => [
					'description' => __( 'Title of the shared block.', 'multisite-shared-blocks' ),
					'type'        => 'string',
					'context'     => [ 'view' ],
					'readonly'    => true,
				],
				'full_block_title' => [
					'description' => __( 'Title of the shared block including original site name.', 'multisite-shared-blocks' ),
					'type'        => 'string',
					'context'     => [ 'view' ],
					'readonly'    => true,
				],
			],
		];

		$this->schema = $schema;

		return $this->add_additional_fields_schema( $this->schema );
	}
}
