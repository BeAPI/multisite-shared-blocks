<?php
/**
 * Shared blocks excerpt template.
 *
 * @var array $args {
 *      @type string $block_excerpt
 *      @type string $original_post_title
 *      @type string $original_post_permalink
 * }
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
?>
<p><?php echo esc_html( (string) $args['block_excerpt'] ); ?></p>
<p>
	<a href="<?php echo esc_url( (string) $args['original_post_permalink'] ); ?>">
		<?php esc_html_e( 'View original content', 'multisite-shared-blocks' ); ?>
	</a>
</p>
