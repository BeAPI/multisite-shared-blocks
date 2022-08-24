<?php

namespace Beapi\MultisiteSharedBlocks;

/**
 * Class Parser is used to extract shared blocks from post content.
 *
 * @package Beapi\MultisiteSharedBlocks
 */
final class Parser {

	/**
	 * Extract block from post content using its id.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_Post $post
	 * @param string $block_id
	 * @param array $excluded_blocks list of block's names that are not allowed to be shared.
	 *
	 * @return array|null block's data or null.
	 */
	public static function get_shared_block( \WP_Post $post, string $block_id, array $excluded_blocks = [] ): ?array {
		$parsed_blocks = parse_blocks( $post->post_content );

		return self::match_shared_block( $parsed_blocks, $block_id, $excluded_blocks );
	}

	/**
	 * Extract shared blocks from post.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_Post $post
	 * @param array $excluded_blocks list of block's names that are not allowed to be shared.
	 *
	 * @return array shared blocks
	 */
	public static function parse_shared_blocks_from_post( \WP_Post $post, array $excluded_blocks = [] ): array {
		return self::parse_shared_blocks_from_post_content( $post->post_content, $excluded_blocks );
	}

	/**
	 * Extract shared blocks from post content.
	 *
	 * @since 1.0.0
	 *
	 * @param string $post_content
	 * @param array $excluded_blocks list of block's names that are not allowed to be shared.
	 *
	 * @return array shared blocks
	 */
	public static function parse_shared_blocks_from_post_content( string $post_content, array $excluded_blocks = [] ): array {
		$parsed_blocks = parse_blocks( $post_content );

		return self::find_shared_blocks( $parsed_blocks, $excluded_blocks );
	}

	/**
	 * Extract shared blocks from parsed post content.
	 *
	 * @since 1.0.0
	 *
	 * @param array $parse_blocks parsed blocks data.
	 * @param array $excluded_blocks list of block's names that are not allowed to be shared.
	 *
	 * @return array shared blocks
	 */
	private static function find_shared_blocks( array $parse_blocks, array $excluded_blocks = [] ): array {
		$shared_blocks = [];

		foreach ( $parse_blocks as $block ) {
			if ( empty( $block['blockName'] ) || in_array( $block['blockName'], $excluded_blocks, true ) ) {
				continue;
			}

			$attributes = $block['attrs'] ?? [];

			// Check if the block is "shareable"
			if (
				! empty( $attributes['sharedBlockId'] ) // Ensure block has a share ID.
				&& ( isset( $attributes['sharedBlockIsShared'] ) && true === $attributes['sharedBlockIsShared'] ) // And the share option is active.
			) {
				$shared_blocks[] = [
					'block_id'    => $attributes['sharedBlockId'],
					'block_title' => $attributes['sharedBlockShareTitle'] ?? '',
				];

				// Don't check for shared blocks in the children if the parent is already a shared block.
				continue;
			}

			// Look for shared blocks in the children.
			if ( ! empty( $block['innerBlocks'] ) ) {
				$shared_inner_blocks = self::find_shared_blocks( $block['innerBlocks'], $excluded_blocks );
				$shared_blocks       = array_merge( $shared_blocks, $shared_inner_blocks );
			}
		}

		return $shared_blocks;
	}

	/**
	 * Find a shared block in parsed post content.
	 *
	 * @since 1.0.0
	 *
	 * @param array $parsed_blocks
	 * @param string $block_id
	 * @param array $excluded_blocks
	 *
	 * @return array|null
	 */
	private static function match_shared_block( array $parsed_blocks, string $block_id, array $excluded_blocks = [] ): ?array {

		foreach ( $parsed_blocks as $block ) {
			if ( empty( $block['blockName'] ) || in_array( $block['blockName'], $excluded_blocks, true ) ) {
				continue;
			}

			$attributes = $block['attrs'] ?? [];

			// Find block matching the id and with shared option activated
			if (
				( ! empty( $attributes['sharedBlockId'] ) && $block_id === $attributes['sharedBlockId'] )
				&& ( ! empty( $attributes['sharedBlockIsShared'] ) && true === $attributes['sharedBlockIsShared'] )
			) {
				return $block;
			}

			// Look for shared blocks in the children.
			if ( ! empty( $block['innerBlocks'] ) ) {
				$matched_block = self::match_shared_block( $block['innerBlocks'], $block_id, $excluded_blocks );
				if ( null !== $matched_block ) {
					return $matched_block;
				}
			}
		}

		return null;
	}
}
