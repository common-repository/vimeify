<?php
/**
 * Plugin Name:       Vimeify
 * Plugin URI:        https://vimeify.com
 * Description:       Upload, manage and display Vimeo videos on your sites, beautifully.
 * Version:           1.0.0-beta1
 * Author:            CodeVerve
 * Author URI:        https://codeverve.com
 * Requires at least: 4.2
 * Requires PHP:      7.3
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       vimeify
 * Domain Path:       /languages
 *
 ********************************************************************
 *
 * Copyright (C) 2024 Darko Gjorgjijoski (https://darkog.com/)
 * Copyright (C) 2024 IDEOLOGIX MEDIA Dooel (https://ideologix.com/)
 *
 * This file is property of IDEOLOGIX MEDIA Dooel (https://ideologix.com)
 * This file is part of Vimeify Plugin - https://wordpress.org/plugins/vimeify/
 *
 * Vimeify - Formerly "WP Vimeo Videos" is free software: you can redistribute it
 * and/or modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation, either version 2 of the License,
 * or (at your option) any later version.
 *
 * Vimeify - Formerly "WP Vimeo Videos" is distributed in the hope that
 * it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Vimeify - Formerly "WP Vimeo Videos". If not, see <https://www.gnu.org/licenses/>.
 *
 * Code developed by Darko Gjorgjijoski <dg@darkog.com>.
 **********************************************************************/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'VIMEIFY_VERSION', '1.0.0-beta1' );
define( 'VIMEIFY_DB_VERSION', '100' );
define( 'VIMEIFY_PATH', rtrim( plugin_dir_path( __FILE__ ), DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR );
define( 'VIMEIFY_URL', rtrim( plugin_dir_url( __FILE__ ), '/' ) . '/' );
define( 'VIMEIFY_BASENAME', plugin_basename( __FILE__ ) );

// Load the composer dependencies, bail if not set up.
if ( ! file_exists( VIMEIFY_PATH . 'vendor/autoload.php' ) ) {
	wp_die( 'You are using a Development version of Vimeify plugin, please run composer install.' );
}

require_once VIMEIFY_PATH . 'vendor/autoload.php';

if ( ! function_exists( 'vimeify' ) ) {

	/**
	 * The Vimeify plugin wrapper
	 * @return \Vimeify\Core\Boot|null
	 * @throws Exception
	 */
	function vimeify() {

		static $boot = null;

		if ( is_null( $boot ) ) {

			$system = new \Vimeify\Core\System( [
				'id'                => 561,
				'name'              => 'Vimeify',
				'slug'              => 'vimeify',
				'icon'              => 'dashicons-video-alt',
				'file'              => __FILE__,
				'path'              => VIMEIFY_PATH,
				'url'               => VIMEIFY_URL,
				'basename'          => VIMEIFY_BASENAME,
				'plugin_version'    => VIMEIFY_VERSION,
				'database_version'  => VIMEIFY_DB_VERSION,
				'views_path'        => VIMEIFY_PATH . 'views',
				'tmp_dir_name'      => 'vimeify',
				'min_php_version'   => '7.3.0',
				'min_wp_version'    => '4.7',
				'settings_key'      => 'vimeify_settings',
				'commercial_url'    => 'https://vimeify.com/',
				'documentation_url' => 'https://vimeify.com/documentation',
				'settings_url'      => admin_url( 'admin.php?page=vimeify-settings' ),
				'components'        => [
					'database' => \Vimeify\Core\Components\Database::class,
					'settings' => \Vimeify\Core\Components\Settings::class,
					'requests' => \Vimeify\Core\Components\Requests::class,
					'logger'   => \Vimeify\Core\Components\Logger::class,
					'vimeo'    => \Vimeify\Core\Components\Vimeo::class,
					'views'    => \Vimeify\Core\Components\Views::class,
					'cache'    => \Vimeify\Core\Components\Cache::class,
				]
			] );

			$plugin = new \Vimeify\Core\Plugin( $system );
			$plugin->dependency_check( [ 'curl' ] );

			$boot = new \Vimeify\Core\Boot( $plugin );
			$boot->register();
		}

		$boot->init_process_manager();

		return $boot;
	}
}

try {

	vimeify();

} catch ( \Exception $e ) {

	add_action( 'admin_notices', function () use ( $e ) {
		$class   = 'notice notice-error is-dismissible';
		$plugin  = 'Vimeify';
		$message = $e->getMessage();
		printf( '<div class="%1$s"><p><strong>%2$s</strong>: %3$s</p></div>', esc_attr( $class ), esc_html( $plugin ), esc_html( $message ) );
	} );

}
