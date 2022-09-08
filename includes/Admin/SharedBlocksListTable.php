<?php

namespace Beapi\MultisiteSharedBlocks\Admin;

use Beapi\MultisiteSharedBlocks\Helpers;
use Beapi\MultisiteSharedBlocks\Query;
use Beapi\MultisiteSharedBlocks\SharedBlockDto;

/**
 * Class SharedBlocksListTable is use to render the shared blocks list table.
 *
 * @package Beapi\MultisiteSharedBlocks\Admin
 */
class SharedBlocksListTable extends \WP_List_Table {

	/**
	 * @inheritDoc
	 */
	public function ajax_user_can(): bool {
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function get_columns(): array {
		return [
			'msb_block' => _x( 'Shared Block', 'Column name', 'multisite-shared-blocks' ),
			'msb_post'  => _x( 'From', 'Column name', 'multisite-shared-blocks' ),
			'msb_site'  => _x( 'Site', 'Column name', 'multisite-shared-blocks' ),
		];
	}

	/**
	 * @inheritDoc
	 */
	public function prepare_items(): void {
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
				's'        => isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '',
				'site__in' => isset( $_GET['on_current_site'] ) ? get_current_blog_id() : '',
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
	 * Render shared block column.
	 *
	 * @since 1.0.0
	 *
	 * @param SharedBlockDto $item
	 *
	 * @return void
	 */
	public function column_msb_block( SharedBlockDto $item ): void {
		echo sprintf(
			'<strong>%s</strong> <code>%s</code>',
			esc_html( $item->get_block_title() ),
			esc_html( $item->get_block_id() )
		);
	}

	/**
	 * Render shared block's original post column.
	 *
	 * @since 1.0.0
	 *
	 * @param SharedBlockDto $item
	 *
	 * @return void
	 */
	public function column_msb_post( SharedBlockDto $item ): void {
		$did_switch = false;
		if ( get_current_blog_id() !== $item->get_site_id() ) {
			$did_switch = true;
			switch_to_blog( $item->get_site_id() );
		}

		$post_type_object = get_post_type_object( $item->get_post_type() );
		echo '<strong>';
		echo sprintf(
			'<a href="%s" target="_blank">%s</a>',
			esc_url( get_permalink( $item->get_post_id() ) ),
			esc_html(
				sprintf(
					// translators: 1: the post type, 2: the post title, 3: the post ID
					__( '%1$s &#8220;%2$s&#8221; (ID %3$s)', 'multisite-shared-blocks' ),
					null !== $post_type_object ? $post_type_object->labels->singular_name : __( 'Unknown type', 'multisite-shared-blocks' ),
					$item->get_post_title(),
					$item->get_post_id()
				)
			)
		);
		echo '</strong>';
		if ( $did_switch ) {
			restore_current_blog();
		}
	}

	/**
	 * Render shared block's original site column.
	 *
	 * @since 1.0.0
	 *
	 * @param SharedBlockDto $item
	 *
	 * @return void
	 */
	public function column_msb_site( SharedBlockDto $item ): void {

		echo '<strong>';
		echo sprintf(
			'<a href="%s" target="_blank">%s</a>',
			esc_url( get_site_url( $item->get_site_id() ) ),
			esc_html(
				sprintf(
					// translators: 1: is the site name, 2: is the site ID
					__( 'Site &#8220;%1$s&#8221; (ID %2$s)', 'multisite-shared-blocks' ),
					get_site( $item->get_site_id() )->blogname,
					$item->get_site_id()
				)
			)
		);
		echo '</strong>';
	}

	/**
	 * @inheritDoc
	 */
	protected function get_views(): array {
		//phpcs:disable WordPress.Security.NonceVerification.Recommended
		$all_count          = new Query(
			[
				'fields'        => 'block_ids',
				'nopaging'      => true,
				'no_found_rows' => true,
			]
		);
		$current_site_count = new Query(
			[
				'site__in'      => get_current_blog_id(),
				'fields'        => 'block_ids',
				'nopaging'      => true,
				'no_found_rows' => true,
			]
		);

		$all_text = sprintf(
			// translators: %s is the number of items
			_nx(
				'All <span class="count">(%s)</span>',
				'All <span class="count">(%s)</span>',
				$all_count->get_total(),
				'All blocks view',
				'multisite-shared-blocks'
			),
			number_format_i18n( $all_count->get_total() )
		);

		$current_site_text = sprintf(
			// translators: %s is the number of items
			_nx(
				'Current site <span class="count">(%s)</span>',
				'Current site <span class="count">(%s)</span>',
				$current_site_count->get_total(),
				'Current site blocks view',
				'multisite-shared-blocks'
			),
			number_format_i18n( $current_site_count->get_total() )
		);

		return [
			'all'             => sprintf(
				'<a href="%s"%s>%s</a>',
				add_query_arg(
					[ 'page' => 'multisite-shared-blocks' ],
					admin_url( 'tools.php' )
				),
				! isset( $_GET['on_current_site'] ) ? ' class="current" aria-current="page"' : '',
				$all_text
			),
			'on_current_site' => sprintf(
				'<a href="%s"%s>%s</a>',
				add_query_arg(
					[
						'page'            => 'multisite-shared-blocks',
						'on_current_site' => get_current_blog_id(),
					],
					admin_url( 'tools.php' )
				),
				isset( $_GET['on_current_site'] ) ? ' class="current" aria-current="page"' : '',
				$current_site_text
			),
		];
		//phpcs:enable WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * Generates and display row actions links for the list table.
	 *
	 * @param SharedBlockDto $item The item being acted upon.
	 * @param string $column_name Current column name.
	 * @param string $primary Primary column name.
	 *
	 * @return string The row actions HTML, or an empty string
	 *                if the current column is not the primary column.
	 */
	protected function handle_row_actions( $item, $column_name, $primary ): string {
		$actions = [];

		// Add actions for the primary column
		if ( $primary === $column_name ) {
			$actions['block_preview'] = sprintf(
				'<a href="%s" class="msb-preview thickbox" aria-label="%s">%s</a>',
				esc_url(
					add_query_arg(
						[
							'TB_iframe' => true,
							'width'     => 870,
							'height'    => 520,
						],
						Helpers::get_shared_block_preview_link(
							$item->get_site_id(),
							$item->get_post_id(),
							$item->get_block_id()
						)
					)
				),
				sprintf(
				// translators: %s is the block's title
					__( 'Open a preview for the block &#8220;%s&#8221;', 'multisite-shared-blocks' ),
					$item->get_block_title()
				),
				__( 'Preview', 'multisite-shared-blocks' )
			);

			// Allow administrators to flush request cache for the block.
			if ( current_user_can( 'manage_options' ) ) {
				$actions['block_flush'] = sprintf(
					'<a href="%s" class="msb-flush" aria-label="%s">%s</a>',
					wp_nonce_url(
						add_query_arg(
							[
								'action' => 'msb-flush',
								'block'  => $item->get_id(),
							],
							admin_url()
						),
						sprintf( 'flush_block_%s', $item->get_id() )
					),
					sprintf(
					// translators: %s is the block's title
						__( 'Flush cache for the block &#8220;%s&#8221;', 'multisite-shared-blocks' ),
						$item->get_block_title()
					),
					__( 'Flush cache', 'multisite-shared-blocks' )
				);
			}
		}

		// Add actions for the post column
		if ( 'msb_post' === $column_name && current_user_can_for_blog( $item->get_site_id(), 'edit_post', $item->get_post_id() ) ) {
			$did_switch = false;
			if ( get_current_blog_id() !== $item->get_site_id() ) {
				$did_switch = true;
				switch_to_blog( $item->get_site_id() );
			}

			$actions['msb_post_edit'] = sprintf(
				'<a href="%s" class="msb-edit-post" aria-label="%s">%s</a>',
				get_edit_post_link( $item->get_post_id() ),
				/* translators: %s: Post title. */
				esc_attr( sprintf( __( 'Edit &#8220;%s&#8221;', 'default' ), $item->get_post_title() ) ),
				__( 'Edit', 'default' )
			);

			if ( $did_switch ) {
				restore_current_blog();
			}
		}

		return $this->row_actions( $actions );
	}

	/**
	 * @inheritDoc
	 */
	protected function display_tablenav( $which ): void {
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
