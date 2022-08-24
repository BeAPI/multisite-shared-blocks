/* global multisiteSharedBlocksHooksData */

/**
 * Check if current block is excluded from sharing.
 *
 * @param {string} blockName
 * @returns {boolean}
 */
export function blockSupportSharing( blockName ) {
	const excludedBlocks = multisiteSharedBlocksHooksData.excluded_blocks ?? [];

	return ! excludedBlocks.includes( blockName );
}

/**
 * Get custom attributes for the sharing functionality.
 *
 * @returns {object}
 */
export function getBlockSharingAttributs() {
	return multisiteSharedBlocksHooksData.attributes || {};
}
