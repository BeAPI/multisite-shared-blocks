<?php

namespace Beapi\MultisiteSharedBlocks\Tests;

use Beapi\MultisiteSharedBlocks\Parser;

/**
 * @covers \Beapi\MultisiteSharedBlocks\Parser
 */
class ParserTest extends \WP_UnitTestCase_Base {

	/**
	 * @covers       \Beapi\MultisiteSharedBlocks\Parser::parse_shared_blocks_from_post_content
	 * @covers       \Beapi\MultisiteSharedBlocks\Parser::find_shared_blocks
	 *
	 * @dataProvider data_parse_shared_blocks_from_post_content
	 */
	public function test_parse_shared_blocks_from_post_content( array $shared_blocks, string $post_content, array $excluded_blocks = [] ) {
		$this->assertSame(
			$shared_blocks,
			Parser::parse_shared_blocks_from_post_content( $post_content, $excluded_blocks )
		);
	}

	/**
	 * @covers       \Beapi\MultisiteSharedBlocks\Parser::get_shared_block
	 * @covers       \Beapi\MultisiteSharedBlocks\Parser::match_shared_block
	 */
	public function test_find_a_shared_block_in_post_content() {
		$post_data = (object) [
			'post_content' => '<!-- wp:paragraph {"sharedBlockId":"87c748c8-9a97-4b52-9ba6-e6c7670b62f4"} -->
				<p>Adipiscing at in tellus integer feugiat scelerisque varius morbi.</p>
				<!-- /wp:paragraph -->
				
				<!-- wp:paragraph {"sharedBlockId":"cc38bbdf-f161-459e-8242-b91cd9a5b73f","sharedBlockIsShared":true} -->
				<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
				<!-- /wp:paragraph -->',
		];
		$_post     = new \WP_Post( $post_data );

		$found_block = Parser::get_shared_block( $_post, 'cc38bbdf-f161-459e-8242-b91cd9a5b73f' );
		$this->assertNotEmpty( $found_block, 'parser found a shared block matching the ID' );
		$this->assertEquals( 'core/paragraph', $found_block['blockName'], 'shared block is a paragraph' );
		$this->assertSameSets(
			[
				'sharedBlockId'       => 'cc38bbdf-f161-459e-8242-b91cd9a5b73f',
				'sharedBlockIsShared' => true,
			],
			$found_block['attrs'],
			'shared block has the correct attributes'
		);
	}

	/**
	 * @covers       \Beapi\MultisiteSharedBlocks\Parser::get_shared_block
	 * @covers       \Beapi\MultisiteSharedBlocks\Parser::match_shared_block
	 *
	 * @dataProvider data_non_shared_blocks_should_be_ignore_during_a_match
	 */
	public function test_non_shared_blocks_should_be_ignore_during_a_match( $shared_block_id, $post_content ) {
		$post_data = (object) [ 'post_content' => $post_content ];
		$_post     = new \WP_Post( $post_data );

		$found_block = Parser::get_shared_block( $_post, $shared_block_id );
		$this->assertEmpty( $found_block );
	}

	/**
	 * @covers       \Beapi\MultisiteSharedBlocks\Parser::get_shared_block
	 * @covers       \Beapi\MultisiteSharedBlocks\Parser::match_shared_block
	 */
	public function test_excluded_blocks_should_be_ignore_during_a_match() {
		$post_data = (object) [
			'post_content' => '<!-- wp:paragraph {"sharedBlockId":"87c748c8-9a97-4b52-9ba6-e6c7670b62f4"} -->
				<p>Adipiscing at in tellus integer feugiat scelerisque varius morbi.</p>
				<!-- /wp:paragraph -->
				
				<!-- wp:paragraph {"sharedBlockId":"cc38bbdf-f161-459e-8242-b91cd9a5b73f","sharedBlockIsShared":true} -->
				<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
				<!-- /wp:paragraph -->',
		];
		$_post     = new \WP_Post( $post_data );

		$found_block = Parser::get_shared_block( $_post, 'cc38bbdf-f161-459e-8242-b91cd9a5b73f', [ 'core/paragraph' ] );
		$this->assertEmpty( $found_block );
	}

