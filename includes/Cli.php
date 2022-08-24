<?php

namespace Beapi\MultisiteSharedBlocks;

use function WP_CLI\Utils\make_progress_bar;

/**
 * Class Cli
 *
 * @package Beapi\MultisiteSharedBlocks
 */
final class Cli extends \WP_CLI_Command {

	/**
	 * Rebuild shared blocks index for the site.
	 *
	 * ## EXAMPLES
	 *
	 *     wp multisite-shared-blocks rebuild-index
	 *
	 * @subcommand rebuild-index
	 */
	public function rebuild_index( $args, $assoc_args ) {
		$ptypes = Helpers::get_supported_post_types();
		if ( empty( $ptypes ) ) {
			\WP_CLI::error( 'No supported post types are registered for this site.' );
		}

		$query = new \WP_Query(
			[
				'post_type'      => $ptypes,
				'post_status'    => 'publish',
				'posts_per_page' => - 1,
				'no_found_rows'  => true,
			]
		);

		if ( ! $query->have_posts() ) {
			\WP_CLI::error( 'Found no posts to index.' );
		}

		$progress = make_progress_bar( 'Indexing shared blocks', $query->found_posts );
		foreach ( $query->posts as $p ) {
			PostUpdater::update( $p );
			$progress->tick();
		}
		$progress->finish();

		\WP_CLI::success( 'Reindex complete.' );
	}
}
