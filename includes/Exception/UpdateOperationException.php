<?php

namespace Beapi\MultisiteSharedBlocks\Exception;

use Beapi\MultisiteSharedBlocks\SharedBlockDto;

/**
 * Exception thrown when an update operation for a share block record fail.
 *
 * @package Beapi\MultisiteSharedBlocks\Exception
 */
final class UpdateOperationException extends OperationException {

	/**
	 * Create a new exception.
	 *
	 * @since 1.0.0
	 *
	 * @param SharedBlockDto $dto the share block being updated.
	 * @param string $sql_error the SQL error message.
	 *
	 * @return UpdateOperationException
	 */
	public static function make( SharedBlockDto $dto, string $sql_error = '' ): UpdateOperationException {
		$message = sprintf( 'Fail to update block %s for post %d.', $dto->get_block_id(), $dto->get_post_id() );
		if ( ! empty( $sql_error ) ) {
			$message .= sprintf( ' SQL error: %s', $sql_error );
		}

		return new UpdateOperationException( $message );
	}
}
