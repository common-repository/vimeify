<?php

namespace Vimeify\Core\Backend\Metaboxes;

use Vimeify\Core\Abstracts\Interfaces\ProviderInterface;
use Vimeify\Core\Backend\Ui;
use Vimeify\Core\Components\Database;
use Vimeify\Core\Plugin;

class VideoMeta implements ProviderInterface {
	/**
	 * The plugin instance
	 * @var Plugin
	 */
	private $plugin;

	/**
	 * The constructor
	 *
	 * @param Plugin $plugin
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Register the meta
	 * @return void
	 */
	public function register() {
		add_action( 'admin_init', [ $this, 'disable_new_posts_page' ] );
		add_action( 'add_meta_boxes', [ $this, 'register_meta_boxes' ], 1 );
		add_action( 'edit_form_after_title', [ $this, 'prioritize_meta_boxes' ] );
		add_action( 'save_post', [ $this, 'save_post' ], 10, 3 );
	}

	/**
	 * Save post data
	 *
	 * @param $post_id
	 * @param $post
	 * @param $update
	 *
	 * @return void
	 */
	public function save_post( $post_id, $post, $update ) {
		if ( ! $update ) {
			return;
		}

		if ( empty( $_POST['vimeify_nonce_field'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['vimeify_nonce_field'] ) ), 'vimeify_nonce' ) ) {
			return;
		}

