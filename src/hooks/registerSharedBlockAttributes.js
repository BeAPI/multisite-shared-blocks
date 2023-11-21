/**
 * Internal dependencies
 */
import { blockSupportSharing, getBlockSharingAttributes } from './helper';

/**
 * Merge our custom attributes with existing attributes of the block type.
 *
 * Since we need to keep our custom attributes in sync between PHP and JS, we inject them in the page in a custom
 * variable `multisiteSharedBlocksGutenbergData`.
 *
 * @param {Object} settings
 * @param {string} name
 * @return {any} New settings with the custom attributes for sharing.
 */
const registerSharedBlockAttributes = ( settings, name ) => {
	const sharedBlockAttributes = getBlockSharingAttributes();

	if (
		! blockSupportSharing( name ) ||
		Object.keys( sharedBlockAttributes ).length === 0
	) {
		return settings;
	}

	return {
		...settings,
		attributes: {
			...settings.attributes,
			...sharedBlockAttributes,
		},
	};
};

export default registerSharedBlockAttributes;
