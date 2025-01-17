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

use Automattic\WooCommerce\Admin\API\Data;
use Vimeify\Core\Abstracts\BaseProvider;
use Vimeify\Core\Components\Database;

class Ui extends BaseProvider {

	const PAGE_VIMEO = 'vimeify';
	const PAGE_UPLOAD = 'vimeify-upload';
	const PAGE_SETTINGS = 'vimeify';

	public $screen_options;

	/**
	 * Registers specific piece of functionality
	 * @return void
	 */
	public function register() {

		add_action( 'in_admin_header', [ $this, 'do_admin_notices' ], 50 );
		add_action( 'admin_menu', [ $this, 'register_admin_menu' ], 5 );
		add_action( 'add_meta_boxes', [ $this, 'register_media_library_upload_metabox' ] );
		add_filter( 'manage_media_columns', [ $this, 'manage_media_columns' ], 15, 1 );
		add_action( 'manage_media_custom_column', [ $this, 'manage_media_custom_column' ], 15, 2 );
		add_filter( 'plugin_action_links_' . $this->plugin->basename(), [ $this, 'plugin_action_links' ], 100, 1 );
		add_filter( 'parent_file', [ $this, 'parent_file' ] );
		add_filter( 'add_menu_classes', [ $this, 'menu_classes' ] );

		// Prevent deletion.
		add_action( 'wp_trash_post', [ $this, 'prevent_deletion_if_used' ], 10, 1 );
		add_action( 'before_delete_post', [ $this, 'prevent_deletion_if_used' ], 10, 1 );
		add_action( 'display_post_states', [ $this, 'display_post_states' ], 10, 2 );

		$this->screen_options = new \Vimeify\Core\Utilities\ScreenOptions(
			[
				self::PAGE_VIMEO => [
					'description'              => __( 'Show Description', 'vimeify' ),
					'link_insteadof_shortcode' => __( 'Show Link instead of shortcode', 'vimeify' ),
				]
			]
		);

	}

	/**
	 * Make the $submenu_file to be equal to the
	 * Vimeify submenu links when visited an edit page.
	 *
	 * This will activate the submenu link if admin
	 * uses edit page of that submenu item. Eg. Upload Profiles.
	 *
	 * @param $parent_file
	 *
	 * @return mixed
	 */
	public function parent_file( $parent_file ) {
		global $submenu_file, $pagenow;

		if ( isset( $_GET['taxonomy'] ) && $_GET['taxonomy'] == Database::TAX_CATEGORY ) {
			$submenu_file = 'edit-tags.php?taxonomy=' . Database::TAX_CATEGORY . '&post_type=' . Database::POST_TYPE_UPLOADS;
		} else if ( isset( $_GET['action'] ) && $_GET['action'] === 'edit' ) {
			global $post;
			if ( ! empty( $post->post_type ) && Database::POST_TYPE_UPLOADS === $post->post_type ) {
				$submenu_file = 'edit.php?post_type=' . Database::POST_TYPE_UPLOADS;
			}
		}

		return $parent_file;
	}

	/**
	 * Add the needed menu classes
	 *
	 * @param $menu
	 *
	 * @return array|mixed
	 */
	public function menu_classes( $menu ) {
		if ( self::is_upload_profiles() || self::is_categories() ) {
			foreach ( $menu as $i => $item ) {
				if ( 'vimeify' === $item[2] ) {
					$menu[ $i ][4] = add_cssclass( 'wp-has-current-submenu wp-menu-open', $item[4] );
				}
			}
		}

		return $menu;
	}

	/**
	 * Check if page is edit
	 * @return bool
	 */
	public static function is_upload_profiles() {
		global $pagenow;

		return is_admin() && $pagenow === 'post.php' && isset( $_GET['post'] ) && get_post_type( intval( $_GET['post'] ) ) === Database::POST_TYPE_UPLOAD_PROFILES;
	}

	/**
	 * Check if page is edit
	 * @return bool
	 */
	public static function is_categories() {
		global $pagenow;

		return is_admin() && in_array( $pagenow, [
				'edit-tags.php',
				'term.php'
			] ) && isset( $_GET['taxonomy'] ) && $_GET['taxonomy'] === Database::TAX_CATEGORY;
	}

	/**
	 * Register the admin menus
	 *
	 * @since 1.0.0
	 */
	public function register_admin_menu() {

		add_menu_page(
			__( 'Vimeify - Vimeo Uploads', 'vimeify' ),
			__( 'Vimeify', 'vimeify' ),
			'edit_others_posts',
			'vimeify',
			array( $this, 'render_main_page' ),
			$this->plugin->icon( '20' ),
			5
		);

		add_submenu_page( 'vimeify',
			__( 'Vimeify - All Videos', 'vimeify' ),
			__( 'All Videos', 'vimeify' ),
			'edit_others_posts',
			'vimeify'
		);

		add_submenu_page( 'vimeify',
			__( 'Vimeify - All Videos', 'vimeify' ),
			__( 'Upload New', 'vimeify' ),
			'upload_files',
			'vimeify-upload',
			array( $this, 'render_upload_page' ),
		);

		add_submenu_page(
			'vimeify',
			__( 'Vimeify - Categories', 'vimeify' ),
			__( 'Categories' ),
			'edit_others_posts',
			'edit-tags.php?taxonomy=vimeify-category&post_type=' . Database::POST_TYPE_UPLOADS
		);

		add_submenu_page(
			'vimeify',
			__( 'Vimeify - Upload Profiles', 'vimeify' ),
			__( 'Upload Profiles', 'vimeify' ),
			'upload_files',
			'edit.php?post_type=vimeify-uprofile'
		);

	}