		if ( Database::POST_TYPE_UPLOADS !== $post->post_type ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$logtag = 'VIMEIFY-ADMIN-EDIT';

		$video_title      = isset( $_POST['video_name'] ) ? sanitize_text_field( wp_unslash( $_POST['video_name'] ) ) : '';
		$video_desc       = isset( $_POST['video_description'] ) ? sanitize_text_field( wp_unslash( $_POST['video_description'] ) ) : '';
		$video_uri        = isset( $_POST['video_uri'] ) ? sanitize_text_field( wp_unslash( $_POST['video_uri'] ) ) : '';
		$folder_uri       = isset( $_POST['folder_uri'] ) ? sanitize_text_field( wp_unslash( $_POST['folder_uri'] ) ) : '';
		$embed_preset_uri = isset( $_POST['embed_preset_uri'] ) ? sanitize_text_field( wp_unslash( $_POST['embed_preset_uri'] ) ) : '';
		$privacy_embed    = isset( $_POST['privacy_embed'] ) ? sanitize_text_field( wp_unslash( $_POST['privacy_embed'] ) ) : '';
		$view_privacy     = isset( $_POST['view_privacy'] ) ? sanitize_text_field( wp_unslash( $_POST['view_privacy'] ) ) : 'anybody';

		/**
		 * 1. Basic details & privacy
		 */
		try {
			$params  = array(
				'name'        => $video_title,
				'description' => $video_desc,
			);
			$privacy = $this->get_view_privacy( $view_privacy );
			if ( ! empty( $privacy ) ) {
				if ( 'default' !== $privacy ) {
					$params['privacy'] = array( 'view' => $privacy );
				}
			}

			$response = $this->plugin->system()->vimeo()->edit( $video_uri, $params );
			if ( isset( $response['status'] ) ) {
				if ( $response['status'] === 200 ) {
					global $wpdb;
					$wpdb->update( $wpdb->posts, [ 'post_title' => $video_title ], [ 'ID' => $post_id ], [ '%s' ] );
					$this->plugin->system()->logger()->log( sprintf( 'Video "%s" saved', $video_uri ), $logtag );
				} else {
					$this->plugin->system()->logger()->log( sprintf( 'Unable to save video %s', $video_uri ), $logtag );
				}
			} else {
				$this->plugin->system()->logger()->log( sprintf( 'Unable to save video %s', $video_uri ), $logtag );
			}
		} catch ( \Exception $e ) {
			$this->plugin->system()->logger()->log( sprintf( 'Unable to save video  %s. (%s)', $video_uri, $e->getMessage() ), $logtag );
		}

		/**
		 * 2. Update folder
		 */
		if ( $folder_uri === 'default' ) {
			$video      = $this->plugin->system()->vimeo()->get( $video_uri . '?fields=parent_folder' );
			$folder_uri = isset( $video['body']['parent_folder']['uri'] ) && ! empty( $video['body']['parent_folder']['uri'] ) ? $video['body']['parent_folder']['uri'] : null;
			if ( $folder_uri ) {
				try {
					$response = $this->plugin->system()->vimeo()->remove_video_folder( $video_uri, $folder_uri );
					$this->plugin->system()->logger()->log( sprintf( 'Folder changed to %s for %s', 'default', $video_uri ), $logtag );
					if ( isset( $response['status'] ) ) {
						if ( in_array( $response['status'], array( 200, 204 ) ) ) {
							$this->plugin->system()->logger()->log( sprintf( 'Folder changed to %s for %s', 'default', $video_uri ), $logtag );
						} else {
							$error = '';
							if ( isset( $response['body']['error'] ) ) {
								$error = sprintf( 'Error: %s', $response['body']['error'] );
							}
							$this->plugin->system()->logger()->log( sprintf( 'Unable to change folder to %s for %s. %s', 'default', $video_uri, $error ), $logtag );
						}
					} else {
						$this->plugin->system()->logger()->log( sprintf( 'Unable to set folder %s for %s. Error: Unreadable response', 'default', $video_uri ), $logtag );
					}
				} catch ( \Exception $e ) {
					$this->plugin->system()->logger()->log( sprintf( 'Unable to remove folder %s for %s. Error: %s', $folder_uri, $video_uri, $e->getMessage() ), $logtag );
				}
			} else {
				$this->plugin->system()->logger()->log( 'No folder removal is needed.', $logtag );
			}
		} else {
			try {
				$response = $this->plugin->system()->vimeo()->set_video_folder( $video_uri, $folder_uri );
				if ( isset( $response['status'] ) ) {
					if ( in_array( $response['status'], array( 200, 204 ) ) ) {
						$this->plugin->system()->logger()->log( sprintf( 'Folder changed to %s for %s', $folder_uri, $video_uri ), $logtag );
					} else {
						$error = '';
						if ( isset( $response['body']['error'] ) ) {
							$error = sprintf( 'Error: %s', $response['body']['error'] );
						}
						$this->plugin->system()->logger()->log( sprintf( 'Unable to change folder to %s for %s. %s', $folder_uri, $video_uri, $error ), $logtag );
					}
				} else {
					$this->plugin->system()->logger()->log( sprintf( 'Unable to set folder %s for %s. Error: Unreadable response', $folder_uri, $video_uri ), $logtag );
				}
			} catch ( \Exception $e ) {
				$this->plugin->system()->logger()->log( sprintf( 'Unable to set folder %s for %s. Error: %s', $folder_uri, $video_uri, $e->getMessage() ), $logtag );
			}
		}

		/**
		 * 3. Update embed presets
		 */
		if ( $embed_preset_uri === 'default' ) {
			$video            = $this->plugin->system()->vimeo()->get( $video_uri . '?fields=embed' );
			$embed_preset_uri = isset( $video['body']['embed']['uri'] ) && ! empty( $video['body']['embed']['uri'] ) ? $video['body']['embed']['uri'] : null;
			if ( $embed_preset_uri ) {
				try {
					$response = $this->plugin->system()->vimeo()->remove_video_embed_preset( $video_uri, $embed_preset_uri );
					if ( isset( $response['status'] ) ) {
						if ( in_array( $response['status'], array( 200, 204 ) ) ) {
							$this->plugin->system()->logger()->log( sprintf( 'Embed preset changed to %s for %s', 'default', $video_uri ), $logtag );
						} else {
							$error = '';
							if ( isset( $response['body']['error'] ) ) {
								$error = sprintf( 'Error: %s', $response['body']['error'] );
							}
							$this->plugin->system()->logger()->log( sprintf( 'Unable to change embed preset to %s for %s. %s', 'default', $video_uri, $error ), $logtag );
						}
					} else {
						$this->plugin->system()->logger()->log( sprintf( 'Unable to set embed preset %s for %s. Error: Unreadable response.', 'default', $video_uri ), $logtag );
					}
				} catch ( \Exception $e ) {
					$this->plugin->system()->logger()->log( sprintf( 'Unable to remove embed preset %s for %s. Error: %s', $embed_preset_uri, $video_uri, $e->getMessage() ), $logtag );
				}
			} else {
				$this->plugin->system()->logger()->log( 'No embed preset removal is needed.', $logtag );
			}
		} else { // Handle Preset
			try {
				$response = $this->plugin->system()->vimeo()->set_video_embed_preset( $video_uri, $embed_preset_uri );
				if ( isset( $response['status'] ) ) {
					if ( in_array( $response['status'], array( 200, 204 ) ) ) {
						$this->plugin->system()->logger()->log( sprintf( 'Embed preset changed to %s for %s', $embed_preset_uri, $video_uri ), $logtag );
					} else {
						$error = '';
						if ( isset( $response['body']['error'] ) ) {
							$error = sprintf( 'Error: %s', $response['body']['error'] );
						}
						$this->plugin->system()->logger()->log( sprintf( 'Unable to change embed preset to %s for %s. %s', $embed_preset_uri, $video_uri, $error ), $logtag );
					}
				} else {
					$this->plugin->system()->logger()->log( sprintf( 'Unable to set embed preset %s for %s. Error: Unraedable response', $embed_preset_uri, $video_uri ), $logtag );
				}
			} catch ( \Exception $e ) {
				$this->plugin->system()->logger()->log( sprintf( 'Unable to set embed preset %s for %s. Error: %s', $embed_preset_uri, $video_uri, $e->getMessage() ), $logtag );
			}
		}

		/**
		 * 4. Update embed preset
		 */
		try {
			$this->plugin->system()->vimeo()->set_embed_privacy( $video_uri, $privacy_embed );
		} catch ( \Exception $e ) {

		}

		/**
		 * Other save hooks...
		 */
		do_action( 'vimeify_video_edit_save', $this->plugin, $video_uri, $post_id );

	}


