/**
 * WordPress dependencies
 */
import { addFilter } from '@wordpress/hooks';

/**
 * Internal dependencies
 */
import registerSharedBlockAttributes from './registerSharedBlockAttributes';
import sharedBlockIdComponent from './sharedBlockIdComponent';
import sharedBlockIdControls from './sharedBlockIdControls';

/**
 * Filters for the automatic unique ID added to every block
 */
addFilter(
	'blocks.registerBlockType',
	'multisite-shared-blocks/shared-block-id-attribute',
	registerSharedBlockAttributes
);
addFilter(
	'editor.BlockEdit',
	'multisite-shared-blocks/shared-block-id',
	sharedBlockIdComponent
);
addFilter(
	'editor.BlockEdit',
	'multisite-shared-blocks/shared-block-id-controls',
	sharedBlockIdControls
);
