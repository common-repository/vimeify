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

namespace Vimeify\Core\Shared;

use Vimeify\Core\Abstracts\BaseProvider;
use Vimeify\Core\Traits\AfterUpload;
use Vimeify\Core\Utilities\Formatters\VimeoFormatter;

class Hooks extends BaseProvider {

	use AfterUpload;

	/**
	 * Registers specific piece of functionality
	 * @return void
	 */
	public function register() {


		add_action( 'vimeify_upload_complete', [ $this, 'upload_complete' ], 5 );
		add_filter( 'wp_kses_allowed_html', [ $this, 'kses_allowed_html' ], 10, 2 );

		$this->register_integrations();
	}

	/**
	 * Register integrations
	 * @return void
	 */
	public function register_integrations() {

		$integrations = $this->plugin->get_integrations();

		if ( empty( $integrations ) ) {
			return;
		}

		foreach ( $integrations as $integration ) {
			$integration->register();
		}
	}

	/**
	 * Handle after upload hook in Admin area
	 *
	 * @param $args
	 *
	 * @since 1.7.0
	 */
	public function upload_complete( $args ) {

		$logtag = 'VIMEIFY-UPLOAD-HOOKS';
		$this->plugin->system()->logger()->log( sprintf( 'Running upload_complete hook. (%s)', wp_json_encode( [ 'args' => $args ] ) ), $logtag );

		/**
		 * Make sure we are on the right track.
		 */
		if ( ! isset( $args['vimeo_id'] ) ) {
			$this->plugin->system()->logger()->log( 'No vimeo id found. Failed to execute post upload hooks. (backend)', $logtag );

			return;
		}

		/**
		 * Obtain some important data.
		 */
		$response        = $args['vimeo_id'];
		$vimeo_formatter = new VimeoFormatter();
		$uri             = $vimeo_formatter->response_to_uri( $response );

		/**
		 * Signal start
		 */
		$this->plugin->system()->logger()->log( sprintf( 'Processing hooks for %s', $uri ), $logtag );

		/**
		 * Retrieve the source
		 */
		$source = isset( $args['source']['software'] ) ? $args['source']['software'] : null;
		if ( empty( $source ) ) {
			$this->plugin->system()->logger()->log( sprintf( '-- Source (%s) not found.', ( $source ? $source : 'NULL' ) ), $logtag );
		} else {
			$this->plugin->system()->logger()->log( sprintf( '-- Source found: %s.', $source ) );
		}

		/**
		 * Retrieve the profile
		 */
		$profile_id = $this->plugin->system()->settings()->profile()->find_profile_id( $source );
		if ( empty( $profile_id ) ) {
			$this->plugin->system()->logger()->log( '-- No upload profile found. Please go to Vimeify > Upload Profiles and create one, then go to Vimeify Settings > Upload profiles and select the desired ones where you need them.', $logtag );
		} else {
			$this->plugin->system()->logger()->log( '-- Using profile with ID: ' . $profile_id, $logtag );
		}

		/**
		 * Set Folder
		 */
		$folder_uri = isset( $args['overrides']['folder_uri'] ) ? $args['overrides']['folder_uri'] : $this->plugin->system()->settings()->profile()->get( $profile_id, 'folder', 'default' );
		$this->set_folder( $uri, $folder_uri, $logtag );

		/**
		 * Set Embed privacy
		 */
		if ( $this->plugin->system()->vimeo()->supports_embed_privacy() ) {
			$whitelisted_domains = $this->plugin->system()->settings()->profile()->get_whitelisted_domains( $profile_id );
			$this->set_embed_privacy( $uri, $whitelisted_domains, $logtag );
		}

		/**
		 * Set Embed presets
		 */
		if ( $this->plugin->system()->vimeo()->supports_embed_presets() ) {
			$preset_uri = $this->plugin->system()->settings()->profile()->get( $profile_id, 'embed_preset' );
			$this->set_embed_preset( $uri, $preset_uri, $logtag );
		}

		/**
		 * Set View privacy
		 */
		$view_privacy = isset( $args['overrides']['view_privacy'] ) ? $args['overrides']['view_privacy'] : $this->plugin->system()->settings()->profile()->get( $profile_id, 'view_privacy' );
		if ( $this->plugin->system()->vimeo()->supports_view_privacy_option( $view_privacy ) ) {
			$this->set_view_privacy( $uri, $view_privacy, $logtag );
		}

		/**
		 * Create local video
		 */
		if ( (int) $this->plugin->system()->settings()->profile()->get( $profile_id, 'behavior.store_in_library', 1 ) ) {
			$this->create_local_video( $args, $logtag );
		}

		/**
		 * Upload complete hook
		 */
		do_action( 'vimeify_upload_complete_hook_finished', $this, $args, $profile_id, $logtag );

		/**
		 * Signal finish
		 */
		$this->plugin->system()->logger()->log( 'Finished upload_complete hook.', $logtag );
	}

