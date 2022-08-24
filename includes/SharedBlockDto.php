<?php

namespace Beapi\MultisiteSharedBlocks;

/**
 * Shared block Dto.
 *
 * @package Beapi\MultisiteSharedBlocks
 */
final class SharedBlockDto {

	/**
	 * @var int
	 */
	private $id;

	/**
	 * @var int
	 */
	private $site_id;

	/**
	 * @var int
	 */
	private $post_id;

	/**
	 * @var string
	 */
	private $post_type;

	/**
	 * @var string
	 */
	private $post_title;

	/**
	 * @var string
	 */
	private $block_id;

	/**
	 * @var string
	 */
	private $block_title;

	/**
	 * Instantiate new Dto from record.
	 *
	 * @since 1.0.0
	 *
	 * @param \stdClass $record
	 *
	 * @return SharedBlockDto
	 */
	public static function from_record( \stdClass $record ): SharedBlockDto {
		return new SharedBlockDto(
			(int) $record->id,
			(int) $record->site_id,
			(int) $record->post_id,
			(string) $record->post_type,
			(string) $record->post_title,
			(string) $record->block_id,
			(string) $record->block_title
		);
	}

	/**
	 * SharedBlockDto constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param int $id
	 * @param int $site_id
	 * @param int $post_id
	 * @param string $post_type
	 * @param string $post_title
	 * @param string $block_id
	 * @param string $block_title
	 */
	public function __construct(
		int $id,
		int $site_id,
		int $post_id,
		string $post_type,
		string $post_title,
		string $block_id,
		string $block_title
	) {
		$this->id          = $id;
		$this->site_id     = $site_id;
		$this->post_id     = $post_id;
		$this->post_type   = $post_type;
		$this->post_title  = $post_title;
		$this->block_id    = $block_id;
		$this->block_title = $block_title;
	}

	/**
	 * Get record ID.
	 *
	 * @since 1.0.0
	 *
	 * @return int
	 */
	public function get_id(): int {
		return $this->id;
	}

	/**
	 * Get site's ID where the source block is.
	 *
	 * @since 1.0.0
	 *
	 * @return int
	 */
	public function get_site_id(): int {
		return $this->site_id;
	}

	/**
	 * Get ID of the post where the source block is.
	 *
	 * @since 1.0.0
	 *
	 * @return int
	 */
	public function get_post_id(): int {
		return $this->post_id;
	}

	/**
	 * Get type of the post where the source block is.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_post_type(): string {
		return $this->post_type;
	}

	/**
	 * Get title of the post where the source block is.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_post_title(): string {
		return $this->post_title;
	}

	/**
	 * Get UUID of the source block.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_block_id(): string {
		return $this->block_id;
	}

	/**
	 * Get custom title of the source block.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_block_title(): string {
		return $this->block_title;
	}

	/**
	 * Get an array representation of the current instance.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function to_array(): array {
		return [
			'id'          => $this->id,
			'site_id'     => $this->site_id,
			'post_id'     => $this->post_id,
			'post_type'   => $this->post_type,
			'post_title'  => $this->post_title,
			'block_id'    => $this->block_id,
			'block_title' => $this->block_title,
		];
	}
}