	public function data_parse_shared_blocks_from_post_content() {
		return [
			'a single shared block with no title'       => [
				[
					[
						'block_id'    => 'cc38bbdf-f161-459e-8242-b91cd9a5b73f',
						'block_title' => '',
					],
				],
				'<!-- wp:paragraph {"sharedBlockId":"87c748c8-9a97-4b52-9ba6-e6c7670b62f4"} -->
				<p>Adipiscing at in tellus integer feugiat scelerisque varius morbi.</p>
				<!-- /wp:paragraph -->
				
				<!-- wp:paragraph {"sharedBlockId":"cc38bbdf-f161-459e-8242-b91cd9a5b73f","sharedBlockIsShared":true} -->
				<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
				<!-- /wp:paragraph -->',
			],
			'a single shared block with an empty title' => [
				[
					[
						'block_id'    => 'cc38bbdf-f161-459e-8242-b91cd9a5b73f',
						'block_title' => '',
					],
				],
				'
			<!-- wp:paragraph {"sharedBlockId":"87c748c8-9a97-4b52-9ba6-e6c7670b62f4"} -->
<p>Adipiscing at in tellus integer feugiat scelerisque varius morbi.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"sharedBlockId":"cc38bbdf-f161-459e-8242-b91cd9a5b73f","sharedBlockIsShared":true,"sharedBlockShareTitle":""} -->
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
<!-- /wp:paragraph -->
			',
			],
			'a single shared block with a custom title' => [
				[
					[
						'block_id'    => 'cc38bbdf-f161-459e-8242-b91cd9a5b73f',
						'block_title' => "Lorem ispum's text block",
					],
				],
				'
				<!-- wp:paragraph {"sharedBlockId":"87c748c8-9a97-4b52-9ba6-e6c7670b62f4"} -->
<p>Adipiscing at in tellus integer feugiat scelerisque varius morbi.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"sharedBlockId":"cc38bbdf-f161-459e-8242-b91cd9a5b73f","sharedBlockIsShared":true,"sharedBlockShareTitle":"Lorem ispum\'s text block"} -->
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
<!-- /wp:paragraph -->
			',
			],
			'multiple shared blocks'                    => [
				[
					[
						'block_id'    => 'cc38bbdf-f161-459e-8242-b91cd9a5b73f',
						'block_title' => "Lorem ispum's text block",
					],
					[
						'block_id'    => '8d7344de-0e8c-4388-ace2-8921dcaa0c79',
						'block_title' => '',
					],
					[
						'block_id'    => '53989ad0-892e-4a31-9fd2-5eaaa1249100',
						'block_title' => "Another lorem ispum's text block",
					],
				],
				'
				<!-- wp:paragraph {"sharedBlockId":"87c748c8-9a97-4b52-9ba6-e6c7670b62f4"} -->
<p>Adipiscing at in tellus integer feugiat scelerisque varius morbi.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"sharedBlockId":"cc38bbdf-f161-459e-8242-b91cd9a5b73f","sharedBlockIsShared":true,"sharedBlockShareTitle":"Lorem ispum\'s text block"} -->
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"sharedBlockId":"8d7344de-0e8c-4388-ace2-8921dcaa0c79","sharedBlockIsShared":true} -->
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"sharedBlockId":"53989ad0-892e-4a31-9fd2-5eaaa1249100","sharedBlockIsShared":true,"sharedBlockShareTitle":"Another lorem ispum\'s text block"} -->
<p>Egestas sed tempus urna et pharetra. Euismod lacinia at quis risus sed vulputate odio. Egestas quis ipsum suspendisse ultrices gravida.</p>
<!-- /wp:paragraph -->
			',
			],
			'a single shared block excluded'            => [
				[],
				'
			<!-- wp:paragraph {"sharedBlockId":"87c748c8-9a97-4b52-9ba6-e6c7670b62f4"} -->
<p>Adipiscing at in tellus integer feugiat scelerisque varius morbi.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"sharedBlockId":"cc38bbdf-f161-459e-8242-b91cd9a5b73f","sharedBlockIsShared":true} -->
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
<!-- /wp:paragraph -->
			',
				[
					'core/paragraph',
				],
			],
			'skip inner blocks of a shared block'       => [
				[
					[
						'block_id'    => 'fad312a6-7c08-4e9b-8e7c-d197112d9e75',
						'block_title' => 'Parent block shared',
					],
					[
						'block_id'    => 'cca3f7ae-3a30-4c93-a026-e8971f1f314a',
						'block_title' => 'Another shared block',
					],
				],
				'
				<!-- wp:group {"sharedBlockId":"fad312a6-7c08-4e9b-8e7c-d197112d9e75","sharedBlockIsShared":true,"sharedBlockShareTitle":"Parent block shared","layout":{"type":"constrained"}} -->
<div class="wp-block-group"><!-- wp:paragraph {"sharedBlockId":"b9162cce-64e3-462f-a682-bf180fad3e24"} -->
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut condimentum nisl ac quam blandit pellentesque.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"sharedBlockId":"2a8f018f-822d-4d73-897a-bab5b283aa50","sharedBlockIsShared":true,"sharedBlockShareTitle":"Inner block shared"} -->
<p>Nulla porttitor sem in laoreet elementum. Maecenas venenatis felis enim, at fringilla dui faucibus nec.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"sharedBlockId":"96588c6a-eea5-4dc1-acd5-f99935f0c741"} -->
<p>Ut sagittis neque arcu, a dapibus sem mattis vel. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:paragraph {"sharedBlockId":"cca3f7ae-3a30-4c93-a026-e8971f1f314a","sharedBlockIsShared":true,"sharedBlockShareTitle":"Another shared block"} -->
<p>Nulla porttitor sem in laoreet elementum. Maecenas venenatis felis enim, at fringilla dui faucibus nec.</p>
<!-- /wp:paragraph -->
				',
			],
		];
	}

	public function data_non_shared_blocks_should_be_ignore_during_a_match() {
		return [
			'non shared block'             => [
				'cc38bbdf-f161-459e-8242-b91cd9a5b73f',
				'<!-- wp:paragraph {"sharedBlockId":"cc38bbdf-f161-459e-8242-b91cd9a5b73f"} -->
				<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
				<!-- /wp:paragraph -->',
			],
			'share attribute set to false' => [
				'cc38bbdf-f161-459e-8242-b91cd9a5b73f',
				'<!-- wp:paragraph {"sharedBlockId":"cc38bbdf-f161-459e-8242-b91cd9a5b73f","sharedBlockIsShared":false} -->
				<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
				<!-- /wp:paragraph -->',
			],
		];
	}
}
