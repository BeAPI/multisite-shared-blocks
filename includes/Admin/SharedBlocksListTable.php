<?php

namespace Beapi\MultisiteSharedBlocks\Admin;

use Beapi\MultisiteSharedBlocks\Query;
use Beapi\MultisiteSharedBlocks\SharedBlockDto;

class SharedBlocksListTable extends \WP_List_Table {

	public function ajax_user_can() {
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function get_columns() {
		return [
			'block' => _x( 'Block', 'Column name', 'multisite-shared-blocks' ),
			'post'  => _x( 'Post', 'Column name', 'multisite-shared-blocks' ),
			'site'  => _x( 'Site', 'Column name', 'multisite-shared-blocks' ),
		];
	}

	/**
	 * @inheritDoc
	 */
	public function prepare_items() {
		$this->_column_headers = [
			apply_filters( "manage_{$this->screen->id}_columns", $this->get_columns() ),
			[],
			[],
		];

		$per_page = 20;

		//phpcs:disable WordPress.Security.NonceVerification.Recommended
		$query = new Query(
			[
				'per_page' => $per_page,
				's'        => $_GET['s'] ?? '',
				'site__in' => isset( $_GET['current_site'] ) ? get_current_blog_id() : '',
			]
		);
		//phpcs:enable WordPress.Security.NonceVerification.Recommended

		$this->set_pagination_args(
			[
				'total_items' => $query->get_total(),
				'total_pages' => ceil( $query->get_total() / $per_page ),
				'per_page'    => $per_page,
			]
		);

		$this->items = array_map( [ SharedBlockDto::class, 'from_record' ], $query->get_results() );
	}

	/**
	 * Render block column
	 *
	 * @param SharedBlockDto $item
	 *
	 * @return void
	 */
	public function column_block( $item ) {

		echo '<strong>';
		echo esc_html( $item->get_block_title() );
		echo '</strong>';
	}

	/**
	 * Render post column
	 *
	 * @param SharedBlockDto $item
	 *
	 * @return void
	 */
	public function column_post( $item ) {
		$did_switch = false;
		if ( get_current_blog_id() !== $item->get_site_id() ) {
			$did_switch = true;
			switch_to_blog( $item->get_site_id() );
		}
		echo '<strong>';
		echo sprintf(
			'<a href="%s">%s</a>',
			esc_url( get_permalink( $item->get_post_id() ) ),
			esc_html( get_the_title( $item->get_post_id() ) )
		);
		echo '</strong>';
		if ( $did_switch ) {
			restore_current_blog();
		}
	}

	/**
	 * Render site column
	 *
	 * @param SharedBlockDto $item
	 *
	 * @return void
	 */
	public function column_site( $item ) {

		echo '<strong>';
		echo sprintf(
			'<a href="%s" target="_blank">%s</a>',
			esc_url( get_site_url( $item->get_site_id() ) ),
			esc_html( get_site( $item->get_site_id() )->blogname )
		);
		echo '</strong>';
	}

	/**
	 * @inheritDoc
	 */
	protected function get_views() {

		//phpcs:disable WordPress.Security.NonceVerification.Recommended
		return [
			'all'          => sprintf(
				'<a href="%s"%s>%s</a>',
				add_query_arg(
					[ 'page' => 'multisite-shared-blocks' ],
					admin_url( 'admin.php' )
				),
				! isset( $_GET['current_site'] ) ? ' class="current" aria-current="page"' : '',
				__( 'All', 'multisite-shared-blocks' )
			),
			'current_site' => sprintf(
				'<a href="%s"%s>%s</a>',
				add_query_arg(
					[
						'page'         => 'multisite-shared-blocks',
						'current_site' => 1,
					],
					admin_url( 'admin.php' )
				),
				isset( $_GET['current_site'] ) ? ' class="current" aria-current="page"' : '',
				__( 'Current Site', 'multisite-shared-blocks' )
			),
		];
		//phpcs:enable WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * @inheritDoc
	 */
	protected function get_bulk_actions() {
		return [];
	}

	/**
	 * Generates and display row actions links for the list table.
	 *
	 * @param \WP_Post $post The item being acted upon.
	 * @param string $column_name Current column name.
	 * @param string $primary Primary column name.
	 *
	 * @return string The row actions HTML, or an empty string
	 *                if the current column is not the primary column.
	 */
	protected function handle_row_actions( $post, $column_name, $primary ) {
		return '';
	}

	/**
	 * @inheritDoc
	 */
	protected function display_tablenav( $which ) {
		?>
		<div class="tablenav <?php echo esc_attr( $which ); ?>">
			<?php
			$this->extra_tablenav( $which );
			$this->pagination( $which );
			?>

			<br class="clear"/>
		</div>
		<?php
	}
}
