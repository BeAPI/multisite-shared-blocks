<?php

namespace Beapi\MultisiteSharedBlocks\Admin;

use Beapi\MultisiteSharedBlocks\Api;
use Beapi\MultisiteSharedBlocks\BlockDataCache;
use Beapi\MultisiteSharedBlocks\Helpers;
use Beapi\MultisiteSharedBlocks\Singleton;

class Main {

	use Singleton;

	public const MENU_SLUG = 'multisite-shared-blocks';

	/**
	 * @inheritDoc
	 */
	protected function init(): void {
		add_action( 'admin_init', [ $this, 'handle_flush_action' ] );
		add_action( 'admin_menu', [ $this, 'register_admin_page' ] );
	}

	/**
	 * Handle shared block cache purge request.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function handle_flush_action(): void {
		if (
			empty( $_GET['action'] )
			|| 'msb-flush' !== $_GET['action']
			|| ! current_user_can( 'manage_options' )
		) {
			return;
		}

		$block_record_id = (int) ( $_GET['block'] ?? 0 );
		if ( empty( $block_record_id ) ) {
			return;
		}

		$action = sprintf( 'flush_block_%s', $block_record_id );
		if ( false === check_admin_referer( $action ) ) {
			return;
		}

		$dto = Api::for_current_site()->get_by_id( $block_record_id );
		if ( null === $dto ) {
			wp_safe_redirect(
				add_query_arg(
					[
						'page'    => self::MENU_SLUG,
						'message' => 10,
					],
					admin_url( 'tools.php' )
				)
			);
			exit();
		}

		BlockDataCache::delete( $dto->get_site_id(), $dto->get_post_id(), $dto->get_block_id() );
		wp_safe_redirect(
			add_query_arg(
				[
					'page'    => self::MENU_SLUG,
					'message' => 1,
				],
				admin_url( 'tools.php' )
			)
		);
		exit();
	}

	public function register_admin_page(): void {
		add_management_page(
			__( 'Multisite Shared Blocks', 'multisite-shared-blocks' ),
			__( 'Multisite Shared Blocks', 'multisite-shared-blocks' ),
			'edit_posts',
			self::MENU_SLUG,
			[ $this, 'render_callback' ]
		);
	}

	public function render_callback(): void {
		add_thickbox();
		Helpers::render( 'admin/admin-page' );
	}
}
