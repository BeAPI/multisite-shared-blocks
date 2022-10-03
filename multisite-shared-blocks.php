<?php
/**
 * Plugin Name: Multisite Shared Blocks
 * Plugin URI: https://github.com/BeAPI/multisite-shared-blocks
 * Description: Share blocks between network's sites.
 * Version: 1.0.0
 * Requires at least: 5.9
 * Requires PHP: 7.2
 * Author: Be API Technical team
 * Author URI: https://beapi.fr
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: multisite-shared-blocks
 * Network: true
 */

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

// Plugin constants
define( 'MULTISITE_SHARED_BLOCKS_VERSION', '1.0.0' );
define( 'MULTISITE_SHARED_BLOCKS_CACHE_VERSION', '1' );
define( 'MULTISITE_SHARED_BLOCKS_VIEWS_FOLDER_NAME', 'multisite-shared-blocks' );

// Plugin URL and PATH
define( 'MULTISITE_SHARED_BLOCKS_URL', plugin_dir_url( __FILE__ ) );
define( 'MULTISITE_SHARED_BLOCKS_DIR', plugin_dir_path( __FILE__ ) );
define( 'MULTISITE_SHARED_BLOCKS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

register_activation_hook( __FILE__, [ \Beapi\MultisiteSharedBlocks\Plugin::class, 'activate' ] );

add_action( 'plugins_loaded', 'init_multisite_shared_blocks_plugin' );
/**
 * Init the plugin
 */
function init_multisite_shared_blocks_plugin(): void {

	// Register custom table and check for schema updates
	\Beapi\MultisiteSharedBlocks\Database::setup();

	\Beapi\MultisiteSharedBlocks\Main::get_instance();
	\Beapi\MultisiteSharedBlocks\Gutenberg\SharedBlocksAttributes::get_instance();
	\Beapi\MultisiteSharedBlocks\Blocks\SharedBlock::get_instance();
	\Beapi\MultisiteSharedBlocks\Listener::get_instance();
	\Beapi\MultisiteSharedBlocks\Rest\Rest::get_instance();
	\Beapi\MultisiteSharedBlocks\Preview::get_instance();

	if ( is_admin() ) {
		\Beapi\MultisiteSharedBlocks\Admin\Main::get_instance();
	}

	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		\WP_CLI::add_command( 'multisite-shared-blocks', \Beapi\MultisiteSharedBlocks\Cli::class );
	}
}
