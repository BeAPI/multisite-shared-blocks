/* global multisiteSharedBlocksEditorData */

/**
 * External dependencies
 */
import InfiniteScroll from 'react-infinite-scroll-component';

/**
 * WordPress dependencies
 */
import {
	Button,
	SelectControl,
	Spinner,
	TextControl,
} from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import { __, sprintf } from '@wordpress/i18n';

const SEARCH_ENDPOINT = '/multisite-shared-blocks/v1/search';

/**
 * Do search request.
 *
 * Build and send request to the shared blocks search endpoint.
 *
 * @param {Object} params
 * @param {AbortController} controller
 * @return {Promise<*[]>} Search request promise.
 */
function makeRequest( params, controller ) {
	const path = addQueryArgs( SEARCH_ENDPOINT, {
		search: params.search || '',
		post_type:
			params.postType && '' !== params.postType
				? [ params.postType ]
				: [],
		site_in: params.site && 0 !== params.site ? [ params.site ] : [],
		page: params.page || 1,
		per_page: params.per_page || 30,
	} );

	return apiFetch( { path, signal: controller?.signal } );
}

export default function SharedBlocksSelector( { onItemSelect } ) {
	const DEFAULT_QUERY = {
		search: '',
		postType: '',
		site: 0,
	};

	// Get available values for the "Sites" and "Post Type" filters.
	const { sites: availableSites = [], post_types: availablePostTypes = [] } =
		multisiteSharedBlocksEditorData;

	const availableSitesOptions = Object.keys( availableSites ).map(
		( key ) => {
			return { label: availableSites[ key ], value: key };
		}
	);
	const availablePostTypesOptions = Object.keys( availablePostTypes ).map(
		( key ) => {
			return { label: availablePostTypes[ key ], value: key };
		}
	);

	// Current query parameters.
	const [ query, setQuery ] = useState( DEFAULT_QUERY );

	// List of results.
	const [ results, setResults ] = useState( [] );

	// Flag when the query return no results.
	const [ hasResults, setHasResults ] = useState( true );

	// Current page of results.
	const [ currentPage, setCurrentPage ] = useState( 1 );

	// Flag when we reach the last page of results.
	const [ hasMore, setHasMore ] = useState( false );

	/**
	 * Reset pagination state.
	 */
	const resetPagination = () => {
		setResults( [] );
		setCurrentPage( 1 );
		setHasMore( false );
		setHasResults( true );
	};

	/**
	 * Update the search term param in the query.
	 *
	 * @param {string} term
	 */
	const setSearch = ( term ) => {
		setQuery( { ...query, search: term } );
		resetPagination();
	};

	/**
	 * Update the site id param in the query.
	 *
	 * @param {number} site
	 */
	const setSite = ( site ) => {
		setQuery( { ...query, site } );
		resetPagination();
	};

	/**
	 * Update the post type param in the query.
	 *
	 * @param {string} postType
	 */
	const setPostType = ( postType ) => {
		setQuery( { ...query, postType } );
		resetPagination();
	};

	/**
	 * Refresh the results when the query change.
	 */
	useEffect( () => {
		const controller =
			typeof window.AbortController === 'undefined'
				? undefined
				: new window.AbortController();

		searchBlock( query, controller );

		return () => controller?.abort();
	}, [ query ] );

	/**
	 * Fetch results for the query.
	 *
	 * @param {Object} params
	 * @param {?AbortController} controller
	 */
	const searchBlock = ( params, controller ) => {
		const searchQuery = {
			...params,
			page: currentPage,
		};
		makeRequest( searchQuery, controller )
			.then( ( blocks ) => {
				// Handle case where we don't have results and the request returned no results.
				// In that case we want to display the "No Results" state.
				if ( ! results.length && ! blocks.length ) {
					setHasResults( false );
					setHasMore( false );
					return;
				}

				// Handle case where the request returned new results.
				// In that case new results are merged with the current ones and the current page in increase by one
				// for the next request.
				if ( blocks.length ) {
					setResults( [ ...results, ...blocks ] );
					setCurrentPage( currentPage + 1 );
					setHasResults( true );
					setHasMore( true );
					return;
				}

				// Handle edge case where we already have some results but the request returned no results.
				setHasMore( false );
			} )
			.catch( ( e ) => {
				// The request return an error. Set hasMore state at false to avoid any more requests from infinite scroll
				// component.
				setHasMore( false );

				// Since we don't have access to request headers with the total number of pages allowed for a query we
				// rely on the error code `rest_search_invalid_page_number` to know when we reach the limit.
				// This just mean the server doesn't have any more results for the current query.
				if ( 'rest_search_invalid_page_number' === e.code ) {
					return;
				}

				// For any other errors we reset the results list and show the "No results" state.
				setResults( [] );
				setHasResults( false );
				setCurrentPage( 1 );
			} );
	};

	return (
		<div className={ 'shared-block-selector' }>
			<div className={ 'shared-block-selector__filters' }>
				<div className={ 'filter filter--sites' }>
					<SelectControl
						label={ __(
							'Filter by site',
							'multisite-shared-blocks'
						) }
						value={ query.site }
						options={ [
							{
								value: 0,
								label: __( 'All', 'multisite-shared-blocks' ),
							},
							...availableSitesOptions,
						] }
						onChange={ ( value ) => {
							const site = '0' !== value ? +value : 0;
							setSite( site );
						} }
					/>
				</div>
				<div className={ 'filter filter--posttypes' }>
					<SelectControl
						label={ __(
							'Filter by post type',
							'multisite-shared-blocks'
						) }
						value={ query.postType }
						options={ [
							{
								value: 0,
								label: __( 'All', 'multisite-shared-blocks' ),
							},
							...availablePostTypesOptions,
						] }
						onChange={ ( value ) => {
							const postType = '0' !== value ? value : '';
							setPostType( postType );
						} }
					/>
				</div>
				<TextControl
					label={ __(
						'Search for a shared block',
						'multisite-shared-blocks'
					) }
					className={ 'filter filter--search' }
					onChange={ ( text ) => {
						setSearch( text );
					} }
				/>
			</div>
			<div className={ 'shared-block-selector__results' }>
				{ hasResults ? (
					<InfiniteScroll
						dataLength={ results.length }
						next={ () => searchBlock( query ) }
						hasMore={ hasMore }
						height={ 300 }
						loader={ <Spinner /> }
					>
						{ results.map( ( result ) => {
							return (
								<div
									key={ result.id }
									className={ 'results__item' }
								>
									<Button
										variant={ 'link' }
										label={ sprintf(
											//translators: %s shared block title
											__(
												'Select block "%s"',
												'multisite-shared-blocks'
											),
											result.block_title
										) }
										onClick={ () => {
											onItemSelect( result );
										} }
									>
										{ `${ result.full_block_title }` }
									</Button>
								</div>
							);
						} ) }
					</InfiniteScroll>
				) : (
					<div className={ 'no-results' }>
						{ __( 'No results.', 'multisite-shared-blocks' ) }
					</div>
				) }
			</div>
		</div>
	);
}
