/* global multisiteSharedBlocksHooksData */

/**
 * Check if current block is excluded from sharing.
 *
 * @param {string} blockName
 * @return {boolean} True if the block type support sharing, false otherwise.
 */
export function blockSupportSharing( blockName ) {
	const excludedBlocks = multisiteSharedBlocksHooksData.excluded_blocks ?? [];

	return ! excludedBlocks.includes( blockName );
}

/**
 * Get custom attributes for the sharing functionality.
 *
 * @return {Object} Custom attributes for the sharing functionality.
 */
export function getBlockSharingAttributs() {
	return multisiteSharedBlocksHooksData.attributes || {};
}
