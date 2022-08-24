<?php

namespace Beapi\MultisiteSharedBlocks;

/**
 * Class Helpers
 *
 * @package Beapi\MultisiteSharedBlocks
 */
class Helpers {

	/**
	 * Use the trait
	 */
	use Singleton;

	/**
	 * Locate template in the theme or plugin if needed
	 *
	 * @since 1.0.0
	 *
	 * @param string $tpl : the tpl name, add automatically .php at the end of the file
	 *
	 * @return bool|string
	 */
	public static function locate_template( string $tpl ) {
		if ( empty( $tpl ) ) {
			return false;
		}

		$path = apply_filters( 'beapi_helpers_locate_template_templates', [ 'views/' . MULTISITE_SHARED_BLOCKS_VIEWS_FOLDER_NAME . '/' . $tpl . '.php' ], $tpl, __NAMESPACE__ );

		// Locate from the theme
		$located = locate_template( $path, false, false );
		if ( ! empty( $located ) ) {
			return $located;
		}

		// Locate on the files
		if ( is_file( MULTISITE_SHARED_BLOCKS_DIR . 'views/' . $tpl . '.php' ) ) {// Use builtin template
			return ( MULTISITE_SHARED_BLOCKS_DIR . 'views/' . $tpl . '.php' );
		}

		return false;
	}

	/**
	 * Include the template given.
	 *
	 * @since 1.0.0
	 *
	 * @param string $tpl : the template name to load
	 *
	 * @return bool
	 */
	public static function include_template( string $tpl ): bool {
		if ( empty( $tpl ) ) {
			return false;
		}

		$tpl_path = self::locate_template( $tpl );
		if ( false === $tpl_path ) {
			return false;
		}

		include $tpl_path;

		return true;
	}

	/**
	 * Load the template given and return a view to be rendered.
	 *
	 * @since 1.0.0
	 *
	 * @param string $tpl : the template name to load
	 *
	 * @return \Closure|false
	 */
	public static function load_template( string $tpl ) {
		if ( empty( $tpl ) ) {
			return false;
		}

		$tpl_path = self::locate_template( $tpl );
		if ( false === $tpl_path ) {
			return false;
		}

		return static function ( $data ) use ( $tpl_path ) {
			if ( ! is_array( $data ) ) {
				$data = array( 'data' => $data );
			}

			// phpcs:ignore WordPress.PHP.DontExtract.extract_extract
			extract( $data, EXTR_OVERWRITE );
			include $tpl_path;
		};
	}

	/**
	 * Render a view
	 *
	 * @since 1.0.0
	 *
	 * @param string $tpl : the template's name
	 * @param array $data : the template's data
	 */
	public static function render( string $tpl, $data = array() ): void {
		$view = self::load_template( $tpl );
		if ( false !== $view ) {
			$view( $data );
		}
	}

	/**
	 * Get a list of supported post_type.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public static function get_supported_post_types(): array {
		$ptypes = get_post_types(
			[
				'public'       => true,
				'show_in_rest' => true,
			]
		);
		unset( $ptypes['attachment'] );

		return array_values( $ptypes );
	}

	/**
	 * Get action hook name use to print block support styles.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public static function get_block_support_styles_hook_name(): string {
		$action_hook_name = 'wp_footer';
		if ( wp_is_block_theme() ) {
			$action_hook_name = 'wp_head';
		}

		return $action_hook_name;
	}
}
