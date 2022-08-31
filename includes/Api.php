<?php

namespace Beapi\MultisiteSharedBlocks;

use Beapi\MultisiteSharedBlocks\Exception\DeleteOperationException;
use Beapi\MultisiteSharedBlocks\Exception\InsertOperationException;
use Beapi\MultisiteSharedBlocks\Exception\UpdateOperationException;

/**
 * Class Api is use for CRUD operations on the shared blocks index table.
 *
 * @package Beapi\MultisiteSharedBlocks
 */
final class Api {

	/**
	 * @var int
	 */
	private $site_id;

	/**
	 * Create an instance for the current site and network ids.
	 *
	 * @since 1.0.0
	 *
	 * @return Api
	 */
	public static function for_current_site(): Api {
		return new Api( get_current_blog_id() );
	}

	/**
	 * Api constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param int $site_id target site id.
	 */
	public function __construct( int $site_id ) {
		$this->site_id = $site_id;
	}

	/**
	 * Get a shared block.
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_id
	 * @param string $block_id
	 *
	 * @global \wpdb $wpdb WordPress database abstraction object.
	 *
	 * @return SharedBlockDto|null
	 */
	public function get( int $post_id, string $block_id ): ?SharedBlockDto {
		global $wpdb;

		$table_name = Database::SHARED_BLOCKS_INDEX_TABLE_NAME;

		//phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		/** @var \stdClass|null $record */
		$record = $wpdb->get_row(
			/** @psalm-suppress TooManyArguments */
			$wpdb->prepare(
				"
				SELECT * from {$wpdb->$table_name}
				WHERE 1=1
				AND site_id = %d
				AND post_id = %d
				AND block_id = %s
				LIMIT 1
				",
				$this->site_id,
				$post_id,
				$block_id
			)
		);
		//phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		return ! empty( $record ) ? SharedBlockDto::from_record( $record ) : null;
	}

	/**
	 * Get a shared block by ID.
	 *
	 * @since 1.0.0
	 *
	 * @param int $record_id the shared block record ID.
	 *
	 * @global \wpdb $wpdb WordPress database abstraction object.
	 *
	 * @return SharedBlockDto|null
	 */
	public function get_by_id( int $record_id ): ?SharedBlockDto {
		global $wpdb;

		$table_name = Database::SHARED_BLOCKS_INDEX_TABLE_NAME;

		//phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		/** @var \stdClass|null $record */
		$record = $wpdb->get_row(
		/** @psalm-suppress TooManyArguments */
			$wpdb->prepare(
				"
				SELECT * from {$wpdb->$table_name}
				WHERE 1=1
				AND id = %d
				LIMIT 1
				",
				$record_id
			)
		);
		//phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		return ! empty( $record ) ? SharedBlockDto::from_record( $record ) : null;
	}

	/**
	 * Get all shared blocks for a post.
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_id
	 *
	 * @global \wpdb $wpdb WordPress database abstraction object.
	 *
	 * @return SharedBlockDto[]
	 */
	public function get_for_post( int $post_id ): array {
		global $wpdb;

		$table_name = Database::SHARED_BLOCKS_INDEX_TABLE_NAME;

		//phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		/** @var list<\stdClass> $records */
		$records = $wpdb->get_results(
			/** @psalm-suppress TooManyArguments */
			$wpdb->prepare(
				"
				SELECT * from {$wpdb->$table_name}
				WHERE 1=1
				AND site_id = %d
				AND post_id = %d
				",
				$this->site_id,
				$post_id
			)
		);
		//phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		$dtos = array_map( [ SharedBlockDto::class, 'from_record' ], $records );

		return array_filter( $dtos );
	}

	/**
	 * Get all distinct site ids in the index.
	 *
	 * @since 1.0.0
	 *
	 * @global \wpdb $wpdb WordPress database abstraction object.
	 *
	 * @return int[]
	 */
	public function list_sites(): array {
		global $wpdb;

		$table_name = Database::SHARED_BLOCKS_INDEX_TABLE_NAME;

		/** @var string[] $site_ids */
		$site_ids = $wpdb->get_col( "SELECT DISTINCT site_id from {$wpdb->$table_name}" ); //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		return array_map( 'absint', $site_ids );
	}

	/**
	 * Get all distinct post types in the index.
	 *
	 * @since 1.0.0
	 *
	 * @global \wpdb $wpdb WordPress database abstraction object.
	 *
	 * @return string[]
	 */
	public function list_post_types(): array {
		global $wpdb;

		$table_name = Database::SHARED_BLOCKS_INDEX_TABLE_NAME;

		$post_types = $wpdb->get_col( "SELECT DISTINCT post_type from {$wpdb->$table_name}" ); //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		/** @var string[] $post_types */
		return array_map( 'sanitize_text_field', $post_types );
	}

	/**
	 * Upsert a shared block.
	 *
	 * @since 1.0.0
	 *
	 * @param SharedBlockDto $shared_block
	 *
	 * @global \wpdb $wpdb WordPress database abstraction object.
	 *
	 * @return void
	 * @throw InsertOperationException|UpdateOperationException
	 */
	public function save( SharedBlockDto $shared_block ): void {
		global $wpdb;

		$table_name = Database::SHARED_BLOCKS_INDEX_TABLE_NAME;

		if ( $shared_block->get_id() > 0 ) {
			$result = $wpdb->update(
				(string) $wpdb->$table_name,
				[
					'post_title'  => $shared_block->get_post_title(),
					'block_title' => $shared_block->get_block_title(),
				],
				[
					'id' => $shared_block->get_id(),
				],
				[
					'%s',
					'%s',
				],
				[
					'%d',
				]
			);
		} else {
			$result = $wpdb->insert(
				(string) $wpdb->$table_name,
				[
					'site_id'     => $shared_block->get_site_id(),
					'post_id'     => $shared_block->get_post_id(),
					'post_type'   => $shared_block->get_post_type(),
					'post_title'  => $shared_block->get_post_title(),
					'block_id'    => $shared_block->get_block_id(),
					'block_title' => $shared_block->get_block_title(),
				],
				[
					'%d',
					'%d',
					'%s',
					'%s',
					'%s',
					'%s',
				]
			);
		}

		if ( false === $result ) {
			if ( 0 === $shared_block->get_id() ) {
				throw InsertOperationException::make( $shared_block, $wpdb->last_error );
			}

			throw UpdateOperationException::make( $shared_block, $wpdb->last_error );
		}
	}

	/**
	 * Delete a shared block.
	 *
	 * @since 1.0.0
	 *
	 * @param int $record_id
	 *
	 * @global \wpdb $wpdb WordPress database abstraction object.
	 *
	 * @return void
	 * @throw DeleteOperationException
	 */
	public function delete( int $record_id ): void {
		global $wpdb;

		$table_name = Database::SHARED_BLOCKS_INDEX_TABLE_NAME;

		$result = $wpdb->delete(
			(string) $wpdb->$table_name,
			[
				'id' => $record_id,
			],
			[
				'%d',
			]
		);

		if ( false === $result ) {
			throw DeleteOperationException::make( $record_id, $wpdb->last_error );
		}
	}
}
