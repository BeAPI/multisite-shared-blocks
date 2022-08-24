<?php

namespace Beapi\MultisiteSharedBlocks\Exception;

use Beapi\MultisiteSharedBlocks\SharedBlockDto;

/**
 * Exception thrown when an insert operation for a share block record fail.
 *
 * @package Beapi\MultisiteSharedBlocks\Exception
 */
final class InsertOperationException extends OperationException {

	/**
	 * Create a new exception.
	 *
	 * @since 1.0.0
	 *
	 * @param SharedBlockDto $dto the share block being inserted.
	 * @param string $sql_error the SQL error message.
	 *
	 * @return InsertOperationException
	 */
	public static function make( SharedBlockDto $dto, string $sql_error = '' ): InsertOperationException {
		$message = sprintf( 'Fail to insert block %s for post %d.', $dto->get_block_id(), $dto->get_post_id() );
		if ( ! empty( $sql_error ) ) {
			$message .= sprintf( ' SQL error: %s', $sql_error );
		}

		return new InsertOperationException( $message );
	}
}
