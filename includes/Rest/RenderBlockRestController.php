<?php

namespace Beapi\MultisiteSharedBlocks\Rest;

use Beapi\MultisiteSharedBlocks\Gutenberg\SharedBlocksAttributes;
use Beapi\MultisiteSharedBlocks\Helpers;
use Beapi\MultisiteSharedBlocks\Parser;

/**
 * Class RenderBlockRestController handle the rendering of the shared blocks for embedding on network's sites.
 *
 * @package Beapi\MultisiteSharedBlocks\Rest
 */
class RenderBlockRestController extends \WP_REST_Controller {

	/**
	 * Store block types used during the rendering of a shared block.
	 *
	 * @var array
	 *
	 * @access private
	 */
	public $rendered_block_types = [];

	/**
	 * RenderBlockRestController constructor.
	 */
	public function __construct() {
		$this->namespace = 'multisite-shared-blocks/v1';
		$this->rest_base = 'renderer';
	}

	/**
	 * @inheritDoc
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<post_id>[\d]+)/(?P<block_id>[a-zA-Z0-9-]+)',
			[
				'args'   => [
					'post_id'  => [
						'description' => __( 'Unique identifier for the post containing the shared block.', 'multisite-shared-blocks' ),
						'type'        => 'integer',
					],
					'block_id' => [
						'description' => __( 'Unique identifier for the shared block.', 'multisite-shared-blocks' ),
						'type'        => 'string',
					],
				],
				[
					'methods'             => [ \WP_REST_Server::READABLE ],
					'callback'            => [ $this, 'get_item' ],
					'permission_callback' => '__return_true',
					'args'                => [],
				],
				'schema' => [ $this, 'get_public_item_schema' ],
			]
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_item( $request ) {
		$post_id  = $request->get_param( 'post_id' );
		$block_id = $request->get_param( 'block_id' );

		$shared_block_post = get_post( $post_id );
		if ( null === $shared_block_post || 'publish' !== get_post_status( $shared_block_post ) ) {
			return new \WP_Error(
				'shared_blocks_invalid_post_id',
				__( 'Invalid post id', 'multisite-shared-blocks' ),
				[ 'status' => 404 ]
			);
		}

		$shared_block_data = Parser::get_shared_block(
			$shared_block_post,
			$block_id,
			SharedBlocksAttributes::get_excluded_block_types()
		);
		if ( null === $shared_block_data ) {
			return new \WP_Error(
				'shared_blocks_invalid_block_id',
				__( 'Invalid shared block id', 'multisite-shared-blocks' ),
				[ 'status' => 404 ]
			);
		}

		$shared_block_data = $this->filter_inner_excluded_blocks(
			$shared_block_data,
			SharedBlocksAttributes::get_excluded_block_types()
		);

		$GLOBALS['post'] = $shared_block_post; //phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		setup_postdata( $shared_block_post );

		$this->setup_use_block_types_capture();
		$this->setup_block_support_styles_capture();

		/** This filter is documented in wp-includes/post-template.php */
		$block_render = apply_filters( 'the_content', serialize_block( $shared_block_data ) );

		// Get list of block types used while rendering the shared block.
		$use_block_types = $this->capture_use_block_types();

		// Get block support styles generated while rendering the shared block.
		$block_support_styles = $this->capture_block_support_styles();

		// Replace dynamically generated container CSS classes in block HML content and CSS rules to avoid collision
		// with existing rules when embedding the block.
		$substitution_list = $this->build_substitution_list( $block_render, (int) $post_id );
		if ( ! empty( $substitution_list ) ) {
			$search               = array_keys( $substitution_list );
			$replace              = array_values( $substitution_list );
			$block_render         = str_replace( $search, $replace, $block_render );
			$block_support_styles = str_replace( $search, $replace, $block_support_styles );
		}

		$data = [
			'post'                 => [
				'title' => get_the_title(),
				'link'  => get_permalink(),
			],
			'rendered'             => $block_render,
			'use_block_types'      => $use_block_types,
			'block_support_styles' => $block_support_styles,
		];

		wp_reset_postdata();

