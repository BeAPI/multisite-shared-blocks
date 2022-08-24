<?php

namespace Beapi\MultisiteSharedBlocks\Rest;

use Beapi\MultisiteSharedBlocks\Singleton;

/**
 * Class Rest is used to register all endpoints.
 *
 * @package Beapi\MultisiteSharedBlocks\Rest
 */
class Rest {

	use Singleton;

	/**
	 * @inheritDoc
	 */
	protected function init(): void {
		add_action( 'rest_api_init', [ $this, 'register_endpoints' ] );
	}

	/**
	 * Register REST endpoints
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_endpoints(): void {
		( new RenderBlockRestController() )->register_routes();
		( new SearchRestController() )->register_routes();
	}
}
