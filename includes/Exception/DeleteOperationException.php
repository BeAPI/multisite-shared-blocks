<?php

namespace Beapi\MultisiteSharedBlocks\Exception;

/**
 * Exception thrown when a delete operation for a share block record fail.
 *
 * @package Beapi\MultisiteSharedBlocks\Exception
 */
final class DeleteOperationException extends OperationException {

	/**
	 * Create a new exception.
	 *
	 * @since 1.0.0
	 *
	 * @param int $record_id the record id being deleted.
	 * @param string $sql_error the SQL error message.
	 *
	 * @return DeleteOperationException
	 */
	public static function make( int $record_id, string $sql_error = '' ): DeleteOperationException {
		$message = sprintf( 'Fail to delete block with record id %d.', $record_id );
		if ( ! empty( $sql_error ) ) {
			$message .= sprintf( ' SQL error: %s', $sql_error );
		}

		return new DeleteOperationException( $message );
	}
}