		return rest_ensure_response( $data );
	}

	/**
	 * @inheritDoc
	 */
	public function get_item_schema() {
		if ( $this->schema ) {
			return $this->schema;
		}

		$this->schema = [
			'$schema'    => 'http://json-schema.org/schema#',
			'title'      => 'rendered-shared-block',
			'type'       => 'object',
			'properties' => [
				'post'                 => [
					'description' => __( "Original post's data.", 'multisite-shared-blocks' ),
					'type'        => 'object',
					'context'     => [ 'view' ],
					'readonly'    => true,
					'properties'  => [
						'title' => [
							'description' => __( "Original post's title.", 'multisite-shared-blocks' ),
							'type'        => 'string',
							'context'     => [ 'view' ],
							'readonly'    => true,
						],
						'link'  => [
							'description' => __( "Original post's permalink.", 'multisite-shared-blocks' ),
							'type'        => 'string',
							'context'     => [ 'view' ],
							'readonly'    => true,
						],
					],
				],
				'rendered'             => [
					'description' => __( "Shared block's HTML content.", 'multisite-shared-blocks' ),
					'type'        => 'string',
					'context'     => [ 'view' ],
					'readonly'    => true,
				],
				'use_block_types'      => [
					'description' => __( 'Block types rendered by the shared block.', 'multisite-shared-blocks' ),
					'type'        => 'array',
					'context'     => [ 'view' ],
					'readonly'    => true,
				],
				'block_support_styles' => [
					'description' => __( "Shared block's inline CSS style.", 'multisite-shared-blocks' ),
					'type'        => 'string',
					'context'     => [ 'view' ],
					'readonly'    => true,
				],
			],
		];

		return $this->schema;
	}

	/**
	 * Remove excluded blocks from shared block.
	 *
	 * @since 1.0.0
	 *
	 * @param array $shared_block
	 * @param array $excluded_blocks
	 *
	 * @return array
	 */
	private function filter_inner_excluded_blocks( array $shared_block, array $excluded_blocks = [] ): array {
		if ( empty( $excluded_blocks ) || empty( $shared_block['blockName'] ) || empty( $shared_block['innerBlocks'] ) ) {
			return $shared_block;
		}

		/**
		 * Remove excluded blocks from block's innerBlocks.
		 *
		 * Loop over block's innerBlocks and remove blocks if they are excluded.
		 */
		$filtered_inner_block       = []; // new innerBlocks array for the block.
		$removed_inner_blocks_index = []; // innerBlocks indexes removed.
		foreach ( $shared_block['innerBlocks'] as $k => $inner_block ) {
			if ( empty( $inner_block['blockName'] ) || in_array( $inner_block['blockName'], $excluded_blocks, true ) ) {
				$removed_inner_blocks_index[] = $k;
				continue;
			}

			// Check for excluded blocks in inner blocks
			$filtered_inner_block[] = $this->filter_inner_excluded_blocks( $inner_block, $excluded_blocks );
		}
		$shared_block['innerBlocks'] = $filtered_inner_block;

		/**
		 * Update block's innerContent.
		 *
		 * Loop over block's innerContent and remove placeholders for innerBlocks removed previously. Placeholders are
		 * all null values contain in the innerContent array.
		 */
		$filtered_inner_content = []; // new innerContent for the block.
		$index                  = 0; // index for placeholders.
		foreach ( $shared_block['innerContent'] as $content ) {
			// real values are directly re-added to the innerContent.
			if ( null !== $content ) {
				$filtered_inner_content[] = $content;
				continue;
			}

			// if we encounter a placeholder value and its index is part of the removed innerBlocks, we remove it from
			// innerContent array as well.
			if ( in_array( $index, $removed_inner_blocks_index, true ) ) {
				$index ++;
				continue;
			}

			// finally remaining placeholders are added back to the innerContent array.
			$filtered_inner_content[] = $content;
			$index ++;
		}
		$shared_block['innerContent'] = $filtered_inner_content;

		return $shared_block;
	}

	/**
	 * Extract dynamically generated container CSS classes in block HTML content and generate new unique CSS classes
	 * for each one.
	 *
	 * @since 1.0.0
	 *
	 * @param string $block_render
	 * @param int $post_id
	 *
	 * @return array
	 */
	private function build_substitution_list( string $block_render, int $post_id ): array {
		$substitution_list = [];
		if ( preg_match_all( '@(?<selector>wp-container-(?<num>\d+))@', $block_render, $matches, PREG_SET_ORDER ) ) {
			foreach ( $matches as $match ) {
				$prefix                                  = $match['num'] . $post_id;
				$substitution_list[ $match['selector'] ] = str_replace(
					$match['num'],
					sanitize_html_class( uniqid( $prefix, true ), $match['selector'] ),
					$match['selector']
				);
			}
		}

		return $substitution_list;
	}

	/**
	 * Clear all existing actions hooked to the support styles hook name. This is to ensure the only ones after the
	 * call to `render_block` are block support styles added during the block rendering.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function setup_block_support_styles_capture(): void {
		remove_all_actions( Helpers::get_block_support_styles_hook_name() );
	}

	/**
	 * Get support styles registered during the rendering of the shared block.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	private function capture_block_support_styles(): string {
		$block_support_styles = '';
		$action_hook_name     = Helpers::get_block_support_styles_hook_name();
		if ( has_action( $action_hook_name ) ) {
			ob_start();
			do_action( $action_hook_name );
			$block_support_styles = ob_get_clean();
		}

		return $block_support_styles;
	}

	/**
	 * Setup hook to catch each block being rendered and store their name.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function setup_use_block_types_capture(): void {
		$this->rendered_block_types = [];
		$instance                   = $this;

		add_filter(
			'pre_render_block',
			function ( ?string $html, array $block ) use ( $instance ) {
				if ( ! empty( $block['blockName'] ) ) {
					$instance->rendered_block_types[ $block['blockName'] ] = 1;
				}

				return $html;
			},
			1,
			2
		);
	}

	/**
	 * Return the list of block types stored will rendering the shared block.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	private function capture_use_block_types(): array {
		return array_filter( array_keys( $this->rendered_block_types ) );
	}
}
