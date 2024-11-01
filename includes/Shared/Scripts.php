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
use Vimeify\Core\Utilities\Validators\RequestValidator;
use Vimeify\Core\Utilities\Validators\WPValidator;

class Scripts extends BaseProvider {

	/**
	 * Registers specific piece of functionality
	 * @return void
	 */
	public function register() {
		add_action( 'wp_enqueue_scripts', [ $this, 'register_scripts' ], 0 );
		add_action( 'admin_enqueue_scripts', [ $this, 'register_scripts' ], 0 );
		add_action( 'wp_enqueue_editor', [ $this, 'enqueue_scripts_tinymce' ], 1000 );
		add_action( 'before_wp_tiny_mce', [ $this, 'tinymce_globals' ] );
		add_action( 'after_setup_theme', [ $this, 'tinymce_styles' ] );
		add_filter( 'mce_buttons', [ $this, 'tinymce_vimeo_button' ] );
		add_filter( 'mce_external_plugins', [ $this, 'tinymce_vimeo_plugin' ] );
	}

	/**
	 * Create nonce
	 */
	public function get_nonce() {
		static $vimeify_nonce;
		if ( empty( $vimeify_nonce ) ) {
			$vimeify_nonce = wp_create_nonce( 'vimeify_nonce' );
		}

		return $vimeify_nonce;
	}

