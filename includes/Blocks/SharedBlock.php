<?php

namespace Beapi\MultisiteSharedBlocks\Blocks;

use Beapi\MultisiteSharedBlocks\Api;
use Beapi\MultisiteSharedBlocks\BlockDataCache;
use Beapi\MultisiteSharedBlocks\Helpers;
use Beapi\MultisiteSharedBlocks\Singleton;

/**
 * Class SharedBlock
 *
 * @package Beapi\MultisiteSharedBlocks\Blocks
 */
final class SharedBlock {

	use Singleton;

	/**
	 * @inheritDoc
	 */
	protected function init(): void {
		add_action( 'init', [ $this, 'register_block' ] );
	}

	/**
	 * Register "Shared block" block type.
	 *
	 * Additional data for the block's filters are linked to block's the editor script.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_block(): void {
		register_block_type(
			MULTISITE_SHARED_BLOCKS_DIR . '/build/blocks/shared-block',
			[
				'render_callback' => [ $this, 'render_callback' ],
			]
		);

		// Include filters' options.
		wp_add_inline_script(
			'multisite-shared-blocks-shared-block-editor-script',
			sprintf(
				'var multisiteSharedBlocksEditorData = %s',
				(string) \wp_json_encode( $this->get_block_data() )
			),
			'before'
		);
	}

	/**
	 * Block's render callback.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attributes
	 * @param string $content
	 * @param \WP_Block $block
	 *
	 * @return string
	 */
	public function render_callback( $attributes, $content, $block ): string {
		$site_id  = (int) ( $attributes['siteId'] ?? 0 );
		$post_id  = (int) ( $attributes['postId'] ?? 0 );
		$block_id = (string) ( $attributes['blockId'] ?? '' );
		$display  = (string) ( $attributes['display'] ?? 'full' );

		if ( empty( $site_id ) || empty( $post_id ) || empty( $block_id ) ) {
			return '';
		}

		$block_data = $this->get_rendered_block( $site_id, $post_id, $block_id );

		// When displaying full content, just return the rendered content.
		if ( 'full' === $display ) {
			/**
			 * Filters if dependencies for the blocks contain in the shared block should be enqueued or not on
			 * the current site.
			 *
			 * @since 1.0.0
			 *
			 * @param bool $skip_dependencies True to skip enqueuing dependencies. Default to false.
			 */
			$skip_dependencies = apply_filters( 'multisite_shared_block_skip_block_dependencies', false );

			$use_block_types = $block_data['use_block_types'] ?? [];
			$block_registry  = \WP_Block_Type_Registry::get_instance();
			if ( ! $skip_dependencies && ! empty( $use_block_types ) && null !== $block_registry ) {
				foreach ( $use_block_types as $block_type_name ) {
					$block_type = $block_registry->get_registered( $block_type_name );
					if ( null === $block_type ) {
						continue;
					}

					if ( null !== $block_type->script ) {
						wp_enqueue_script( $block_type->script );
					}

					if ( null !== $block_type->style ) {
						wp_enqueue_style( $block_type->style );
					}
				}
			}

			/**
			 * Filters if support styles generated during the shared block rendering should be enqueued or not on
			 * the current site.
			 *
			 * @since 1.0.0
			 *
			 * @param bool $skip_support_styles True to skip enqueuing support styles. Default to false.
			 */
			$skip_support_styles = apply_filters( 'multisite_shared_block_skip_block_support_styles', false );

			$block_support_styles = $block_data['block_support_styles'] ?? '';
			if ( ! $skip_support_styles && ! empty( $block_support_styles ) ) {
				$action_hook_name = Helpers::get_block_support_styles_hook_name();
				add_action(
					$action_hook_name,
					function () use ( $block_support_styles ) {
						$block_support_styles = preg_replace(
							'@<(script)[^>]*?>.*?</\\1>@si',
							'',
							$block_support_styles
						);
						echo wp_kses(
							$block_support_styles,
							[
								'style' => [
									'nonce' => true,
									'media' => true,
									'title' => true,
								],
							]
						);
					}
				);
			}

			/**
			 * Ensure Shared block output only contain allowed HTML tags and attributes.
			 *
			 * Since the shortcodes and embed from the original source have already been processed, we temporarily allow
			 * the `iframe` tag in the output.
			 */
			$html = preg_replace( '@<(script)[^>]*?>.*?</\\1>@si', '', $block_data['html'] );
			add_filter( 'wp_kses_allowed_html', [ Helpers::class, 'kses_post_iframe_tag' ], 10, 2 );
			$html = wp_kses_post( $html );
			remove_filter( 'wp_kses_allowed_html', [ Helpers::class, 'kses_post_iframe_tag' ] );
			return $html;
		}

		// When displaying excerpt content, prepare excerpt content and return the custom view.
		$block_excerpt = $this->make_excerpt( $block_data['html'] );
		if ( empty( $block_excerpt ) ) {
			return '';
		}

		$post_title     = $block_data['post_title'];
		$post_permalink = $block_data['post_permalink'];

		/**
		 * Filter block's template slug.
		 *
		 * Template must be located in the theme as it will be loaded via `get_template_part`.
		 *
		 * @since 1.0.0
		 *
		 * @param string $template_slug block's template slug
		 * @param array $attributes block's attributes
		 * @param \WP_Block $block block's \WP_Block instance
		 */
		$template_slug = apply_filters( 'multisite_shared_block_template_slug', 'components/gutenberg/shared-block/block-excerpt', $attributes, $block ); //phpcs:ignore

		/**
		 * Filter block's template args.
		 *
		 * @since 1.0.0
		 *
		 * @param string $template_slug block's template slug
		 * @param array $attributes block's attributes
		 * @param \WP_Block $block block's \WP_Block instance
		 */
		$template_args = apply_filters(
			'multisite_shared_block_template_args', //phpcs:ignore
			[
				'block_excerpt'           => $block_excerpt,
				'original_post_title'     => $post_title,
				'original_post_permalink' => $post_permalink,
			],
			$template_slug,
			$attributes,
			$block
		);

		ob_start();
		$rendered      = get_template_part( $template_slug, '', $template_args );
		$block_content = ob_get_clean();

		// If block render was successful return the content.
		if ( false !== $rendered ) {
			return $block_content;
		}

		// Otherwise use default template.
		ob_start();
		load_template( MULTISITE_SHARED_BLOCKS_DIR . '/views/block-excerpt.php', false, $template_args );

		return ob_get_clean();
	}

