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

namespace Vimeify\Core\Components;

use Vimeify\Core\Abstracts\Interfaces\SettingsInterface;
use Vimeify\Core\Abstracts\Interfaces\SystemComponentInterface;
use Vimeify\Core\Abstracts\Interfaces\SystemInterface;
use Vimeify\Core\Utilities\FileSystem;
use Vimeify\Core\Utilities\Input\Sanitizer;
use Vimeify\Core\Utilities\Settings\PluginSettings;
use Vimeify\Core\Utilities\Settings\ProfileSettings;

class Settings implements SettingsInterface, SystemComponentInterface {

	/**
	 * Plugin settings
	 * @var PluginSettings
	 */
	protected $plugin_settings;

	/**
	 * Profile settings
	 * @var ProfileSettings
	 */
	protected $profile_settings;

	/**
	 * The system instance
	 * @var SystemInterface
	 */
	protected $system;

	/**
	 * Settings constructor.
	 *
	 * @param  SystemInterface  $system
	 * @param  array  $args
	 *
	 * @throws \Exception
	 */
	public function __construct( SystemInterface $system, $args = [] ) {

		$this->system = $system;

		if ( empty( $args ) ) {
			$args = $this->system->config();
		}

		$this->plugin_settings  = new PluginSettings( $args );
		$this->profile_settings = new ProfileSettings( $this->plugin_settings );
	}

	/**
	 * Set specific file to the temporary files list
	 *
	 * This means that the file is marked to be deleted from file system once the cron is executed.
	 *
	 * @param $path
	 * @param $time
	 *
	 * @return bool
	 */
	public function mark_as_temporary_file( $path, $time = null ) {

		if ( ! FileSystem::exists( $path ) ) {
			return false;
		}

		$tmp_files          = $this->get_temporary_files();
		$tmp_files[ $path ] = time();

		$this->set_temporary_files( $tmp_files );

		return true;
	}

	/**
	 * Unset a file from the temporary files list
	 *
	 * @param $path
	 *
	 * @return void
	 */
	public function remove_from_temporary_files( $path ) {
		$tmp_files = $this->get_temporary_files();
		if ( isset( $tmp_files[ $path ] ) ) {
			FileSystem::delete( $tmp_files[ $path ] );
		}
		$this->set_temporary_files( $tmp_files );
	}

	/**
	 * Returns the temporary pull files
	 * @return array
	 */
	public function get_temporary_files() {
		$tmp_files = get_option( 'vimeify_tmp_files' );
		if ( ! $tmp_files || ! is_array( $tmp_files ) ) {
			$tmp_files = [];
		}

		return $tmp_files;
	}

	/**
	 * Designate specific list as temporary files
	 *
	 * @param $list
	 *
	 * @return void
	 */
	public function set_temporary_files( $list ) {
		update_option( 'vimeify_tmp_files', $list );
	}

	/**
	 * Returns the api credentials
	 *
	 * @param  bool  $force
	 *
	 * @return void
	 */
	public function import_defaults( $force = false ) {

		$defaults = $this->get_plugin_defaults();
		$values   = array();

		// Set default profile
		$default_profile_id = get_option( 'vimeify_default_profile' );
		if ( ! $default_profile_id ) {
			$default_profile_id = wp_insert_post( [
				'post_type'   => Database::POST_TYPE_UPLOAD_PROFILES,
				'post_status' => 'publish',
				'post_title'  => 'Default Profile',
			] );
			if ( is_numeric( $default_profile_id ) && $default_profile_id > 0 ) {
				update_option( 'vimeify_default_profile', $default_profile_id );
			}
		}
		// Set default profile settings.
		foreach ( $this->get_profile_defaults() as $key => $value ) {
			$current = $this->profile()->get( $default_profile_id, $key, null );
			if ( is_null( $current ) ) {
				$value = Sanitizer::run( $value );
				$this->profile()->set( $default_profile_id, $key, $value );
			}
		}
		$this->profile()->save( $default_profile_id );
		$fields = array_values( array_unique( $this->profile()->get_profiles_map() ) );
		foreach ( $fields as $key ) {
			$current = $this->plugin()->get( $key, null );
			error_log('Current profile: ');
			error_log(print_R($current, true));
			if ( empty( $current ) ) {
				$this->plugin()->set( $key, $default_profile_id );
			}
		}
		$this->plugin()->save();

		// Find out if the defaults are not yet initialized
		// and if the defaults are not yet initialized, import as defaults.
		if ( ! $force ) {
			foreach ( $defaults as $key ) {
				$value = $this->plugin()->get( $key );
				if ( ! empty( $value ) ) {
					$values[] = $value;
				}
			}
		}
		if ( empty( $values ) || $force ) {
			foreach ( $defaults as $key => $value ) {
				if ( empty( $value ) ) {
					continue;
				}
				$this->plugin()->set( $key, $value );
			}
		}
		$this->plugin()->save();
	}

	/**
	 * Return the plugin defaults
	 * @return string[]
	 */
	public function get_plugin_defaults() {
		return array(
			'admin.tinymce.enable_account_search'         => '1',
			'admin.tinymce.enable_local_search'           => '1',
			'admin.tinymce.show_author_uploads_only'      => '0',
			'admin.gutenberg.enable_account_search'       => '1',
			'admin.gutenberg.show_author_uploads_only'    => '0',
			'frontend.behavior.enable_single_pages'       => '1',
			'admin.video_management.enable_embed_presets' => '1',
			'admin.video_management.enable_embed_privacy' => '1',
			'admin.video_management.enable_folders'       => '1',
			'profiles.default'                            => '',
		);
	}

	/**
	 * Return the profile defaults
	 * @return array
	 */
	public function get_profile_defaults() {
		return array(
			'behavior.store_in_library' => '1',
			'view_privacy'              => 'anybody',
			'embed_domains'             => '',
			'folder'                    => 'default',
			'embed_preset'              => 'default',
			'category'                  => '',
			'content_rating_class'      => 'safe',
			'content_rating'            => 'unrated',
		);
	}

	/**
	 * Prepare Values
	 *
	 * @param $value
	 *
	 * @return array|string
	 */
	public function prepare_value( $value ) {
		if ( ! is_array( $value ) ) {
			$option_value = sanitize_text_field( $value );
		} else {
			$option_value = $value;
		}

		return $option_value;
	}

	/**
	 * Profile settings
	 * @return ProfileSettings
	 */
	public function profile() {
		return $this->profile_settings;
	}

	/**
	 * Plugin settings
	 * @return PluginSettings
	 */
	public function plugin() {
		return $this->plugin_settings;
	}

}