	/**
	 * Check if it is possible to enqueue the Vimeo plugin for tinymce.
	 * @return bool
	 */
	public function can_enqueue_vimeo_tinymce() {

		$wp_validator = new WPValidator();

		$is_gutenberg = is_admin() && $wp_validator->is_gutenberg_active();

		if ( $is_gutenberg ) {
			return false;
		}

		if ( ! apply_filters( 'vimeify_enable_tinymce_upload_plugin', true ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Register Vimeo Button
	 *
	 * @param $buttons
	 *
	 * @return mixed
	 */
	public function tinymce_vimeo_button( $buttons ) {

		if ( $this->can_enqueue_vimeo_tinymce() ) {
			array_push( $buttons, 'vimeify_vimeo_button' );
		}

		return $buttons;
	}

	/**
	 * Register Vimeo Plugin
	 *
	 * @param $plugin_array
	 *
	 * @return mixed
	 */
	public function tinymce_vimeo_plugin( $plugin_array ) {

		if ( $this->can_enqueue_vimeo_tinymce() ) {
			$plugin_array['vimeify_vimeo_button'] = $this->plugin->url() . 'assets/shared/dist/scripts/tinymce-upload.min.js';
		}

		return $plugin_array;
	}

	/**
	 * Add editor styles
	 */
	public function tinymce_styles() {
		/*if ( ! wp_script_is( 'vimeify-upload-modal', 'enqueued' ) ) {
			add_editor_style( $this->plugin->url() . 'shared/dist/styles/upload-modal.min.css' );
		}*/
		// Disabled, throws wp_script_is() warning.
	}

	/**
	 * Tinymce globals
	 *
	 * @param $settings
	 */
	public function tinymce_globals( $settings ) {

		$is_loaded = false;

		if ( is_array( $settings ) ) {
			foreach ( $settings as $editor_id => $editor ) {
				if ( isset( $editor['external_plugins'] ) ) {
					if ( strpos( $editor['external_plugins'], 'vimeify_vimeo_button' ) !== false ) {
						$is_loaded = true;
					}
				}
			}
		}

		if ( ! $is_loaded ) {
			return;
		}

		$this->enqueue_tinymce_assets();
	}


	/**
	 * Register all the resources
	 */
	public function register_scripts() {

		$request_validator = new RequestValidator();

		wp_register_style(
			'vimeify-iconfont',
			$this->plugin->url() . 'assets/resources/iconfont/css/vimeify.css',
			null,
			null,
			'all'
		);

		wp_register_style(
			'vimeify-grid',
			$this->plugin->url() . 'assets/shared/dist/styles/grid.css',
			null,
			null,
			'all'
		);

		wp_register_script(
			'vimeify-http',
			$this->plugin->url() . 'assets/shared/dist/scripts/http.min.js',
			null,
			null,
			true
		);

		wp_register_script(
			'vimeify-dropzone',
			$this->plugin->url() . 'assets/resources/dropzone/dropzone.min.js',
			null,
			'5.7.1',
			true
		);

		wp_register_style(
			'vimeify-dropzone',
			$this->plugin->url() . 'assets/resources/dropzone/dropzone.min.css',
			null,
			'5.7.1',
			'all'
		);

		wp_register_script(
			'vimeify-chunked-upload',
			$this->plugin->url() . 'assets/shared/dist/scripts/chunked-upload.min.js',
			array( 'wp-util', 'vimeify-dropzone', 'jquery' ),
			filemtime( $this->plugin->path() . 'assets/shared/dist/scripts/chunked-upload.min.js' ),
			true
		);

		wp_localize_script( 'vimeify-chunked-upload', 'Vimeify_Chunked_Upload', array(
			'url'             => admin_url( 'admin-ajax.php' ),
			'errors'          => array(
				'file_not_uploaded' => esc_html__( 'This file was not uploaded.', 'vimeify' ),
				'file_limit'        => esc_html__( 'File limit has been reached ({fileLimit}).', 'vimeify' ),
				'file_extension'    => esc_html__( 'File type is not allowed.', 'vimeify' ),
				'file_size'         => esc_html__( 'File exceeds the max size allowed.', 'vimeify' ),
				'post_max_size'     => sprintf( /* translators: %s - max allowed file size by a server. */
					esc_html__( 'File exceeds the upload limit allowed (%s).', 'vimeify' ),
					size_format( wp_max_upload_size() ),
				),
			),
			'loading_message' => esc_html__( 'File upload is in progress. Please submit the form once uploading is completed.', 'vimeify' ),
		) );

		wp_register_script(
			'vimeify-select2',
			$this->plugin->url() . 'assets/resources/select2/select2.min.js',
			null,
			'4.0.13',
			true
		);

		wp_register_style(
			'vimeify-select2',
			$this->plugin->url() . 'assets/resources/select2/select2.min.css',
			array(),
			'4.0.13',
			'all'
		);

		wp_register_script(
			'vimeify-swal',
			$this->plugin->url() . 'assets/resources/sweetalert2/sweetalert2.min.js',
			null,
			'11.4.8',
			true
		);

		wp_register_script(
			'vimeify-tus',
			$this->plugin->url() . 'assets/resources/tus-js-client/tus.min.js',
			null, '4.1.0'
		);

		wp_register_script(
			'vimeify-uploader',
			$this->plugin->url() . 'assets/shared/dist/scripts/uploader.min.js',
			array( 'vimeify-tus' ),
			filemtime( $this->plugin->path() . 'assets/shared/dist/scripts/uploader.min.js' )
		);

		wp_register_script(
			'vimeify-upload-modal',
			$this->plugin->url() . 'assets/shared/dist/scripts/upload-modal.min.js',
			array( 'jquery', 'vimeify-uploader', 'vimeify-swal' ),
			filemtime( $this->plugin->path() . 'assets/shared/dist/scripts/upload-modal.min.js' )
		);

		$modal_config = $this->get_modal_config_params();
		wp_localize_script( 'vimeify-upload-modal', 'Vimeify_Modal_Config', $modal_config );

		wp_register_style(
			'vimeify-upload-modal',
			$this->plugin->url() . 'assets/shared/dist/styles/upload-modal.min.css',
			array(),
			filemtime( $this->plugin->path() . 'assets/shared/dist/styles/upload-modal.min.css' ),
			'all'
		);
	}

	/**
	 * Enqueues scripts
	 * @return void
	 */
	public function enqueue_scripts_tinymce( $config ) {
		if ( isset( $config['tinymce'] ) && $config['tinymce'] && $this->can_enqueue_vimeo_tinymce() ) {
			$this->enqueue_tinymce_assets();
		}
	}

	/**
	 * Enqueues TinyMCE assets
	 */
	public function enqueue_tinymce_assets() {
		foreach ( array( 'vimeify-swal', 'vimeify-tus', 'vimeify-uploader', 'vimeify-upload-modal' ) as $script ) {
			wp_enqueue_script( $script );
		}
		foreach ( array( 'vimeify-upload-modal' ) as $style ) {
			wp_enqueue_style( $style );
		}
		// Config
		$mce_icon     = apply_filters( 'vimeify_mce_toolbar_icon_enable', true );
		$mce_icon_url = $mce_icon ? apply_filters( 'vimeify_mce_toolbar_icon_url', $this->plugin->icon() ) : null;
		$mce_text     = apply_filters( 'vimeify_mce_toolbar_title', esc_html__( 'Vimeo', 'vimeify' ) );
		$mce_text     = $mce_icon && $mce_text ? sprintf( ' %s', $mce_text ) : $mce_text;
		$mce_tooltip  = apply_filters( 'vimeify_mce_toolbar_tooltip', esc_html__( 'Insert Vimeo Video', 'vimeify' ) );
		wp_localize_script( 'wp-tinymce', 'Vimeify_MCE_Config', array(
			'phrases'  => array(
				'tmce_title'            => $mce_text,
				'tmce_tooltip'          => $mce_tooltip,
				'cancel_upload_confirm' => esc_html__( 'Are you sure you want to cancel the upload?', 'vimeify' ),
			),
			'icon'     => $mce_icon,
			'icon_url' => $mce_icon_url,
			'markup'   => apply_filters( 'vimeify_mce_output_markup', '[vimeify_video id="{id}"]' )
		) );
		wp_localize_script( 'vimeify-upload-modal', 'Vimeify_Modal_Config', $this->get_modal_config_params() );
	}


	/**
	 * The modal config params
	 * @return array
	 */
	private function get_modal_config_params() {
		return array(
			'nonce'               => $this->get_nonce(),
			'ajax_url'            => admin_url( 'admin-ajax.php' ),
			'access_token'        => $this->plugin->system()->settings()->plugin()->get( 'api_credentials.access_token' ),
			'default_privacy'     => $this->plugin->system()->settings()->profile()->get_view_privacy('Backend.Editor.Classic'),
			'enable_vimeo_search' => $this->plugin->system()->settings()->plugin()->get( 'admin.tinymce.enable_account_search' ),
			'enable_local_search' => $this->plugin->system()->settings()->plugin()->get( 'admin.tinymce.enable_local_search' ),
			'words'               => array(
				'sorry'        => esc_html__( 'Sorry', 'vimeify' ),
				'success'      => esc_html__( 'Success', 'vimeify' ),
				'title'        => esc_html__( 'Title', 'vimeify' ),
				'desc'         => esc_html__( 'Description', 'vimeify' ),
				'insert'       => esc_html__( 'Insert', 'vimeify' ),
				'search'       => esc_html__( 'Search', 'vimeify' ),
				'searching3d'  => esc_html__( 'Searching...', 'vimeify' ),
				'upload'       => esc_html__( 'Upload', 'vimeify' ),
				'uploading3d'  => esc_html__( 'Uploading', 'vimeify' ),
				'file'         => esc_html__( 'File', 'vimeify' ),
				'privacy_view' => esc_html__( 'Who can view this video?', 'vimeify' ),
			),
			'phrases'             => array(
				'title'                 => apply_filters( 'vimeify_upload_modal_title', esc_html__( 'Insert Vimeo Video', 'vimeify' ) ),
				'http_error'            => esc_html__( 'Sorry there was a HTTP error. Please check the server logs or contact support.', 'vimeify' ),
				'upload_invalid_file'   => esc_html__( 'Please select valid video file.', 'vimeify' ),
				'invalid_search_phrase' => esc_html__( 'Invalid search phrase. Please enter valid search phrase.', 'vimeify' ),
				'videos_not_found'      => esc_html__( 'No uploaded videos found.', 'vimeify' ),
				'search_not_found'      => esc_html__( 'No matching videos found for your search', 'vimeify' ),
				'cancel_upload_confirm' => esc_html__( 'Are you sure you want to cancel the upload?', 'vimeify' )
			),
			'methods'             => array(
				'upload' => esc_html__( 'Upload new Vimeo video', 'vimeify' ),
				'local'  => esc_html__( 'Insert Vimeo video from local library', 'vimeify' ),
				'search' => esc_html__( 'Search your Vimeo account', 'vimeify' ),
			),
			'upload_form_options' => array(
				'enable_view_privacy' => (int) $this->plugin->system()->settings()->plugin()->get( 'admin.tinymce.enable_view_privacy', 0 ),
				'privacy_view'          => is_admin() ? $this->plugin->system()->vimeo()->get_view_privacy_options_for_forms( 'admin' ) : null,
			)
		);
	}
}