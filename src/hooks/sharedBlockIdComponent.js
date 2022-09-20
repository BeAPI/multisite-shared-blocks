/**
 * External dependencies
 */
import { v4 as uuidv4 } from 'uuid';

/**
 * WordPress dependencies
 */
import { Component } from '@wordpress/element';
import { createHigherOrderComponent } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import { blockSupportSharing } from './helper';

/**
 * Custom wrapper component use to set the sharedBlockId for every block.
 */
const sharedBlockIdComponent = createHigherOrderComponent( ( BlockEdit ) => {
	return class extends Component {
		componentDidMount() {
			const {
				name,
				setAttributes,
				attributes: { sharedBlockId } = false,
			} = this.props;
			if ( blockSupportSharing( name ) && ! sharedBlockId ) {
				setAttributes( { sharedBlockId: uuidv4() } );
			}
		}

		render() {
			return <BlockEdit { ...this.props } />;
		}
	};
}, 'sharedBlockIdComponent' );

export default sharedBlockIdComponent;
