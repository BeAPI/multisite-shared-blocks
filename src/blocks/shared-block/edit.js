/**
 * WordPress dependencies
 */
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { createInterpolateElement, useState } from '@wordpress/element';
import {
	Button,
	ButtonGroup,
	PanelBody,
	Placeholder,
} from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import SharedBlocksSelector from './sharedBlocksSelector';
import { default as BlockIcon } from './icon';

import './editor.scss';

export default function Edit( { attributes, setAttributes } ) {
	const { blockId, blockTitle, display } = attributes;
	const hasBlockId = blockId && blockId.length > 0 ? true : false;

	const [ isEditing, setIsEditing ] = useState( false );

	const setSharedBlockData = ( sharedBlock ) => {
		setAttributes( {
			siteId: sharedBlock.site_id,
			postId: sharedBlock.post_id,
			blockId: sharedBlock.block_id,
			blockTitle: sharedBlock.full_block_title,
		} );
	};

	const setDisplay = ( mode ) => {
		setAttributes( {
			display: mode,
		} );
	};

	const getPlaceholderLabel = ( currentDisplay ) => {
		return 'full' === currentDisplay ? (
			<p>
				{ createInterpolateElement(
					sprintf(
						// translators: %s is the shared block's title
						__(
							'Shared block <strong>"%s"</strong> will be displayed here.',
							'multisite-shared-blocks'
						),
						blockTitle
					),
					{ strong: <strong /> }
				) }
			</p>
		) : (
			<p>
				{ createInterpolateElement(
					sprintf(
						// translators: %s is the shared block's title
						__(
							'Excerpt for the shared block <strong>"%s"</strong> will be displayed here.',
							'multisite-shared-blocks'
						),
						blockTitle
					),
					{ strong: <strong /> }
				) }
			</p>
		);
	};

	return (
		<div { ...useBlockProps() }>
			<InspectorControls>
				<PanelBody
					title={ __( 'Display options', 'multisite-shared-blocks' ) }
				>
					<ButtonGroup>
						<Button
							onClick={ () => setDisplay( 'full' ) }
							isPressed={ 'full' === display }
						>
							{ __( 'Full content', 'multisite-shared-blocks' ) }
						</Button>
						<Button
							onClick={ () => setDisplay( 'excerpt' ) }
							isPressed={ 'excerpt' === display }
						>
							{ __( 'Excerpt', 'multisite-shared-blocks' ) }
						</Button>
					</ButtonGroup>
				</PanelBody>
			</InspectorControls>
			{ ! hasBlockId || isEditing ? (
				<div className={ 'shared-block-selector-wrapper' }>
					{ hasBlockId && (
						<div
							className={
								'shared-block-selector-wrapper__cancel'
							}
						>
							<div
								className={
									'shared-block-selector-wrapper__cancel--message'
								}
							>
								{ __(
									'Cancel changes and keep current selected block ?',
									'multisite-shared-blocks'
								) }
							</div>
							<Button
								className={
									'shared-block-selector-wrapper__cancel--button'
								}
								variant="secondary"
								isDestructive={ true }
								onClick={ () => setIsEditing( false ) }
							>
								{ __(
									'Cancel changes',
									'multisite-shared-blocks'
								) }
							</Button>
						</div>
					) }
					<SharedBlocksSelector
						onItemSelect={ ( item ) => {
							setSharedBlockData( item );
							setIsEditing( false );
						} }
					/>
				</div>
			) : (
				<Placeholder
					icon={ BlockIcon }
					label={ __( 'Shared Block', 'multisite-shared-blocks' ) }
					className={ 'shared-block-placeholder' }
				>
					{ getPlaceholderLabel( display ) }
					<Button
						variant="primary"
						onClick={ () => setIsEditing( true ) }
					>
						{ __(
							'Choose a new block',
							'multisite-shared-blocks'
						) }
					</Button>
				</Placeholder>
			) }
		</div>
	);
}
