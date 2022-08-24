<?php

namespace Beapi\MultisiteSharedBlocks;

class Plugin {

	/**
	 * Plugin's activation process.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function activate(): void {
		Database::setup();
	}
}