	/**
	 * Prepare block data print in the Gutenberg editor page.
	 *
	 * @since 1.0.0
	 *
	 * @return array[]
	 */
	private function get_block_data(): array {

		$api = Api::for_current_site();

		// Prepare list of available sites for the block's filter.
		$site_ids        = $api->list_sites();
		$available_sites = [];
		foreach ( $site_ids as $site_id ) {
			$site = get_site( $site_id );
			if ( null === $site ) {
				continue;
			}

			$available_sites[ $site_id ] = $site->blogname;
		}

		// Prepare list of available post_types for the block's filter.
		$post_types           = $api->list_post_types();
		$available_post_types = [];
		foreach ( $post_types as $post_type ) {
			$post_type_object = get_post_type_object( $post_type );
			if ( null === $post_type_object ) {
				continue;
			}
			$available_post_types[ $post_type ] = (string) ( $post_type_object->labels->singular_name ?? $post_type );
		}

		return [
			'sites'      => $available_sites,
			'post_types' => $available_post_types,
		];
	}

	/**
	 * Execute a remote request to the "shared blocks" render endpoint to retrieve block data containing the
	 * html content, the original post title and permalink.
	 *
	 * Making a remote request on the original site ensure the block content is rendered with the
	 * correct context (plugins/cpt/tax/options etc.).
	 *
	 * @since 1.0.0
	 *
	 * @param int $site_id original site id.
	 * @param int $post_id original post id.
	 * @param string $block_id block id.
	 *
	 * @return array rendered block data.
	 * @psalm-return array{html: string, use_block_types: string[], block_support_styles:string, post_title:string, post_permalink:string}
	 */
	private function get_rendered_block( int $site_id, int $post_id, string $block_id ): array {
		// Get data from cache.
		$data = BlockDataCache::get( $site_id, $post_id, $block_id );
		if ( null !== $data ) {
			return $data;
		}

		$block_data = [
			'html'                 => '',
			'use_block_types'      => [],
			'block_support_styles' => '',
			'post_title'           => '',
			'post_permalink'       => '',
		];

		$rest_url = get_rest_url(
			$site_id,
			sprintf( '/multisite-shared-blocks/v1/renderer/%d/%s', $post_id, $block_id )
		);

		$response = wp_safe_remote_get( $rest_url );
		if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			return $block_data;
		}

		$body = wp_remote_retrieve_body( $response );
		/** @var array $parsed_json */
		$parsed_json = \json_decode( $body, true );

		if ( empty( $parsed_json ) || JSON_ERROR_NONE !== \json_last_error() ) {
			return $block_data;
		}

		$block_data = wp_parse_args(
			[
				'html'                 => $parsed_json['rendered'] ?? '',
				'use_block_types'      => $parsed_json['use_block_types'] ?? [],
				'block_support_styles' => $parsed_json['block_support_styles'] ?? '',
				'post_title'           => $parsed_json['post']['title'] ?? '',
				'post_permalink'       => $parsed_json['post']['link'] ?? '',
			],
			$block_data
		);

		BlockDataCache::set( $site_id, $post_id, $block_id, $block_data );

		return $block_data;
	}

	/**
	 * Make excerpt from block's html content.
	 *
	 * @since 1.0.0
	 *
	 * @param string $block_html
	 *
	 * @return string
	 */
	private function make_excerpt( string $block_html ): string {
		/** This filter is documented in wp-includes/formatting.php */
		$excerpt_more = apply_filters( 'excerpt_more', ' [&hellip;]' );

		return wp_html_excerpt( $block_html, 280, $excerpt_more );
	}
}
