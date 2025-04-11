<?php
/**
 * Handles the drop-in functionality.
 *
 * @package WP_Syntex\Object_Cache_Annihilator
 */

namespace WP_Syntex\Object_Cache_Annihilator;

use WP_Error;
use WP_Object_Cache;
use Object_Cache_Annihilator;

/**
 * Class Dropper
 *
 * Handles the drop-in functionality.
 *
 * @since 0.1.0
 */
class Dropper {
	/**
	 * The source path of the object cache file.
	 *
	 * @since 0.1.0
	 * @var string
	 */
	private $source_file;

	/**
	 * The target path where the object cache file should be copied.
	 *
	 * @var string
	 */
	private $target_file;

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 *
	 * @param string $source_file The source file path.
	 * @param string $target_file The target file path.
	 */
	public function __construct( string $source_file, string $target_file ) {
		$this->source_file = $source_file;
		$this->target_file = $target_file;
	}

	/**
	 * Drops the source file into the target file.
	 *
	 * @since 0.1.0
	 *
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	public function drop() {
		if ( ! is_writable( WP_CONTENT_DIR ) ) {
			return new \WP_Error(
				'not_writable',
				__( 'The wp-content directory is not writable.', 'object-cache-annihilator' )
			);
		}

		if ( file_exists( $this->target_file ) ) {
			if ( ! unlink( $this->target_file ) ) {
				return new \WP_Error(
					'delete_failed',
					__( 'Failed to remove existing object cache file.', 'object-cache-annihilator' )
				);
			}
		}

		if ( ! copy( $this->source_file, $this->target_file ) ) {
			return new \WP_Error(
				'copy_failed',
				__( 'Failed to copy the object cache file.', 'object-cache-annihilator' )
			);
		}

		return true;
	}

	/**
	 * Removes drop-in file.
	 *
	 * @since 0.1.0
	 *
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	public function remove() {
		if ( ! file_exists( $this->target_file ) ) {
			return true;
		}

		if ( ! unlink( $this->target_file ) ) {
			return new \WP_Error(
				'unlink_failed',
				__( 'Failed to remove the object cache file.', 'object-cache-annihilator' )
			);
		}

		return true;
	}
}
