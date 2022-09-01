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
?>
<!DOCTYPE html>
<html>
<head>
	<?php wp_head(); ?>
</head>
<body>
	<?php
	wp_body_open();
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

	echo $output; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	wp_footer();
	?>
</body>
</html>
