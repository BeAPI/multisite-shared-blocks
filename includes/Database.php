<?php

namespace Beapi\MultisiteSharedBlocks;

/**
 * Class Database is used to register custom table in WPDB and handle table schema creation/update.
 *
 * @package Beapi\MultisiteSharedBlocks
 */
final class Database {

	/**
	 * Shared blocks data table name.
	 */
	public const SHARED_BLOCKS_INDEX_TABLE_NAME = 'shared_blocks_index';

	/**
	 * Schema version option's name.
	 */
	private const SCHEMA_VERSION_OPTION = 'shared_blocks_schema_version';

	/**
	 * Current schema version.
	 */
	private const SCHEMA_VERSION = '1643906950';

	/**
	 * Register custom table and check for schema updates.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function setup(): void {
		self::maybe_register_tables();
		self::maybe_create_tables();
	}

	/**
	 * Register custom table in WPDB if it doesn't already exist.
	 *
	 * @since 1.0.0
	 *
	 * @global \wpdb $wpdb WordPress database abstraction object.
	 *
	 * @return void
	 */
	public static function maybe_register_tables(): void {
		global $wpdb;

		$table_name = self::SHARED_BLOCKS_INDEX_TABLE_NAME;
		if ( empty( $wpdb->$table_name ) ) {
			$wpdb->$table_name        = $wpdb->base_prefix . $table_name;
			$wpdb->ms_global_tables[] = $table_name;
		}
	}

	/**
	 * Check the schema version saved in database and run the upsert process if it doesn't match the current one.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function maybe_create_tables(): void {
		if ( self::SCHEMA_VERSION === self::get_schema_version() ) {
			return;
		}

		self::create_shared_blocks_table();
		update_network_option( 1, self::SCHEMA_VERSION_OPTION, self::SCHEMA_VERSION );
	}

	/**
	 * Get schema version saved in database.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public static function get_schema_version(): string {
		return (string) get_network_option( 1, self::SCHEMA_VERSION_OPTION, '' );
	}

	/**
	 * Create the table use to store shared blocks data.
	 *
	 * This table is shared between all sites of the multisite installation.
	 *
	 * @since 1.0.0
	 *
	 * @global \wpdb $wpdb WordPress database abstraction object.
	 *
	 * @return void
	 */
	private static function create_shared_blocks_table(): void {
		global $wpdb;

		$table_name = self::SHARED_BLOCKS_INDEX_TABLE_NAME;

		$schema = "CREATE TABLE {$wpdb->$table_name} (
            `id` BIGINT(20) NOT NULL auto_increment,
            `site_id` BIGINT(20) NOT NULL,
            `post_id` BIGINT(20) NOT NULL,
            `post_type` VARCHAR(20) NOT NULL,
            `post_title` TEXT NOT NULL,
            `block_id` VARCHAR(50) NOT NULL,
            `block_title` VARCHAR(200) NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY `unicity_key` (`site_id`,`post_id`,`block_id`),
            KEY `post_type` (`post_type`)
        );";

		if ( ! function_exists( 'dbDelta' ) ) {
			/** @psalm-suppress all */
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		}

		// Ensure we always try to create this, regardless of whether we're on the main site or not. dbDelta will skip
		// creation of global tables on non-main sites.
		$offset = array_search( self::SHARED_BLOCKS_INDEX_TABLE_NAME, $wpdb->ms_global_tables, true );
		if ( ! empty( $offset ) ) {
			unset( $wpdb->ms_global_tables[ $offset ] );
		}

		dbDelta( $schema );

		$wpdb->ms_global_tables[] = self::SHARED_BLOCKS_INDEX_TABLE_NAME;
	}
}