	/**
	 * Disable new post page for vimeo videos
	 * @return void
	 */
	public function disable_new_posts_page() {
		if ( self::is_create() ) {
			wp_redirect( esc_url( admin_url( 'admin.php?page=vimeify' ) ) );
			exit;
		}
	}

	/**
	 * Make the meta boxes
	 * @return void
	 */
	public function register_meta_boxes() {
		wp_nonce_field( 'vimeify_nonce_field', 'vimeify_nonce' );
		add_meta_box( 'vimeify-video-meta', __( 'Vimeo Settings', 'vimeify' ), [
			$this,
			'render_video_meta'
		], Database::POST_TYPE_UPLOADS, 'vimeify', 'high' );
	}

	/**
	 * Render the video meta data
	 * @return void
	 */
	public function render_video_meta( $post ) {
		$plugin                   = $this->plugin;
		$video_id                 = $post->ID;
		$vimeo_id                 = $plugin->system()->database()->get_vimeo_id( $video_id );
		$front_pages              = (int) $plugin->system()->settings()->plugin()->get( 'frontend.behavior.enable_single_pages' );
		$folders_management       = (int) $plugin->system()->settings()->plugin()->get( 'admin.video_management.enable_folders' );
		$embed_presets_management = (int) $plugin->system()->settings()->plugin()->get( 'admin.video_management.enable_embed_presets' );
		$embed_privacy_management = (int) $plugin->system()->settings()->plugin()->get( 'admin.video_management.enable_embed_privacy' );
		$vimeo_formatter          = new \Vimeify\Core\Utilities\Formatters\VimeoFormatter();
		include( $this->plugin->path() . '/views/admin/partials/post-type-edit.php' );
	}

	/**
	 * Obtian the privacy option based on input and plan support.
	 *
	 * @param $input
	 *
	 * @return mixed|string|null
	 *
	 * @since 1.7.0
	 */
	private function get_view_privacy( $input ) {

		$default    = $this->plugin->system()->settings()->profile()->get( 'Backend.Form.Other', 'view_privacy' );

		$privacy = $input === 'default' || empty( $input ) ? $default : $input;
		if ( $this->plugin->system()->vimeo()->supports_view_privacy_option( $privacy ) ) {
			return $privacy;
		} else {
			return 'default';
		}
	}

	/**
	 * Make the video meta boxes first
	 * @return void
	 */
	public function prioritize_meta_boxes() {
		global $post, $wp_meta_boxes;
		do_meta_boxes( get_current_screen(), 'vimeify', $post );
		unset( $wp_meta_boxes['post']['vimeify'] );
	}

	/**
	 * Chekc if page is create
	 * @return bool
	 */
	public static function is_create() {
		global $pagenow;

		return $pagenow === 'post-new.php' && isset( $_GET['post_type'] ) && $_GET['post_type'] === Database::POST_TYPE_UPLOADS;
	}

}