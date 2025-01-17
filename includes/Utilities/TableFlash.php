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

namespace Vimeify\Core\Utilities;

class TableFlash {

	/**
	 * Flash message
	 *
	 * @param $message
	 * @param $type
	 *
	 * @return string
	 */
	public function flash_message( $message, $type ) {
		$signature = $this->get_flash_signature();
		set_transient( $signature, array( 'message' => $message, 'type' => $type ), HOUR_IN_SECONDS );

		return $signature;
	}

	/**
	 * Unflash message
	 * @return array
	 */
	public function unflash_message() {
		$signature = $this->get_flash_signature();
		$notice    = get_transient( $signature );
		delete_transient( $signature );

		return $notice;
	}

	/**
	 * Returns the current url
	 *
	 * @param  string  $signature
	 *
	 * @return string
	 */
	public function get_current_url( $signature = '' ) {
		$uri = isset( $_SERVER['REQUEST_URI'] ) ? str_replace( '/wp-admin/', '', esc_url( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) ) : '';
		$url = admin_url( $uri );

		if ( ! empty( $signature ) ) {
			$url = add_query_arg( 'h', $signature, $url );
		}

		return remove_query_arg( array(
			'_wpnonce',
			'_wp_http_referer',
			'action',
			'action2',
			'record_id',
			'filter_action',
		), $url );
	}

	/**
	 * Returns the flash signature
	 * @return string
	 */
	public function get_flash_signature() {
		return 'vimeify_flash_user_' . get_current_user_id();
	}

}
