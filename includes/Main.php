<?php

namespace Beapi\MultisiteSharedBlocks;

/**
 * Class Main
 *
 * @package Beapi\MultisiteSharedBlocks
 */
class Main {

	use Singleton;

	protected function init(): void {
		add_action( 'init', [ $this, 'init_translations' ] );
	}

	/**
	 * Load the plugin translation
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function init_translations(): void {
		// Load translations
		load_plugin_textdomain( 'multisite-shared-blocks', false, MULTISITE_SHARED_BLOCKS_PLUGIN_DIRNAME . '/languages' );
	}
}