	/**
	 * Renders the vimeo pages
	 */
	public function render_main_page() {
		$this->plugin->system()->views()->render_view( 'admin/partials/library', [
			'plugin' => $this->plugin,
		] );
	}

	/**
	 * Renders the vimeo pages
	 */
	public function render_upload_page() {
		$this->plugin->system()->views()->render_view( 'admin/partials/library-upload', [
			'plugin' => $this->plugin,
		] );
	}

	/**
	 * Unset third party notices.
	 */
	public function do_admin_notices() {
		if ( $this->is_any_page() ) {
			\remove_all_actions( 'admin_notices' );
		}
		do_action( 'vimeify_admin_notices' );
		$this->instructions();
	}

	/**
	 * Registers the Media Library Integration Button
	 */
	public function register_media_library_upload_metabox() {
		if ( isset( $_GET['post'] ) && 'attachment' === get_post_type( (int) $_GET['post'] ) ) {
			add_meta_box(
				'vimeify_info_metabox_' . (int) $_GET['post'],
				__( 'WP Vimeo', 'vimeify' ),
				array( $this, 'render_media_library_upload_metabox' ),
				null,
				'side'
			);
		}
	}

	/**
	 * Renders the Media Library Integration Button
	 */
	public function render_media_library_upload_metabox() {
		$attachment_id = isset( $_GET['post'] ) ? intval( $_GET['post'] ) : null;
		if ( ! is_null( $attachment_id ) && 'attachment' === get_post_type( $attachment_id ) ) {
			echo wp_kses(
				sprintf( '<div class="vimeify-mlmb-%s">', esc_attr( $attachment_id ) ) .
				$this->plugin->system()->views()->get_view( 'admin/partials/media-buttons', [
					'id'     => $attachment_id,
					'plugin' => $this->plugin,
				] ) . '</div>',
				wp_kses_allowed_html( 'vimeify' )
			);
		}
	}

	/**
	 * Add WP Vimeo in the Media Library table
	 *
	 * @param $columns
	 *
	 * @return mixed
	 */
	public function manage_media_columns( $columns ) {
		if ( $this->plugin->system()->vimeo()->is_connected ) {
			$columns['vimeify_info'] = __( 'WP Vimeo', 'vimeify' );
		}

		return $columns;
	}

	/**
	 * Render WP Vimeo in the Media Library table
	 *
	 * @param $column_name
	 * @param $attachment_id
	 */
	public function manage_media_custom_column( $column_name, $attachment_id ) {
		if ( $this->plugin->system()->vimeo()->is_connected ) {
			switch ( $column_name ) {
				case 'vimeify_info':
					echo wp_kses(
						sprintf( '<div id="vimeify-mlmb-%s">', esc_attr( $attachment_id ) ) .
						$this->plugin->system()->views()->get_view( 'admin/partials/media-buttons', [
							'id'     => $attachment_id,
							'plugin' => $this->plugin,
						] ) . '</div>',
						wp_kses_allowed_html( 'vimeify' )
					);
					break;
			}
		}
	}

	/**
	 * Add instructions view
	 */
	public function instructions() {
		if ( ! current_user_can( 'install_plugins' ) ) {
			return;
		}

		// Verify dismiss
		// Render if not dismissed.
		if ( ! (int) get_option( 'vimeify_welcome' ) ) {
			echo wp_kses(
				$this->plugin->system()->views()->get_view( 'admin/partials/instructions', [ 'plugin' => $this->plugin ] ),
				wp_kses_allowed_html( 'vimeify' )
			);
		}
	}

	/**
	 * Add a link to the settings page on the plugins.php page.
	 *
	 * @param array $links List of existing plugin action links.
	 *
	 * @return array         List of modified plugin action links.
	 *
	 */
	public function plugin_action_links( $links ) {
		$links = array_merge( array(
			'<a href="' . esc_url( admin_url( '/admin.php?page=vimeify-settings' ) ) . '">' . __( 'Settings',
				'vimeify' ) . '</a>'
		), $links );

		return $links;
	}

	/**
	 * Is any page?
	 * @return bool
	 */
	public function is_any_page() {
		return is_admin() && isset( $_GET['page'] ) && in_array( $_GET['page'], array(
				self::PAGE_VIMEO,
				self::PAGE_SETTINGS
			) );
	}

	/**
	 * Prevent deletion if used
	 *
	 * @param $post_id
	 *
	 * @return void
	 */
	public function prevent_deletion_if_used( $post_id ) {
		$settings = $this->plugin->system()->settings();
		if ( Database::POST_TYPE_UPLOAD_PROFILES === get_post_type( $post_id ) ) {
			if ( $settings->profile()->in_use( $post_id ) ) {
				wp_die( esc_html__( 'Sorry, you can\'t delete Upload profile that is already in use.', 'vimeify' ) );
			}
		}
	}

	/**
	 * Display the post states
	 *
	 * @param array $post_states
	 * @param \WP_Post $post
	 *
	 * @return array
	 */
	public function display_post_states( $post_states, $post ) {
		if ( Database::POST_TYPE_UPLOAD_PROFILES === $post->post_type ) {
			$settings = $this->plugin->system()->settings();
			if ( $settings->profile()->in_use( $post->ID ) ) {
				$post_states['in_use'] = 'In use';
			}
		}

		return $post_states;
	}

	/**
	 * Is the list page?
	 * @return bool
	 */
	public function is_list_page() {
		return $this->is_any_page() && ! isset( $_GET['action'] );
	}

	/**
	 * Is the edit page?
	 * @return bool
	 */
	public function is_edit_page() {
		return $this->is_any_page() && isset( $_GET['action'] ) && 'edit' === $_GET['action'];
	}
}