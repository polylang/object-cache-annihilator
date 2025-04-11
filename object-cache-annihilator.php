<?php
/**
 * Object Cache Annihilator
 *
 * @package           Object Cache Annihilator
 * @author            WP SYNTEX
 * @license           GPL-3.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Object Cache Annihilator
 * Plugin URI:        https://polylang.pro
 * Description:       A simple file-based object cache implementation for testing purposes.
 * Version:           0.1.0
 * Requires at least: 6.2
 * Requires PHP:      7.2
 * Author:            WP SYNTEX
 * Author URI:        https://polylang.pro
 * Text Domain:       object-cache-annihilator
 * License:           GPL v3 or later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * Copyright 2025 WP SYNTEX
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace WP_Syntex\Object_Cache_Annihilator;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Don't access directly.
}

define( 'OBJECT_CACHE_ANNIHILATOR_VERSION', '0.1.0' );
define( 'OBJECT_CACHE_ANNIHILATOR_DIR', __DIR__ );

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

use WP_Syntex\Object_Cache_Annihilator\Plugin;

( new Plugin() )->bootstrap();

register_activation_hook( __FILE__, [ Plugin::class, 'install' ] );
register_deactivation_hook( __FILE__, [ Plugin::class, 'uninstall' ] );
register_uninstall_hook( __FILE__, [ Plugin::class, 'uninstall' ] );
