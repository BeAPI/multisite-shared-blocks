<?php

namespace Beapi\MultisiteSharedBlocks\Admin;

use Beapi\MultisiteSharedBlocks\Helpers;
use Beapi\MultisiteSharedBlocks\Singleton;

class Main {

	use Singleton;

	protected function init(): void {
		add_action( 'admin_menu', [ $this, 'register_admin_page' ] );
	}

	public function register_admin_page(): void {
		add_menu_page(
			__( 'Multisite Shared Blocks', 'multisite-shared-blocks' ),
			__( 'Multisite Shared Blocks', 'multisite-shared-blocks' ),
			'edit_posts',
			'multisite-shared-blocks',
			[ $this, 'render_callback' ],
			'dashicons-share'
		);
	}

	public function render_callback(): void {
		Helpers::render( 'admin/admin-page' );
	}
}