	/**
	 * List of allowed tags
	 * @return array
	 */
	public function kses_allowed_html($allowedtags, $context) {

		if ( 'vimeify' !== $context ) {
			return $allowedtags;
		}

		return array_merge_recursive( $allowedtags, [
			'a'          => array( 'id' => true, 'class' => true, 'style' => true, 'href' => true, 'title' => true, 'target' => true, 'data-id' => true, 'data-uri' => true ),
			'abbr'       => array( 'title' => true, ),
			'acronym'    => array( 'title' => true, ),
			'b'          => array(),
			'blockquote' => array( 'cite' => true, ),
			'cite'       => array(),
			'code'       => array(),
			'del'        => array( 'datetime' => true, ),
			'em'         => array(),
			'i'          => array(),
			'q'          => array( 'cite' => true, ),
			's'          => array(),
			'strike'     => array(),
			'strong'     => array(),
			'hr'         => array(),
			'br'         => array(),
			'h1'         => array( 'id' => true, 'class' => true, 'style' => true ),
			'h2'         => array( 'id' => true, 'class' => true, 'style' => true ),
			'h3'         => array( 'id' => true, 'class' => true, 'style' => true ),
			'h4'         => array( 'id' => true, 'class' => true, 'style' => true ),
			'h5'         => array( 'id' => true, 'class' => true, 'style' => true ),
			'h6'         => array( 'id' => true, 'class' => true, 'style' => true ),
			'select'     => array( 'id' => true, 'class' => true, 'style' => true, 'name' => true, 'data-placeholder' => true, 'data-target' => true, 'data-action' => true, 'data-uri' => true, 'data-show-target-if-value' => true, 'disabled' => true ),
			'input'      => array( 'id' => true, 'class' => true, 'style' => true, 'name' => true, 'type' => true, 'value' => true, 'disabled' => true, 'checked' => true ),
			'textarea'   => array( 'id' => true, 'class' => true, 'style' => true, 'name' => true, 'rows' => true, 'cols' => true, 'disabled' => true ),
			'button'     => array( 'id' => true, 'class' => true, 'style' => true, 'name' => true, 'type' => true, 'value' => true, 'disabled' => true ),
			'option'     => array( 'value' => true, 'selected' => true ),
			'p'          => array( 'id' => true, 'class' => true, 'style' => true ),
			'ul'         => array( 'id' => true, 'class' => true, 'style' => true ),
			'ol'         => array( 'id' => true, 'class' => true, 'style' => true ),
			'li'         => array( 'id' => true, 'class' => true, 'style' => true ),
			'div'        => array( 'id' => true, 'class' => true, 'style' => true, 'data-iframe-src' => true, 'allowfullscreen' => true, 'allow' => true ),
			'span'       => array( 'id' => true, 'class' => true, 'style' => true ),
			'label'      => array( 'id' => true, 'class' => true, 'style' => true, 'for' => true ),
			'table'      => array( 'id' => true, 'class' => true, 'style' => true ),
			'tr'         => array( 'id' => true, 'class' => true, 'style' => true ),
			'td'         => array( 'id' => true, 'class' => true, 'style' => true, 'colspan' => true ),
			'th'         => array( 'id' => true, 'class' => true, 'style' => true, 'colspan' => true ),
			'form'       => array( 'id' => true, 'class' => true, 'style' => true, 'enctype' => true, 'method' => true, 'action' => true ),
			'iframe'     => array( 'src' => true, 'frameborder' => true, 'allowfullscreen' => true, 'webkitAllowFullScreen' => true, 'mozallowfullscreen', 'allowFullScreen' ),
		] );
	}
}