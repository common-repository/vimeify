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

namespace Vimeify\Core\Backend;

use Vimeify\Core\Abstracts\Interfaces\CacheInterface;
use Vimeify\Core\Abstracts\Interfaces\ProviderInterface;
use Vimeify\Core\Plugin;
use Vimeify\Core\Utilities\Validators\WPValidator;

class Scripts implements ProviderInterface {

	/**
	 * The plugin instance
	 * @var Plugin
	 */
	protected $plugin;

	/**
	 * The plugin
	 *
	 * @param Plugin $plugin
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Registers specific piece of functionality
	 * @return void
	 */
	public function register() {
		add_action( 'admin_enqueue_scripts', [ $this, 'register_and_enqueue' ], 5 );
	}

	/**
	 * Register and enqueue assets
	 * @return void
	 */
	public function register_and_enqueue() {
		$this->register_assets();
		$this->enqueue_assets();
	}

	/**
	 * Register scripts
	 * @return void
	 */
	public function register_assets() {

		wp_register_script(
			'vimeify-admin',
			$this->plugin->url() . 'assets/admin/dist/scripts/main.min.js',
			array(
				'jquery',
				'vimeify-uploader',
				'vimeify-http',
			),
			filemtime( $this->plugin->path() . 'assets/admin/dist/scripts/main.min.js' ),
			true
		);

		wp_localize_script( 'vimeify-admin', 'Vimeify_Admin', array(
			'phrases' => array(
				'select2' => array(
					'errorLoading'    => esc_html__( 'The results could not be loaded.', 'vimeify' ),
					'inputTooLong'    => esc_html__( 'Please delete {number} character', 'vimeify' ),
					'inputTooShort'   => esc_html__( 'Please enter {number} or more characters', 'vimeify' ),
					'loadingMore'     => esc_html__( 'Loading more results...', 'vimeify' ),
					'maximumSelected' => esc_html__( 'You can only select {number} item', 'vimeify' ),
					'noResults'       => esc_html__( 'No results found', 'vimeify' ),
					'searching'       => esc_html__( 'Searching...', 'vimeify' ),
					'removeAllItems'  => esc_html__( 'Remove all items', 'vimeify' ),
					'removeItem'      => esc_html__( 'Remove item', 'vimeify' ),
					'search'          => esc_html__( 'Search', 'vimeify' ),
				)
			)
		) );

		wp_register_script(
			'vimeify-vimeo-upload-block',
			$this->plugin->url() . 'assets/blocks-legacy/upload/main.js',
			array(
				'wp-blocks',
				'wp-editor',
				'jquery',
				'vimeify-uploader'
			),
			filemtime( $this->plugin->path() . 'assets/blocks-legacy/upload/main.js' )
		);

		wp_register_style(
			'vimeify-vimeo-upload-block',
			$this->plugin->url() . 'assets/blocks-legacy/upload/main.css',
			array(),
			filemtime( $this->plugin->path() . 'assets/blocks-legacy/upload/main.css' ),
			'all'
		);

		wp_register_style(
			'vimeify-admin',
			$this->plugin->url() . 'assets/admin/dist/styles/main.min.css',
			array(),
			filemtime( $this->plugin->path() . 'assets/admin/dist/styles/main.min.css' ),
			'all'
		);
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_assets() {

		if ( ! function_exists( 'get_current_screen' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/screen.php' );
		}

		// Validate the current screen
		$wp_validator             = new WPValidator();
		$current_screen           = get_current_screen();
		$is_edit_screen           = isset( $_GET['post'] ) && is_numeric( $_GET['post'] );
		$is_create_screen         = $current_screen->action === 'add' && $current_screen->base === 'post';
		$is_gutenberg_active      = $wp_validator->is_gutenberg_active();
		$is_create_or_edit_screen = $is_create_screen || $is_edit_screen;

		// Sweet alert
		$this->enqueue_sweetalert();

		// Uploader
		$this->enqueue_vimeo_uploader();
		$this->enqueue_vimeo_upload_modal();

		// Admin
		$this->enqueue_admin_scripts();

		// Gutenbrg block
		if ( $is_gutenberg_active && $is_create_or_edit_screen ) {
			$this->enqueue_gutenberg_block();
			wp_enqueue_style( 'vimeify-dropzone' );
		}


		// Enqueue admin styles
		wp_enqueue_style( 'vimeify-admin' );

	}

	/**
	 * Enqueue Sweet alert
	 */
	public function enqueue_sweetalert() {
		wp_enqueue_script( 'vimeify-swal' );
	}

	/**
	 * Enqueue Vimeo Uploader
	 */
	public function enqueue_vimeo_uploader() {
		wp_enqueue_script( 'vimeify-tus' );
		wp_enqueue_script( 'vimeify-uploader' );
	}

	/**
	 * Enqueue the Upload modal
	 */
	public function enqueue_vimeo_upload_modal() {
		wp_enqueue_script( 'vimeify-upload-modal' );
		wp_enqueue_style( 'vimeify-upload-modal' );
	}

	/**
	 * Enqueue gutenberg block
	 */
	public function enqueue_gutenberg_block() {

		$current_user_uploads      = ! current_user_can( 'administrator' ) && (int) $this->plugin->system()->settings()->plugin()->get( 'admin.gutenberg.show_author_uploads_only', 0 );
		$uploads                   = $this->plugin->system()->database()->get_uploaded_videos( $current_user_uploads );
		$methods                   = array(
			'upload' => esc_html__( 'Upload new Vimeo video', 'vimeify' ),
			'local'  => esc_html__( 'Insert Vimeo video from local library', 'vimeify' ),
			'search' => esc_html__( 'Search your Vimeo account', 'vimeify' ),
		);
		$is_account_search_enabled = $this->plugin->system()->settings()->plugin()->get( 'admin.gutenberg.enable_account_search', 1 );
		if ( ! $is_account_search_enabled ) {
			unset( $methods['search'] );
		}
		$is_local_search_enabled = $this->plugin->system()->settings()->plugin()->get( 'admin.gutenberg.enable_local_search', 1 );
		if ( ! $is_local_search_enabled ) {
			unset( $methods['local'] );
		}

		wp_enqueue_script( 'vimeify-vimeo-upload-block' );
		wp_localize_script( 'vimeify-vimeo-upload-block', 'Vimeify_Gutenberg', array(
			'nonce'               => wp_create_nonce( 'vimeify_nonce' ),
			'access_token'        => $this->plugin->system()->settings()->plugin()->get( 'api_credentials.access_token' ),
			'enable_vimeo_search' => $this->plugin->system()->settings()->plugin()->get( 'admin.gutenberg.enable_account_search' ),
			'default_privacy'     => $this->plugin->system()->settings()->profile()->get_view_privacy( 'Backend.Editor.Gutenberg' ),
			'ajax_url'            => admin_url( 'admin-ajax.php' ),
			'uploads'             => $uploads,
			'methods'             => $methods,
			'words'               => array(
				'block_name'   => esc_html__( 'Vimeify Upload (Old/Legacy/Deprecated)', 'vimeify' ),
				'title'        => esc_html__( 'Title', 'vimeify' ),
				'desc'         => esc_html__( 'Description', 'vimeify' ),
				'file'         => esc_html__( 'File', 'vimeify' ),
				'uploading3d'  => esc_html__( 'Uploading...', 'vimeify' ),
				'upload'       => esc_html__( 'Upload', 'vimeify' ),
				'search'       => esc_html__( 'Search', 'vimeify' ),
				'sorry'        => esc_html__( 'Sorry', 'vimeify' ),
				'privacy_view' => esc_html__( 'Who can view this video?', 'vimeify' ),
			),
			'phrases'             => array(
				'upload_invalid_file'               => esc_html__( 'Please select valid video file.', 'vimeify' ),
				'invalid_search_phrase'             => esc_html__( 'Invalid search phrase. Please enter valid search phrase.', 'vimeify' ),
				'enter_phrase'                      => esc_html__( 'Enter phrase', 'vimeify' ),
				'select_video'                      => esc_html__( 'Select video', 'vimeify' ),
				'upload_success'                    => esc_html__( 'Video uploaded successfully!', 'vimeify' ),
				'block_title'                       => esc_html__( 'Insert Vimeo Video', 'vimeify' ),
				'existing_not_visible_current_user' => esc_html__( '= Uploaded by someone else, not visible to you =', 'vimeify' ),
				'select_existing_video'             => esc_html__( 'Select existing video', 'vimeify' ),
			),
			'upload_form_options' => array(
				'enable_view_privacy' => (int) $this->plugin->system()->settings()->plugin()->get( 'admin.gutenberg.enable_view_privacy', 0 ),
				'privacy_view'        => $this->plugin->system()->vimeo()->get_view_privacy_options_for_forms( 'admin' ),
			)
		) );
		wp_enqueue_style( 'vimeify-vimeo-upload-block' );
	}

	/**
	 * Enqueue admin scripts
	 */
	public function enqueue_admin_scripts() {

		if ( ! wp_script_is( 'select2', 'enqueued' ) ) {
			wp_enqueue_script( 'vimeify-select2' );
		}
		if ( ! wp_style_is( 'select2', 'enqueued' ) ) {
			wp_enqueue_style( 'vimeify-select2' );
		}


		wp_enqueue_script( 'vimeify-admin' );

		wp_localize_script( 'vimeify-admin', 'Vimeify', array(
			'nonce'                         => wp_create_nonce( 'vimeify_nonce' ),
			'ajax_url'                      => admin_url( 'admin-ajax.php' ),
			'access_token'                  => $this->plugin->system()->settings()->plugin()->get( 'api_credentials.access_token' ),
			'default_privacy'               => $this->plugin->system()->settings()->profile()->get_view_privacy( 'Backend.Editor.Classic' ),
			'sorry'                         => esc_html__( 'Sorry', 'vimeify' ),
			'upload_invalid_file'           => esc_html__( 'Please select valid video file.', 'vimeify' ),
			'delete_not_allowed'            => esc_html__( 'Delete is not allowed because your account doesn\'t have the correct delete scope required by Vimeo.', 'vimeify' ),
			'delete_confirm_title'          => esc_html__( 'Are you sure?', 'vimeify' ),
			'delete_confirm_desc'           => esc_html__( 'Are you sure you want to delete this video? This action deletes the video from the Vimeo and can not be reversed.', 'vimeify' ),
			'delete_whitelist_domain_error' => esc_html__( 'Sorry, the domain could not be deleted.', 'vimeify' ),
			'http_error'                    => esc_html__( 'Sorry there was a HTTP error. Please check the server logs or contact support.', 'vimeify' ),
			'success'                       => esc_html__( 'Success', 'vimeify' ),
			'cancel'                        => esc_html__( 'Cancel', 'vimeify' ),
			'confirm'                       => esc_html__( 'Confirm', 'vimeify' ),
			'close'                         => esc_html__( 'Close', 'vimeify' ),
			'remove_lower'                  => esc_html__( 'remove', 'vimeify' ),
			'delete_confirmation'           => esc_html__( 'Are you sure you want to delete this video?', 'vimeify' ),
			'delete_confirmation_yes'       => esc_html__( 'Yes, please', 'vimeify' ),
			'title'                         => esc_html__( 'Title', 'vimeify' ),
			'description'                   => esc_html__( 'Description', 'vimeify' ),
			'upload'                        => esc_html__( 'Upload', 'vimeify' ),
			'upload_to_vimeo'               => esc_html__( 'Upload to vimeo', 'vimeify' ),
			'correct_errors'                => __( 'Please correct the following errors', 'vimeify' ),
			'privacy_view'                  => __( 'Who can view this video?', 'vimeify' ),
			'problem_solution'              => __( 'Problem solution', 'vimeify' ),
			'loading'                       => __( 'Loading...', 'vimeify' ),
			'stats'                         => __( 'Statistics', 'vimeify' ),
			'explanation'                   => __( 'Explanation', 'vimeify' ),
			'upload_form_options'           => array(
				'enable_view_privacy' => (int) $this->plugin->system()->settings()->plugin()->get( 'admin.upload_forms.enable_view_privacy', 0 ),
				'privacy_view'        => $this->plugin->system()->vimeo()->get_view_privacy_options_for_forms( 'admin' ),
			),
			'upload_block_options' => $this->get_upload_block_settings()
		) );
	}

	/**
	 * Upload block settings
	 * @return array
	 */
	public function get_upload_block_settings() {

		$validator = new WPValidator();

		if ( ! $validator->is_gutenberg_active() ) {
			return [];
		}

		$default_folder  = '';
		$folders_enabled = (int) $this->plugin->system()->settings()->plugin()->get( 'admin.gutenberg.enable_folders', 0 );
		if ( $folders_enabled ) {
			$folder = $this->plugin->system()->settings()->profile()->get( 'Backend.Editor.Gutenberg', 'folder' );
			if ( empty( $folder ) || 'default' === $folder ) {
				$default_folder = [ 'name' => __( 'Default (No folder)', 'vimeify' ), 'uri' => 'default' ];
			} else {
				$default_folder = [
					'name' => sprintf( '%s (Default)', $this->plugin->system()->cache()->remember( 'default_folder_name', function () use ( $folder ) {
						return $this->plugin->system()->vimeo()->get_folder_name( $folder );
					}, 30 * CacheInterface::MINUTE_IN_SECONDS ) ),
					'uri'  => $folder
				];
			}
		}

		return [
			'nonce'               => wp_create_nonce( 'wp_rest' ),
			'methods'             => array(
				'upload' => __( 'Upload new Vimeo video', 'vimeify' ),
				'local'  => __( 'Insert Vimeo video from local library', 'vimeify' ),
				'search' => __( 'Search your Vimeo account', 'vimeify' ),
			),
			'upload_form_options' => array(
				'enable_view_privacy' => (int) $this->plugin->system()->settings()->plugin()->get( 'admin.gutenberg.enable_view_privacy', 0 ),
				'enable_folders'      => (int) $this->plugin->system()->settings()->plugin()->get( 'admin.gutenberg.enable_folders', 0 ),
				'privacy_view'        => $this->plugin->system()->vimeo()->get_view_privacy_options_for_forms( 'admin' ),
				'default_folder'      => $default_folder,
			),
			'restBase'       => get_rest_url(),
			'accessToken'    => $this->plugin->system()->settings()->plugin()->get( 'api_credentials.access_token' ),
			'notifyEndpoint' => add_query_arg( [
				'action'   => 'vimeify_store_upload',
				'source'   => 'Backend.Editor.Gutenberg',
				'_wpnonce' => wp_create_nonce( 'vimeify_nonce' ),
			], admin_url( 'admin-ajax.php' ) )
		];
	}
}