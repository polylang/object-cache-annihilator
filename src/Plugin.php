<?php
/**
 * Plugin class.
 *
 * Main plugin class that handles initialization, activation, deactivation,
 * and uninstallation of the object cache annihilator plugin.
 *
 * @package WP_Syntex\Object_Cache_Annihilator
 */

namespace WP_Syntex\Object_Cache_Annihilator;

use WP_Syntex\Object_Cache_Annihilator\Admin;

/**
 * Plugin class.
 *
 * @since 0.1.0
 */
class Plugin {
	/**
	 * Admin instance.
	 *
	 * Handles the admin interface and object cache management operations.
	 *
	 * @since 0.1.0
	 * @var Admin
	 */
	public $admin;

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 */
	public function __construct() {
		$this->admin = new Admin();
	}

	/**
	 * Bootstraps the plugin.
	 *
	 * Initializes the admin interface and sets up the global plugin instance.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function bootstrap() {
		$this->admin->init();

		$GLOBALS['object_cache_annihilator_plugin'] = $this;
	}

	/**
	 * Uninstalls the plugin.
	 *
	 * Removes the object cache drop-in file during plugin uninstallation.
	 * This is a static method as it's called by WordPress during the uninstall process.
	 * 
	 * @global WP_Object_Cache|Object_Cache_Annihilator|null $wp_object_cache
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public static function uninstall() {
		global $wp_object_cache;

		if ( $wp_object_cache instanceof \Object_Cache_Annihilator ) {
			$wp_object_cache->die();
		}

		self::get_dropper()->remove();
	}

	/**
	 * Installs the plugin.
	 *
	 * Sets up the object cache drop-in file during plugin installation.
	 * This is a static method as it's called by WordPress during the activation process.
	 *
	 * @global WP_Object_Cache|Object_Cache_Annihilator|null $wp_object_cache
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public static function install() {
		global $wp_object_cache;

		self::get_dropper()->drop();

		if ( $wp_object_cache instanceof \Object_Cache_Annihilator ) {
			$wp_object_cache->flush();
		}
	}

	/**
	 * Gets a new dropper instance.
	 *
	 * @since 0.1.0
	 *
	 * @return Dropper
	 */
	private static function get_dropper() {
		require_once __DIR__ . '/Dropper.php';
		return new Dropper(
			OBJECT_CACHE_ANNIHILATOR_DIR . '/drop-in.php',
			WP_CONTENT_DIR . '/object-cache.php'
		);
	}
}
