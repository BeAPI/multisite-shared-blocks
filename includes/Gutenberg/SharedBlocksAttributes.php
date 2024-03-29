<?php

namespace Beapi\MultisiteSharedBlocks\Gutenberg;

use Beapi\MultisiteSharedBlocks\Singleton;

/**
 * Class SharedBlocksAttributes is responsible for the integration of the shared blocks functionality in the Gutenberg
 * editor.
 *
 * @package Beapi\MultisiteSharedBlocks\Gutenberg
 */
final class SharedBlocksAttributes {

	use Singleton;

	private const SHARED_BLOCK_SCRIPT_HANDLE = 'multisite-shared-blocks-gutenberg';

	/**
	 * @inheritDoc
	 */
	protected function init(): void {
		add_filter( 'register_block_type_args', [ $this, 'register_block_type_args' ], 10, 2 );
		add_action( 'init', [ $this, 'register_editor_assets' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_editor_assets' ], 1 );
	}

	/**
	 * Get a list of block types excluded from sharing.
	 *
	 * @since 1.0.0
	 *
	 * @return string[]
	 */
	public static function get_excluded_block_types(): array {
		/**
		 * Filter block types that can't be shared.
		 *
		 * @since 1.0.0
		 *
		 * @param string[] $excluded_block_types
		 */
		$excluded_block_types   = apply_filters( 'multisite_shared_block_excluded_block_types', [] );
		$excluded_block_types[] = 'multisite-shared-blocks/shared-block';

		return $excluded_block_types;
	}

	/**
	 * Inject our custom attributes in supported block types.
	 *
	 * This will run for all block type created using `register_block_type` function or
	 * `WP_Block_Type_Registry::register` method.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args block type's args.
	 * @param string $name block type's name.
	 *
	 * @return array
	 */
	public function register_block_type_args( array $args, string $name ): array {
		if ( in_array( $name, self::get_excluded_block_types(), true ) ) {
			return $args;
		}

		$block_attributes   = $args['attributes'] ?? [];
		$block_attributes   = wp_parse_args( $block_attributes, $this->get_shared_blocks_attributes() );
		$args['attributes'] = $block_attributes;

		return $args;
	}

	/**
	 * Register gutenberg editor script to support shared block functionalities.
	 *
	 * The editor script is responsible for three tasks :
	 *  - inject our custom attributes in block types where missing (for block types register via JS),
	 *  - wrap all blocks in a component to generate identifier for each blocks to support sharing,
	 *  - render custom controls to activate sharing options for each block.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_editor_assets(): void {
		$asset_data_path = MULTISITE_SHARED_BLOCKS_DIR . 'build/hooks/index.asset.php';
		if ( ! is_readable( $asset_data_path ) ) {
			return;
		}

		// Register the script.
		$data = include $asset_data_path;
		wp_register_script(
			self::SHARED_BLOCK_SCRIPT_HANDLE,
			MULTISITE_SHARED_BLOCKS_URL . 'build/hooks/index.js',
			$data['dependencies'],
			$data['version'],
			true
		);

		// Load translations.
		wp_set_script_translations( self::SHARED_BLOCK_SCRIPT_HANDLE, 'multisite-shared-blocks' );

		// Register additional configuration data for the script to be print before the script tag.
		wp_add_inline_script(
			self::SHARED_BLOCK_SCRIPT_HANDLE,
			sprintf(
				'var multisiteSharedBlocksHooksData = %s',
				wp_json_encode(
					[
						'attributes'      => $this->get_shared_blocks_attributes(),
						'excluded_blocks' => self::get_excluded_block_types(),
					]
				)
			),
			'before'
		);
	}

	/**
	 * Enqueue gutenberg editor script.
	 *
	 * @since 1.0.0
	 *
	 * @see SharedBlocksAttributes::register_editor_assets()
	 *
	 * @return void
	 */
	public function enqueue_editor_assets(): void {
		wp_enqueue_script( self::SHARED_BLOCK_SCRIPT_HANDLE );
	}

	/**
	 * Get custom attributes for shared blocks.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	private function get_shared_blocks_attributes(): array {
		return [
			'sharedBlockId'         => [
				'type' => 'string',
			],
			'sharedBlockIsShared'   => [
				'type'    => 'boolean',
				'default' => false,
			],
			'sharedBlockShareTitle' => [
				'type' => 'string',
			],
		];
	}
}
