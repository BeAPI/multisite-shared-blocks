/**
 * External dependencies
 */
import { assign, isEmpty } from 'lodash';

/**
 * Internal dependencies
 */
import { blockSupportSharing, getBlockSharingAttributs } from './helper';

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
	const sharedBlockAttributes = getBlockSharingAttributs();

	if ( ! blockSupportSharing( name ) || isEmpty( sharedBlockAttributes ) ) {
		return settings;
	}

	return assign( {}, settings, {
		attributes: assign( {}, settings.attributes, sharedBlockAttributes ),
	} );
};

export default registerSharedBlockAttributes;
