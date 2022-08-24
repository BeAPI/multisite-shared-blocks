<?php

namespace Beapi\MultisiteSharedBlocks;

/**
 * Class Singleton
 * @package Beapi\MultisiteSharedBlocks
 */
trait Singleton {

	/**
	 * @var static
	 */
	protected static $instance;

	/**
	 * Get singleton instance.
	 *
	 * If an instance doesn't already exist, it'll be created and its init method will be invoked.
	 *
	 * @since 1.0.0
	 *
	 * @return static
	 */
	final public static function get_instance() {
		/** @psalm-suppress RedundantPropertyInitializationCheck, UnsafeInstantiation */
		// phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.Found
		return static::$instance ?? static::$instance = new static();
	}

	/**
	 * Constructor protected from the outside
	 */
	private function __construct() {
		$this->init();
	}

	/**
	 * Add init function by default
	 * Implement this method in your child class
	 * If you want to have actions send at construct
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function init() : void {
	}

	/**
	 * Prevent the instance from being cloned
	 *
	 * @since 1.0.0
	 *
	 * @throws \LogicException
	 */
	final public function __clone() {
		throw new \LogicException( 'A singleton must not be cloned!' );
	}

	/**
	 * Prevent from being serialized
	 *
	 * @since 1.0.0
	 *
	 * @throws \LogicException
	 */
	final public function __sleep() {
		throw new \LogicException( 'A singleton must not be serialized!' );
	}

	/**
	 * Prevent from being unserialized
	 *
	 * @since 1.0.0
	 *
	 * @throws \LogicException
	 */
	final public function __wakeup() {
		throw new \LogicException( 'A singleton must not be unserialized!' );
	}

	/**
	 * Destroy singleton instance
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	final public static function destroy(): void {
		static::$instance = null;
	}
}
