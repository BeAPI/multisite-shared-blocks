<?php

namespace Beapi\MultisiteSharedBlocks;

/**
 * Class Listener hooks to post's update and delete events to trigger shared blocks index synchronization.
 *
 * @package Beapi\MultisiteSharedBlocks
 */
final class Listener {

	use Singleton;

	/**
	 * @inheritDoc
	 */
	protected function init(): void {
		add_action( 'transition_post_status', [ $this, 'transition_post_status' ], 10, 3 );
		add_action( 'deleted_post', [ $this, 'deleted_post' ], 10, 2 );
	}

	/**
	 * Post's update event.
	 *
	 * @since 1.0.0
	 *
	 * @param string $new_status
	 * @param string $old_status
	 * @param \WP_Post $post
	 *
	 * @return void
	 */
	public function transition_post_status( $new_status, $old_status, $post ): void {
		if ( ! in_array( get_post_type( $post ), Helpers::get_supported_post_types(), true ) ) {
			return;
		}

		if ( 'publish' === $new_status ) {
			PostUpdater::update( $post );

			return;
		}

		if ( in_array( $new_status, [ 'draft', 'pending', 'trash' ], true ) ) {
			PostUpdater::delete( $post );

			return;
		}
	}

	/**
	 * Post's delete event.
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_id
	 * @param \WP_Post $post
	 *
	 * @return void
	 */
	public function deleted_post( $post_id, $post ): void {
		if ( ! in_array( get_post_type( $post ), Helpers::get_supported_post_types(), true ) ) {
			return;
		}

		PostUpdater::delete( $post );
	}
}
