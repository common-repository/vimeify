<?php
/********************************************************************
 * Copyright (C) 2024 Darko Gjorgjijoski (https://darkog.com/)
 * Copyright (C) 2024 IDEOLOGIX MEDIA Dooel (https://ideologix.com/)
 *
 * This file is property of IDEOLOGIX MEDIA Dooel (https://ideologix.com)
 * This file is part of Vimeify Plugin - https://wordpress.org/plugins/vimeify/
 *
 * Vimeify - Formerly "WP Vimeo Videos" is free software: you can redistribute
 * it and/or modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation, either version 2 of the License,
 * or (at your option) any later version.
 *
 * Vimeify - Formerly "WP Vimeo Videos" is distributed in the hope that it
 * will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this plugin. If not, see <https://www.gnu.org/licenses/>.
 *
 * Code developed by Darko Gjorgjijoski <dg@darkog.com>.
 **********************************************************************/

namespace Vimeify\Core;

use Vimeify\Core\Abstracts\Interfaces\SystemInterface;
use Vimeify\Core\Components\Cache;
use Vimeify\Core\Components\Database;
use Vimeify\Core\Components\Logger;
use Vimeify\Core\Components\Requests;
use Vimeify\Core\Components\Settings;
use Vimeify\Core\Components\Views;
use Vimeify\Core\Components\Vimeo;
use Vimeify\Core\Utilities\FileSystem;
use Vimeify\Core\Utilities\TemporaryDirectory;

class System implements SystemInterface {

	/**
	 * The vimeo instance
	 * @var Vimeo
	 */
	protected $vimeo;

	/**
	 * The database instance
	 * @var Database
	 */
	protected $database;

	/**
	 * The settings instance
	 * @var Settings
	 */
	protected $settings;

	/**
	 * The Logger instance
	 * @var Logger
	 */
	protected $logger;

	/**
	 * The View instance
	 * @var Views
	 */
	protected $views;

	/**
	 * The Requests instance
	 * @var Requests
	 */
	protected $requests;

	/**
	 * The Requests instance
	 * @var Cache
	 */
	protected $cache;

	/**
	 * The config
	 * @var array
	 */
	protected $config;

	/**
	 * Consturctor
	 *
	 * @param $config
	 */
	public function __construct( $config ) {

		$this->config = $config;

		$this->set_database( new $this->config['components']['database']( $this ) );
		$this->set_logger( new $this->config['components']['logger']( $this ) );
		$this->set_settings( new $this->config['components']['settings']( $this ) );
		$this->set_vimeo( new $this->config['components']['vimeo']( $this ) );
		$this->set_views( new $this->config['components']['views']( $this ) );
		$this->set_requests( new $this->config['components']['requests']( $this ) );
		$this->set_cache( new $this->config['components']['cache']( $this ) );
	}

	/**
	 * Set the vimeo instance
	 *
	 * @param Vimeo $vimeo
	 *
	 * @return void
	 */
	public function set_vimeo( Vimeo $vimeo ) {
		$this->vimeo = $vimeo;
	}

	/**
	 * Set the database instance
	 *
	 * @param Database $database
	 *
	 * @return void
	 */
	public function set_database( Database $database ) {
		$this->database = $database;
	}

	/**
	 * Set the settings instance
	 *
	 * @param Settings $settings
	 *
	 * @return void
	 */
	public function set_settings( Settings $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Set the logger instance
	 *
	 * @param Logger $logger
	 *
	 * @return void
	 */
	public function set_logger( Logger $logger ) {
		$this->logger = $logger;
	}

	/**
	 * Set the view instance
	 *
	 * @param Views $views
	 *
	 * @return void
	 */
	public function set_views( Views $views ) {
		$this->views = $views;
	}

	/**
	 * Set the requests instance
	 *
	 * @param Requests $requests
	 *
	 * @return void
	 */
	public function set_requests( Requests $requests ) {
		$this->requests = $requests;
	}

	/**
	 * Set the cache instance
	 *
	 * @param Cache $requests
	 *
	 * @return void
	 */
	public function set_cache( Cache $cache ) {
		$this->cache = $cache;
	}

	/**
	 * The Vimeo API
	 * @return Vimeo
	 */
	public function vimeo() {
		return $this->vimeo;
	}

	/**
	 * The Database API
	 * @return Database
	 */
	public function database() {
		return $this->database;
	}

	/**
	 * The Settings API
	 * @return Settings
	 */
	public function settings() {
		return $this->settings;
	}

	/**
	 * The Logger API
	 * @return Logger
	 */
	public function logger() {
		return $this->logger;
	}

	/**
	 * The Views API
	 * @return Views
	 */
	public function views() {
		return $this->views;
	}

	/**
	 * The Requests API
	 * @return Requests
	 */
	public function requests() {
		return $this->requests;
	}

	/**
	 * The Cache API
	 * @return Cache
	 */
	public function cache() {
		return $this->cache;
	}

	/**
	 * The config data
	 * @return array
	 */
	public function config( $key = null, $default = null ) {

		if ( ! is_null( $key ) ) {
			return isset( $this->config[ $key ] ) ? $this->config[ $key ] : $default;
		}

		return $this->config;
	}


	/**
	 * Returns the tmp dir path
	 * @return TemporaryDirectory
	 */
	public function tmp_dir() {

		static $value = null;

		if ( is_null( $value ) ) {

			$uploads = wp_upload_dir();
			$slug    = $this->config( 'tmp_dir_name', 'vimeify' );
			$base    = trailingslashit( $uploads['basedir'] );
			$dir     = sprintf( '%s%s', $base, $slug );

			if ( ! FileSystem::exists( $dir ) ) {
				if ( ! FileSystem::is_writable( $base ) ) {
					error_log( 'Vimeify: Base ' . $base . ' not writable.' );

					return null;
				} else {
					if ( ! FileSystem::mkdir( $dir ) ) {
						error_log( 'Vimeify: Failed to create tmp dir: ' . $dir );

						return null;
					}
				}
			}

			$url   = $uploads['baseurl'] . '/' . $slug;
			$value = new TemporaryDirectory( [
				'path' => $dir,
				'url'  => $url,
			] );
		}

		return $value;

	}
}