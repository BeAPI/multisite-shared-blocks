<?php
/**
 * Admin page template.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( ! class_exists( \WP_List_Table::class ) ) {
	/** @psalm-suppress UnresolvableInclude */
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

$list = new \Beapi\MultisiteSharedBlocks\Admin\SharedBlocksListTable();
$list->get_pagenum();
$list->prepare_items();
?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php echo esc_html( get_admin_page_title() ); ?></h1>
	<?php
	if ( ! empty( $_GET['s'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		echo '<span class="subtitle">';
		printf(
			/* translators: %s: Search query. */
			esc_html__( 'Search results for: %s', 'default' ),
			'<strong>' . esc_html( (string) $_GET['s'] ) . '</strong>' //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		);
		echo '</span>';
	}
	?>
	<form id="posts-filter" method="get">
		<input type="hidden" name="page" value="multisite-shared-blocks">
		<?php if ( isset( $_GET['on_current_site'] ) ) : //phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<input type="hidden" name="on_current_site" value="1">
		<?php endif; ?>
		<?php
		$list->search_box( __( 'Search', 'multisite-shared-blocks' ), 'shared-block-search' );
		$list->views();
		$list->display();
		?>
	</form>
</div>
