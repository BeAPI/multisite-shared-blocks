import { registerBlockType } from '@wordpress/blocks';

import './style.scss';

import Edit from './edit';
import { default as BlockIcon } from './icon';

registerBlockType( 'multisite-shared-blocks/shared-block', {
	icon: BlockIcon,
	edit: Edit,
} );
