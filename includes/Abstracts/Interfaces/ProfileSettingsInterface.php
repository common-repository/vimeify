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

namespace Vimeify\Core\Abstracts\Interfaces;

interface ProfileSettingsInterface {

	/**
	 * Retrieve single setting.
	 *
	 * @param $profile
	 * @param $key
	 * @param  null  $default
	 *
	 * @return mixed|null
	 */
	public function get( $profile, $key, $default = null );

	/**
	 * Update setting
	 *
	 * @param $profile
	 * @param $key
	 * @param $value
	 */
	public function set( $profile, $key, $value );

	/**
	 * Remove setting
	 *
	 * @param $profile
	 * @param $key
	 */
	public function remove( $profile, $key );

	/**
	 * Save settings
	 */
	public function save( $profile );

	/**
	 * All the settings
	 *
	 * @param $profile
	 *
	 * @return array
	 */
	public function all( $profile );

	/**
	 * Return upload profile by context
	 *
	 * @param $context_or_id
	 *
	 * @return int|null
	 */
	public function find_profile_id( $context_or_id );

	/**
	 * Returns the default admin embed privacy
	 *
	 * @param $profile
	 *
	 * @return  string
	 */
	public function get_view_privacy( $profile );

	/**
	 * Returns list of whitelisted domains
	 *
	 * @param $profile
	 *
	 * @return array
	 */
	public function get_whitelisted_domains( $profile );


	/**
	 * Retuns the profiles map
	 * @return mixed
	 */
	public function get_profiles_map();

	/**
	 * Check whether a profile is in use or not
	 *
	 * @param $profile
	 *
	 * @return bool
	 */
	public function in_use( $profile );

}