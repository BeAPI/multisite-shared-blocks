<?php
/**
 * Block preview template.
 *
 * @var int $site_id
 * @var int $post_id
 * @var string $block_id
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

use Beapi\MultisiteSharedBlocks\Helpers;

?>
<!DOCTYPE html>
<html>
<head>
	<?php wp_head(); ?>
</head>
<body>
<?php wp_body_open(); ?>
<div class="shared-block-preview" style="padding: 20px;">
	<?php
	$blocks = parse_blocks(
		sprintf(
			'<!-- wp:multisite-shared-blocks/shared-block {"siteId":%s,"postId":%s,"blockId":"%s","blockTitle":""} /-->',
			absint( $site_id ),
			absint( $post_id ),
			sanitize_text_field( $block_id )
		)
	);
	$output = '';

	foreach ( $blocks as $block ) {
		$output .= render_block( $block );
	}

	/**
	 * Ensure Shared block output only contain allowed HTML tags and attributes.
	 *
	 * Since the shortcodes and embed from the original source have already been processed, we temporarily allow
	 * the `iframe` tag in the output.
	 */
	add_filter( 'wp_kses_allowed_html', [ Helpers::class, 'kses_post_iframe_tag' ], 10, 2 );
	echo wp_kses_post( $output );
	remove_filter( 'wp_kses_allowed_html', [ Helpers::class, 'kses_post_iframe_tag' ] );
	?>
</div>
<?php wp_footer(); ?>
</body>
</html>
