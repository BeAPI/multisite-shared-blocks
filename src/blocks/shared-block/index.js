import { registerBlockType } from '@wordpress/blocks';

import './style.scss';

import Edit from './edit';

registerBlockType( 'multisite-shared-blocks/shared-block', {
	/**
	 * @see ./edit.js
	 */
	edit: Edit,
} );
