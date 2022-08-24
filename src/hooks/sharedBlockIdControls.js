/**
 * External dependencies
 */
import { isEmpty } from 'lodash';

/**
 * WordPress dependencies
 */
import { createHigherOrderComponent } from '@wordpress/compose';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl } from '@wordpress/components';
import { share } from '@wordpress/icons';
import { count } from '@wordpress/wordcount';
import { __, sprintf } from '@wordpress/i18n';
import { createInterpolateElement } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { blockSupportSharing } from './helper';

/**
 * Custom wrapper component use to render the UI for sharing a block.
 */
const sharedBlockIdControls = createHigherOrderComponent( ( BlockEdit ) => {
	return ( props ) => {
		const { name, attributes, setAttributes } = props;
		const { sharedBlockId, sharedBlockIsShared, sharedBlockShareTitle } =
			attributes;

		const getEmptySharedBlockTitle = () => {
			return sprintf(
				// translators: %s share block unique id
				__( 'Shared block %s', 'multisite-shared-blocks' ),
				sharedBlockId
			);
		};

		const setIsShared = ( newStatus ) => {
			setAttributes( { sharedBlockIsShared: newStatus } );
		};

		const setTitle = ( newTitle ) => {
			setAttributes( { sharedBlockShareTitle: newTitle } );
		};

		const getTitleCharCount = ( title ) => {
			if ( isEmpty( title ) ) {
				return '0';
			}

			return count(
				sharedBlockShareTitle,
				'characters_including_spaces',
				{}
			);
		};

		if ( ! blockSupportSharing( name ) ) {
			return <BlockEdit { ...props } />;
		}

		return (
			<>
				<BlockEdit { ...props } />
				<InspectorControls>
					<PanelBody
						title={ __(
							'Multisite sharing options',
							'multisite-shared-blocks'
						) }
						icon={ share }
					>
						<ToggleControl
							label={ __(
								'Share the block on the network',
								'multisite-shared-blocks'
							) }
							help={ __(
								'This block will be available on all sites of the network.',
								'multisite-shared-blocks'
							) }
							checked={ sharedBlockIsShared }
							onChange={ () => {
								setIsShared( ! sharedBlockIsShared );
							} }
						/>
						{ !! sharedBlockIsShared && (
							<>
								<TextControl
									label={ sprintf(
										// translators: %d is the current number of characters of the title
										__(
											'Public title for the shared block (%d/200)',
											'multisite-shared-blocks'
										),
										getTitleCharCount(
											sharedBlockShareTitle
										)
									) }
									value={ sharedBlockShareTitle }
									onChange={ ( value ) => setTitle( value ) }
									help={ __(
										'Try to choose a title as descriptive as possible that will allow other users to understand what the block is about (200 characteres max).',
										'multisite-shared-blocks'
									) }
								/>
								{ isEmpty( sharedBlockShareTitle ) && (
									<p>
										<small>
											{ createInterpolateElement(
												sprintf(
													// translators: %s is default title
													__(
														'Current title for the shared block is empty, it\'ll be view has <strong>"%s"</strong>',
														'multisite-shared-blocks'
													),
													getEmptySharedBlockTitle()
												),
												{ strong: <strong /> }
											) }
										</small>
									</p>
								) }
							</>
						) }
					</PanelBody>
				</InspectorControls>
			</>
		);
	};
}, 'sharedBlockIdControls' );

export default sharedBlockIdControls;
