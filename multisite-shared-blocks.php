<?php
/**
 * Plugin Name: Multisite Shared Blocks
 * Plugin URI: https://github.com/BeAPI/multisite-shared-blocks
 * Description: Share blocks between network's sites.
 * Version: 1.1.1
 * Requires at least: 5.9
 * Requires PHP: 7.2
 * Author: Be API Technical team
 * Author URI: https://beapi.fr
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: multisite-shared-blocks
 * Network: true
 */

namespace Beapi\MultisiteSharedBlocks;

use Beapi\MultisiteSharedBlocks\Admin\Main as AdminMain;
use Beapi\MultisiteSharedBlocks\Blocks\SharedBlock;
use Beapi\MultisiteSharedBlocks\Gutenberg\SharedBlocksAttributes;
use Beapi\MultisiteSharedBlocks\Rest\Rest;

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

// Plugin constants
define( 'MULTISITE_SHARED_BLOCKS_VERSION', '1.1.1' );
define( 'MULTISITE_SHARED_BLOCKS_CACHE_VERSION', '1' );
define( 'MULTISITE_SHARED_BLOCKS_VIEWS_FOLDER_NAME', 'multisite-shared-blocks' );

// Plugin URL and PATH
define( 'MULTISITE_SHARED_BLOCKS_URL', plugin_dir_url( __FILE__ ) );
define( 'MULTISITE_SHARED_BLOCKS_DIR', plugin_dir_path( __FILE__ ) );
define( 'MULTISITE_SHARED_BLOCKS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Plugin only support multisite installation.
if ( ! is_multisite() ) {
	add_action( 'admin_init', __NAMESPACE__ . '\\handle_multisite_support_issue' );

	return;
}

register_activation_hook( __FILE__, [ \Beapi\MultisiteSharedBlocks\Plugin::class, 'activate' ] );

add_action( 'plugins_loaded', __NAMESPACE__ . '\\init' );
/**
 * Init the plugin
 */
function init(): void {

	// Register custom table and check for schema updates
	Database::setup();

	SharedBlocksAttributes::get_instance();
	SharedBlock::get_instance();
	Listener::get_instance();
	Rest::get_instance();
	Preview::get_instance();

	if ( is_admin() ) {
		AdminMain::get_instance();
	}

	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		\WP_CLI::add_command( 'multisite-shared-blocks', Cli::class );
	}
}

/**
 * Handle multisite support issue.
 *
 * Deactivate the plugin and display an admin notices to users if they are able to manage plugins.
 *
 * @return void
 */
function handle_multisite_support_issue(): void {
	if ( wp_doing_ajax() || ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	// Deactivate plugin
	deactivate_plugins( __FILE__ );
	unset( $_GET['activate'] ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended

	// Display admin notices
	add_action(
		'admin_notices',
		function () {
			echo '<div class="notice error">';
			echo sprintf( '<p>%s</p>', esc_html__( 'Multisite Shared Blocks require a multisite installation to work.', 'multisite-shared-blocks' ) );
			echo '</div>';
		}
	);
}
