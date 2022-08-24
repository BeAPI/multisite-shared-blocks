<?php

namespace Beapi\MultisiteSharedBlocks;

use Beapi\MultisiteSharedBlocks\Exception\OperationException;
use Beapi\MultisiteSharedBlocks\Gutenberg\SharedBlocksAttributes;

/**
 * Class PostUpdater is responsible for synchronizing shared blocks from a post in the index table.
 *
 * @package Beapi\MultisiteSharedBlocks
 */
final class PostUpdater {

	/**
	 * Update shared blocks from the post in the index.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_Post $post
	 *
	 * @return void
	 */
	public static function update( \WP_Post $post ):void {
		$post_id = $post->ID;
		$api     = Api::for_current_site();

		$existing_shared_blocks = [];
		foreach ( $api->get_for_post( $post_id ) as $dto ) {
			$existing_shared_blocks[ $dto->get_block_id() ] = $dto;
		}

		$shared_blocks = Parser::parse_shared_blocks_from_post(
			$post,
			SharedBlocksAttributes::get_excluded_block_types()
		);

		// Upsert found blocks
		foreach ( $shared_blocks as $shared_block ) {
			$dto         = $existing_shared_blocks[ $shared_block['block_id'] ] ?? null;
			$block_title = ! empty( $shared_block['block_title'] )
				? $shared_block['block_title']
				: sprintf(
					// translators: %s is the block id
					__( 'Shared block %s', 'multisite-shared-blocks' ),
					$shared_block['block_id']
				);

			if ( null === $dto ) {
				$dto = new SharedBlockDto(
					0,
					get_current_blog_id(),
					$post_id,
					$post->post_type,
					$post->post_title,
					$shared_block['block_id'],
					$block_title
				);
			} else {
				$dto = new SharedBlockDto(
					$dto->get_id(),
					$dto->get_site_id(),
					$dto->get_post_id(),
					$dto->get_post_type(),
					get_the_title( $post ),
					$dto->get_block_id(),
					$block_title
				);
			}

			try {
				$api->save( $dto );
				BlockDataCache::delete(
					$dto->get_site_id(),
					$dto->get_post_id(),
					$dto->get_block_id()
				);
			} catch ( OperationException $e ) {
				self::log_exception( $e, $dto );
			}
		}

		// Delete old blocks
		// Array of all existing block ids in the table for the post before the update.
		$old_blocks = array_keys( $existing_shared_blocks );
		// Array of new block ids found in the post content.
		$new_blocks = wp_list_pluck( $shared_blocks, 'block_id' );
		// An array of existing block ids that are not present in the post content anymore and need to be removed.
		$blocks_to_remove = array_diff( $old_blocks, $new_blocks );

		foreach ( $blocks_to_remove as $block_id ) {
			$dto = $existing_shared_blocks[ $block_id ] ?? null;
			if ( null === $dto ) {
				continue;
			}

			try {
				$api->delete( $dto->get_id() );
				BlockDataCache::delete(
					$dto->get_site_id(),
					$dto->get_post_id(),
					$dto->get_block_id()
				);
			} catch ( OperationException $e ) {
				self::log_exception( $e, $dto );
			}
		}
	}

	/**
	 * Delete all shared blocks from the post in the index.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_Post $post
	 *
	 * @return void
	 */
	public static function delete( \WP_Post $post ): void {
		$post_id = $post->ID;
		$api     = Api::for_current_site();

		foreach ( $api->get_for_post( $post_id ) as $dto ) {
			try {
				$api->delete( $dto->get_id() );
				BlockDataCache::delete(
					$dto->get_site_id(),
					$dto->get_post_id(),
					$dto->get_block_id()
				);
			} catch ( OperationException $e ) {
				self::log_exception( $e, $dto );
			}
		}
	}

	/**
	 * Log exceptions thrown during the update process.
	 *
	 * @since 1.0.0
	 *
	 * @param \Throwable $e
	 * @param SharedBlockDto $dto
	 *
	 * @return void
	 */
	private static function log_exception( \Throwable $e, SharedBlockDto $dto ): void {
		//TODO: Log exceptions.
	}
}
