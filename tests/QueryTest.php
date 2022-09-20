<?php

namespace Beapi\MultisiteSharedBlocks\Tests;

use Beapi\MultisiteSharedBlocks\Query;

class QueryTest extends \WP_UnitTestCase_Base {

	public function test_no_request_made_is_empty_params(): void {
		$query = new Query( [] );

		$this->assertEmpty(
			$query->get_request(),
			'Failed to assert that no params generate an empty request'
		);
	}

	/**
	 * @uses \wpdb $wpdb
	 */
	public function test_search_param(): void {
		global $wpdb;

		$query = new Query(
			[
				's' => 'test',
			]
		);
		$sql   = $wpdb->remove_placeholder_escape( $query->get_request() );
		$this->assertStringContainsString(
			"({$wpdb->prefix}shared_blocks_index.post_title LIKE '%test%') OR ({$wpdb->prefix}shared_blocks_index.block_title LIKE '%test%')",
			$sql,
			'Failed to assert SQL query contain correct search conditions (one search term).'
		);

		$query = new Query(
			[
				's' => 'test another',
			]
		);
		$sql   = $wpdb->remove_placeholder_escape( $query->get_request() );
		$this->assertStringContainsString(
			"(({$wpdb->prefix}shared_blocks_index.post_title LIKE '%test%') OR ({$wpdb->prefix}shared_blocks_index.block_title LIKE '%test%')) OR (({$wpdb->prefix}shared_blocks_index.post_title LIKE '%another%') OR ({$wpdb->prefix}shared_blocks_index.block_title LIKE '%another%'))",
			$sql,
			'Failed to assert SQL query contain correct search conditions (multiple search terms).'
		);

		$query = new Query(
			[
				's' => 'about where with',
			]
		);
		$sql   = $wpdb->remove_placeholder_escape( $query->get_request() );
		$this->assertStringContainsString(
			"({$wpdb->prefix}shared_blocks_index.post_title LIKE '%about where with%') OR ({$wpdb->prefix}shared_blocks_index.block_title LIKE '%about where with%')",
			$sql,
			'Failed to assert SQL query contain correct search conditions (stopwords).'
		);

		$query = new Query(
			[
				's' => '',
			]
		);
		$sql   = $wpdb->remove_placeholder_escape( $query->get_request() );
		$this->assertStringNotContainsString(
			"{$wpdb->prefix}shared_blocks_index.post_title",
			$sql,
			"Failed to assert SQL query doesn't contain reference to post_title if no search terms"
		);
		$this->assertStringNotContainsString(
			"{$wpdb->prefix}shared_blocks_index.block_title",
			$sql,
			"Failed to assert SQL query doesn't contain reference to block_title if no search terms"
		);
	}

	/**
	 * @uses \wpdb $wpdb
	 */
	public function test_site__in_param(): void {
		global $wpdb;

		$query = new Query(
			[
				'site__in' => [ 1, 2 ],
			]
		);
		$sql   = $wpdb->remove_placeholder_escape( $query->get_request() );
		$this->assertStringContainsString(
			"{$wpdb->prefix}shared_blocks_index.site_id IN ('1', '2')",
			$sql,
			'Failed to assert SQL query contain correct site__in conditions (array of site ids).'
		);

		$query = new Query(
			[
				'site__in' => 3,
			]
		);
		$sql   = $wpdb->remove_placeholder_escape( $query->get_request() );
		$this->assertStringContainsString(
			"{$wpdb->prefix}shared_blocks_index.site_id IN ('3')",
			$sql,
			'Failed to assert SQL query contain correct site__in conditions (single site id).'
		);

		$query = new Query(
			[
				'site__in' => '',
			]
		);
		$sql   = $wpdb->remove_placeholder_escape( $query->get_request() );
		$this->assertStringNotContainsString(
			"{$wpdb->prefix}shared_blocks_index.site_id IN",
			$sql,
			'Failed to assert SQL '
		);
	}
}
