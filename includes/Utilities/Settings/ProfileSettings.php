<?php

namespace Vimeify\Core\Utilities\Settings;

use Vimeify\Core\Abstracts\Interfaces\ProfileSettingsInterface;
use Vimeify\Core\Utilities\Arrays\DotNotation;

class ProfileSettings implements ProfileSettingsInterface {

	/**
	 * The plugin settings
	 * @var PluginSettings
	 */
	protected $plugin_settings;

	/**
	 * Set values
	 * @var array
	 */
	protected $dot = [];

	/**
	 * The plugin settings
	 *
	 * @param  PluginSettings  $settings
	 */
	public function __construct( $settings ) {
		$this->plugin_settings = $settings;
	}

	/**
	 * Return the upload profile data
	 *
	 * @param $profile
	 * @param  null  $key
	 * @param  null  $default
	 *
	 * @return void
	 */
	public function get( $profile, $key = null, $default = null ) {

		$profile = $this->find_profile_id( $profile );

		static $cache = [];

		if ( ! isset( $cache[ $profile ] ) ) {
			$cache[ $profile ] = get_post_meta( $profile, 'profile_settings', true );
		}

		if ( ! empty( $cache[ $profile ] ) ) {
			if ( is_null( $key ) ) {
				return $cache[ $profile ];
			} else {
				$arrayDot = new DotNotation( $cache[ $profile ] );

				return $arrayDot->get( $key, $default );
			}
		} else {
			return $default;
		}

	}

	/**
	 * Update setting
	 *
	 * @param $profile
	 * @param $key
	 * @param $value
	 */
	public function set( $profile, $key, $value ) {
		$profile_id = $this->find_profile_id( $profile );
		$settings   = (array) get_post_meta( $profile_id, 'profile_settings', true );
		if ( ! isset( $this->dot[ $profile_id ] ) ) {
			$this->dot[ $profile_id ] = new DotNotation( $settings );
		}
		$this->dot[ $profile_id ]->set( $key, $value );
	}

	/**
	 * Remove setting
	 *
	 * @param $profile
	 * @param $key
	 */
	public function remove( $profile, $key ) {
		// TODO: Implement.
	}

	/**
	 * Save settings
	 */
	public function save( $profile ) {
		$profile_id = $this->find_profile_id( $profile );
		if ( isset( $this->dot[ $profile_id ] ) ) {
			update_post_meta( $profile_id, 'profile_settings', $this->dot[ $profile_id ]->getValues() );
		}
	}

	/**
	 * All the settings
	 *
	 * @param $profile
	 *
	 * @return array
	 */
	public function all( $profile ) {
		$profile_id = $this->find_profile_id( $profile );

		return (array) get_post_meta( $profile_id, 'profile_settings', true );
	}

	/**
	 * Returns list of whitelisted domains
	 *
	 * @param $profile
	 *
	 * @return array
	 */
	public function get_whitelisted_domains( $profile ) {
		$profile_id = $this->find_profile_id( $profile );
		$whitelist  = $this->get( $profile_id, 'privacy.embed_domains' );
		$domains    = array();
		if ( ! empty( $whitelist ) ) {
			$parts = explode( ',', $whitelist );
			foreach ( $parts as $domain ) {
				if ( empty( $domain ) || false === filter_var( $domain, FILTER_VALIDATE_DOMAIN ) ) {
					continue;
				}
				$domains[] = $domain;
			}
		}

		return $domains;
	}


	/**
	 * Returns the default admin embed privacy
	 *
	 * @param $profile
	 *
	 * @return  string
	 */
	public function get_view_privacy( $profile ) {
		$profile_id = $this->find_profile_id( $profile );
		$privacy    = $this->get( $profile_id, 'view_privacy' );
		if ( ! in_array( $privacy, array( 'anybody', 'contact', 'disable', 'nobody', 'unlisted' ) ) ) {
			$privacy = 'anybody';
		}

		return apply_filters( 'vimeify_default_privacy', $privacy, $profile );
	}

	/**
	 * Return upload profile by context
	 *
	 * @param $context_or_id
	 *
	 * @return int|null
	 */
	public function find_profile_id( $context_or_id ) {

		/**
		 * Pre-filter the profile
		 */
		$profile = apply_filters( 'vimeify_pre_get_upload_profile_by_context', null, $context_or_id, $this->plugin_settings );
		if ( ! empty( $profile ) ) {
			return $profile;
		}

		/**
		 * Check some pre-defined profiles
		 */
		if ( is_numeric( $context_or_id ) ) {
			return (int) $context_or_id; // already a profile?
		} elseif ( 'default' === $context_or_id ) {
			return (int) $this->plugin_settings->get( 'upload_profiles.default' );
		}

		$profiles_map = $this->get_profiles_map();

		if ( isset( $profiles_map[ $context_or_id ] ) ) {
			$profile = $this->plugin_settings->get( $profiles_map[ $context_or_id ], null );
		}
		if ( empty( $profile ) ) {
			$profile = $this->plugin_settings->get( 'upload_profiles.default' );
		}

		return apply_filters( 'vimeify_get_upload_profile_by_context', is_numeric( $profile ) ? (int) $profile : $profile, $context_or_id, $this->plugin_settings );

	}

	/**
	 * Returns the profiles map
	 * @return mixed|null
	 */
	public function get_profiles_map() {
		return apply_filters( 'vimeify_get_upload_profile_map', [
			'Default'                  => 'upload_profiles.default',
			'Backend.Editor.Classic'   => 'upload_profiles.admin_tinymce',
			'Backend.Editor.Gutenberg' => 'upload_profiles.admin_gutenberg',
			'Backend.Form.Other'       => 'upload_profiles.admin_other',
			'Backend.Form.Attachment'  => 'upload_profiles.admin_other',
			'Backend.Form.Upload'      => 'upload_profiles.admin_other',
		], $this->plugin_settings );
	}

	/**
	 * Check whether a profile is in use or not
	 *
	 * @param $profile
	 *
	 * @return bool
	 */
	public function in_use( $profile ) {
		$profile_id    = $this->find_profile_id( $profile );
		$settings_keys = array_unique( array_values( $this->get_profiles_map() ) );
		$state = false;
		foreach ( $settings_keys as $settings_key ) {
			if ( (int) $profile === (int) $this->plugin_settings->get( $settings_key ) ) {
				$state = true;
				break;
			}
		}
		return $state;
	}
}