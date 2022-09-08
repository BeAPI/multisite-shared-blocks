<?php

namespace Beapi\MultisiteSharedBlocks;

/**
 * Class Preview is used to render
 *
 * @package Beapi\MultisiteSharedBlocks
 */
class Preview {

	use Singleton;

	/**
	 * @inheritDoc
	 */
	protected function init(): void {
		add_action( 'wp_loaded', [ $this, 'maybe_do_preview' ], 9999 );
	}

	/**
	 * Handle shared block preview request.
	 *
	 * Use a custom template to output the shared block content.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function maybe_do_preview(): void {
		//phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( empty( $_GET['action'] ) || 'msb-preview' !== $_GET['action'] ) {
			return;
		}

		$site_id       = (int) ( $_GET['site_id'] ?? 0 );
		$post_id       = (int) ( $_GET['post_id'] ?? 0 );
		$block_id      = isset( $_GET['block_id'] ) ? sanitize_text_field( (string) $_GET['block_id'] ) : '';
		$request_token = isset( $_GET['token'] ) ? sanitize_text_field( (string) $_GET['token'] ) : '';
		//phpcs:enable WordPress.Security.NonceVerification.Recommended

		if ( 0 === $site_id || 0 === $post_id || '' === $block_id || '' === $request_token ) {
			exit;
		}

		if ( ! Helpers::validate_shared_block_preview_request( $request_token, $site_id, $post_id, $block_id ) ) {
			exit;
		}

		// Cleanup unnecessary actions/filters for the preview
		remove_action( 'wp_head', 'feed_links', 2 );
		remove_action( 'wp_head', 'feed_links_extra', 3 );
		remove_action( 'wp_head', 'rsd_link' );
		remove_action( 'wp_head', 'wlwmanifest_link' );
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_head', 'wp_generator' );
		remove_action( 'wp_head', 'rel_canonical' );
		remove_action( 'wp_head', 'wp_shortlink_wp_head' );
		remove_action( 'wp_head', 'wp_site_icon', 99 );

		Helpers::render(
			'preview',
			[
				'site_id'  => $site_id,
				'post_id'  => $post_id,
				'block_id' => $block_id,
			]
		);
		exit;
	}
}
