<?php
/**
 * Admin bar integration for Object Cache Annihilator.
 *
 * This class handles the integration with WordPress admin bar, providing
 * a convenient interface to manage object cache operations. It allows administrators
 * to enable/disable the object cache and flush the cache directly from the admin bar.
 * All actions are nonce-protected and require 'manage_options' capability.
 *
 * @package WP_Syntex\Object_Cache_Annihilator
 * @since 0.1.0
 */

namespace WP_Syntex\Object_Cache_Annihilator;

use Object_Cache_Annihilator;
use WP_Syntex\Object_Cache_Annihilator\Dropper;

/**
 * Class Admin
 *
 * Manages the admin bar integration for object cache operations.
 * 
 * @phpstan-type NoticeArray array{
 *     type: 'success'|'error',
 *     message: string
 * }
 *
 * @since 0.1.0
 */
class Admin {
	/**
	 * Option name for the admin notice.
	 *
	 * @var string
	 */
	const NOTICE_OPTION = 'object_cache_notice';

	/**
	 * Dropper instance.
	 *
	 * @var Dropper
	 */
	private $dropper;

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 */
	public function __construct() {
		$this->dropper = new Dropper(
			OBJECT_CACHE_ANNIHILATOR_DIR . '/drop-in.php',
			WP_CONTENT_DIR . '/object-cache.php'
		);
	}

	/**
	 * Initializes the admin integration.
	 *
	 * Sets up WordPress hooks for the admin bar menu and action handling.
	 *
	 * @since 0.1.0
	 *
	 * @return self This instance.
	 */
	public function init(): self {
		add_action( 'admin_bar_menu', [ $this, 'add_admin_bar_menu' ], 100 );
		add_action( 'admin_init', [ $this, 'handle_actions' ] );
		add_action( 'admin_notices', [ $this, 'display_notice' ] );

		return $this;
	}

	/**
	 * Adds the cache management menu to the admin bar.
	 *
	 * Creates a menu structure in the admin bar that allows users to:
	 * - Enable/disable the object cache
	 * - Flush the cache (when enabled)
	 *
	 * Only users with 'manage_options' capability can see this menu.
	 *
	 * @since 0.1.0
	 *
	 * @global WP_Object_Cache|Object_Cache_Annihilator $wp_object_cache
	 *
	 * @param \WP_Admin_Bar $wp_admin_bar WordPress admin bar object.
	 * @return void
	 */
	public function add_admin_bar_menu( $wp_admin_bar ): void {
		global $wp_object_cache;

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$is_enabled = wp_using_ext_object_cache() && $wp_object_cache instanceof Object_Cache_Annihilator;
		$action     = $is_enabled ? 'disable' : 'enable';
		$title      = $is_enabled ? __( 'Die 🔫', 'object-cache-annihilator' ) : __( 'Resurrect 👻', 'object-cache-annihilator' );

		$wp_admin_bar->add_node(
			[
				'id'    => 'object-cache-annihilator',
				'title' => __( 'Object Cache ☠️', 'object-cache-annihilator' ),
				'href'  => '#',
			]
		);

		$wp_admin_bar->add_node(
			[
				'id'     => 'object-cache-annihilator-toggle',
				'parent' => 'object-cache-annihilator',
				'title'  => $title,
				'href'   => wp_nonce_url( admin_url( 'admin.php?action=object_cache_' . $action ), 'object_cache_' . $action ),
			]
		);

		if ( $is_enabled ) {
			$wp_admin_bar->add_node(
				[
					'id'     => 'object-cache-annihilator-flush',
					'parent' => 'object-cache-annihilator',
					'title'  => __( 'Flush 🚽', 'object-cache-annihilator' ),
					'href'   => wp_nonce_url( admin_url( 'admin.php?action=object_cache_flush' ), 'object_cache_flush' ),
				]
			);
		}
	}

	/**
	 * Handles the admin actions for object cache management.
	 * After processing, redirects back to the previous page with a status message.
	 *
	 * @since 0.1.0
	 *
	 * @global WP_Object_Cache|Object_Cache_Annihilator $wp_object_cache
	 * 
	 * @return never
	 */
	public function handle_actions() {
		global $wp_object_cache;

		if ( ! current_user_can( 'manage_options' ) ) {
			$this->set_notice( 'error', __( 'You do not have sufficient permissions to access this page.', 'object-cache-annihilator' ) );
			wp_safe_redirect( admin_url() );
			exit;
		}

		$action = isset( $_GET['action'] ) ? $_GET['action'] : '';
		if ( ! in_array( $action, [ 'object_cache_enable', 'object_cache_disable', 'object_cache_flush' ], true ) ) {
			$this->set_notice( 'error', __( 'Invalid action.', 'object-cache-annihilator' ) );
			wp_safe_redirect( admin_url() );
			exit;
		}

		check_admin_referer( $action );

		$redirect_url   = wp_get_referer() ?: admin_url();
		$notice_type    = 'success';
		$notice_message = '';

		switch ( $action ) {
			case 'object_cache_enable':
				$this->dropper->drop();
				Object_Cache_Annihilator::instance()->resurrect();
				if ( $wp_object_cache instanceof Object_Cache_Annihilator ) {
					$notice_message = __( 'Object cache enabled successfully.', 'object-cache-annihilator' );
				} else {
					$notice_type    = 'error';
					$notice_message = __( 'Failed to enable object cache.', 'object-cache-annihilator' );
				}
				break;

			case 'object_cache_disable':
				Object_Cache_Annihilator::instance()->die();
				$this->dropper->remove();
				if ( ! $wp_object_cache instanceof Object_Cache_Annihilator ) {
					$notice_message = __( 'Object cache disabled successfully.', 'object-cache-annihilator' );
				} else {
					$notice_type    = 'error';
					$notice_message = __( 'Failed to disable object cache.', 'object-cache-annihilator' );
				}
				break;

			case 'object_cache_flush':
				if ( Object_Cache_Annihilator::instance()->flush() ) {
					$notice_message = __( 'Object cache flushed successfully.', 'object-cache-annihilator' );
				} else {
					$notice_type    = 'error';
					$notice_message = __( 'Failed to flush object cache.', 'object-cache-annihilator' );
				}
				break;
			default:
				$notice_type    = 'error';
				$notice_message = __( 'Invalid action.', 'object-cache-annihilator' );
				break;
		}//end switch

		if ( $notice_message ) {
			$this->set_notice( $notice_type, $notice_message );
		}

		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Displays the admin notice for cache operations.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function display_notice(): void {
		/** @var NoticeArray|false $notice */
		$notice = get_option( self::NOTICE_OPTION );
		if ( ! $notice ) {
			return;
		}

		delete_option( self::NOTICE_OPTION );

		if ( ! is_array( $notice ) || ! isset( $notice['type'] ) || ! isset( $notice['message'] ) ) {
			return;
		}
		
		$class   = 'notice notice-' . esc_attr( $notice['type'] );
		$message = wp_kses( $notice['message'], [] );

		printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );
	}

	/**
	 * Sets the admin notice.
	 *
	 * @since 0.1.0
	 *
	 * @param string $type    The type of notice.
	 * @param string $message The message to display.
	 * @return bool True if the notice was set, false otherwise.
	 */
	private function set_notice( string $type, string $message ): bool {
		return update_option(
			self::NOTICE_OPTION,
			[
				'type'    => $type,
				'message' => $message,
			]
		);
	}
}
