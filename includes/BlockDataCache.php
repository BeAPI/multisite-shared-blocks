<?php

namespace Beapi\MultisiteSharedBlocks;

/**
 * Class BlockDataCache is used to handle caching of shared block render requests.
 *
 * @package Beapi\MultisiteSharedBlocks
 */
final class BlockDataCache {

	/**
	 * Get cached response data for a shared block render request.
	 *
	 * @since 1.0.0
	 *
	 * @param int $site_id ID of the site where the original block exist.
	 * @param int $post_id ID or the post where the original block exist.
	 * @param string $block_id ID of the original block.
	 *
	 * @return array|null The response data if cached or null.
	 */
	public static function get( int $site_id, int $post_id, string $block_id ): ?array {
		$data = get_site_transient( self::cache_key( $site_id, $post_id, $block_id ) );

		return is_array( $data ) ? $data : null;
	}

	/**
	 * Cache response data for a shared block render request.
	 *
	 * @since 1.0.0
	 *
	 * @param int $site_id ID of the site where the original block exist.
	 * @param int $post_id ID or the post where the original block exist.
	 * @param string $block_id ID of the original block.
	 * @param array|null $data Response data to save in cache.
	 *
	 * @return bool True is the data was saved, false otherwise.
	 */
	public static function set( int $site_id, int $post_id, string $block_id, array $data = null ): bool {
		return set_site_transient( self::cache_key( $site_id, $post_id, $block_id ), $data );
	}

	/**
	 * Delete cached response data for a shared block render request.
	 *
	 * @since 1.0.0
	 *
	 * @param int $site_id ID of the site where the original block exist.
	 * @param int $post_id ID or the post where the original block exist.
	 * @param string $block_id ID of the original block.
	 *
	 * @return bool True is the data was deleted, false otherwise.
	 */
	public static function delete( int $site_id, int $post_id, string $block_id ): bool {
		return delete_site_transient( self::cache_key( $site_id, $post_id, $block_id ) );
	}

	/**
	 * Generate the cache key for the object.
	 *
	 * @since 1.0.0
	 *
	 * @param int $site_id ID of the site where the original block exist.
	 * @param int $post_id ID or the post where the original block exist.
	 * @param string $block_id ID of the original block.
	 *
	 * @return string The cache key created from the arguments.
	 */
	private static function cache_key( int $site_id, int $post_id, string $block_id ): string {
		return sprintf(
			'msbc:%s:%d:%d:%s',
			MULTISITE_SHARED_BLOCKS_CACHE_VERSION,
			$site_id,
			$post_id,
			$block_id
		);
	}
}
