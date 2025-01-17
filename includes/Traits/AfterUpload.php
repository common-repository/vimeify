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

namespace Vimeify\Core\Traits;

use Vimeify\Core\Components\Database;
use Vimeify\Core\Plugin;

/**
 * After upload trait
 * @property Plugin $plugin
 */
trait AfterUpload {

	/**
	 * Create local video
	 *
	 * @param $data
	 * @param $logtag
	 *
	 * @since 1.7.0
	 */
	protected function create_local_video( $data, $logtag ) {
		$id          = isset( $data['vimeo_id'] ) ? $data['vimeo_id'] : 0;
		$title       = isset( $data['vimeo_title'] ) ? $data['vimeo_title'] : '';
		$description = isset( $data['vimeo_description'] ) ? $data['vimeo_description'] : '';
		$post_id     = $this->plugin->system()->database()->create_local_video( $title, $description, $id, 'frontend' );
		$source      = isset( $data['source'] ) ? $data['source'] : array();

		if ( ! is_wp_error( $post_id ) ) {

			$software   = isset( $data['source']['software'] ) ? $data['source']['software'] : null;
			$profile_id = (int) $this->plugin->system()->settings()->profile()->find_profile_id( $software );

			/**
			 * Update meta
			 */
			update_post_meta( $post_id, 'vimeify_source', $source );
			if ( isset( $data['vimeo_size'] ) && $data['vimeo_size'] ) {
				update_post_meta( $post_id, 'vimeify_size', (int) $data['vimeo_size'] );
			}

			/**
			 * Set link to the Video. Note: For some videos Vimeo creates non-standard links.
			 * e.g View privacy: Those with link only.
			 */
			if ( ! empty( $id ) ) {
				try {
					$response = $this->plugin->system()->vimeo()->get_video_by_id( $id, array( 'link' ) );
					if ( ! empty( $response['body']['link'] ) ) {
						update_post_meta( $post_id, 'vimeify_link', $response['body']['link'] );
					}
				} catch ( \Exception $e ) {
				}
			}

			/**
			 * Set media library attachment source
			 */
			if ( isset( $data['source']['media_id'] ) ) {
				update_post_meta( $data['source']['media_id'], 'vimeify', array(
					'vimeo_id' => $id,
					'local_id' => $post_id,
				) );
			}

			if ( ! empty( $data['vimeo_meta'] ) && is_array( $data['vimeo_meta'] ) ) {
				foreach ( $data['vimeo_meta'] as $k => $v ) {
					update_post_meta( $post_id, $k, $v );
				}
			}

			/**
			 * Set category
			 */
			$category = (int) $this->plugin->system()->settings()->profile()->get( $profile_id, 'category' );
			$this->set_category( $post_id, $category, $logtag );

			do_action( 'vimeify_local_video_created', $post_id, $data, $profile_id, $logtag );

			$this->plugin->system()->logger()->log( sprintf( '-- Local video #%s created', $post_id ), $logtag );
		} else {
			$this->plugin->system()->logger()->log( sprintf( '-- Failed to create local video (%s)', $post_id->get_error_message() ), $logtag );
		}
	}

	/**
	 * Set embed preset.
	 *
	 * @param $uri
	 * @param $preset_uri
	 * @param $logtag
	 *
	 * @since 1.7.0
	 */
	protected function set_embed_preset( $uri, $preset_uri, $logtag ) {

		if ( empty( $preset_uri ) ) {
			$this->plugin->system()->logger()->log( '-- Embed preset not configured, skipping.', $logtag );

			return;
		}

		if ( 'default' === $preset_uri ) {
			$this->plugin->system()->logger()->log( '-- Embed preset skipped. Default is no embed preset.', $logtag );

			return;
		}

		try {
			$this->plugin->system()->vimeo()->set_video_embed_preset( $uri, $preset_uri );
			$this->plugin->system()->logger()->log( sprintf( '-- Embed preset %s set', $preset_uri ), $logtag );
		} catch ( \Exception $e ) {
			$this->plugin->system()->logger()->log( sprintf( '-- Failed to set embed preset (%s)', $e->getMessage() ), $logtag );
		}
	}

	/**
	 * Set folder.
	 *
	 * @param $uri
	 * @param $folder_uri
	 * @param $logtag
	 *
	 * @since 1.7.0
	 */
	protected function set_folder( $uri, $folder_uri, $logtag ) {

		if ( empty( $folder_uri ) ) {
			$this->plugin->system()->logger()->log( '-- Folder not configured, skipping.', $logtag );

			return;
		}

		if ( 'default' === $folder_uri ) {
			$this->plugin->system()->logger()->log( '-- Folder skipped. Default is no folder.', $logtag );

			return;
		}

		try {
			$this->plugin->system()->vimeo()->set_video_folder( $uri, $folder_uri );
			$this->plugin->system()->logger()->log( sprintf( '-- Folder %s set', $folder_uri ), $logtag );
		} catch ( \Exception $e ) {
			$this->plugin->system()->logger()->log( sprintf( '-- Failed to set folder (%s)', $e->getMessage() ), $logtag );
		}
	}

	/**
	 * Set embed privacy.
	 *
	 * @param $uri
	 * @param $whitelisted_domains
	 * @param $logtag
	 *
	 * @since 1.7.0
	 */
	protected function set_embed_privacy( $uri, $whitelisted_domains, $logtag ) {
		try {
			if ( is_array( $whitelisted_domains ) && count( $whitelisted_domains ) > 0 ) {
				$this->plugin->system()->vimeo()->set_embed_privacy( $uri, 'whitelist' );
				foreach ( $whitelisted_domains as $domain ) {
					$this->plugin->system()->vimeo()->whitelist_domain_add( $uri, $domain );
					$this->plugin->system()->logger()->log( sprintf( '-- Embed domain %s whitelisted for %s', $domain, $uri ), $logtag );
				}
			}
		} catch ( \Exception $e ) {
			$this->plugin->system()->logger()->log( sprintf( '-- Failed to set embed privacy for %s. Error: (%s)', $uri, $e->getMessage() ), $logtag );
		}
	}

	/**
	 * Set view privacy
	 *
	 * @param $uri
	 * @param $privacy
	 * @param $logtag
	 *
	 * @since 1.7.0
	 */
	protected function set_view_privacy( $uri, $privacy, $logtag ) {

		if ( ! in_array( $privacy, array( 'default', 'anybody' ) ) ) {
			$params['privacy'] = array( 'view' => $privacy );
			if ( $this->plugin->system()->vimeo()->can_edit() ) {
				try {
					$this->plugin->system()->vimeo()->edit( $uri, $params );
					$this->plugin->system()->logger()->log( sprintf( '-- View privacy set to %s for %s', $privacy, $uri ), $logtag );
				} catch ( \Exception $e ) {
					$this->plugin->system()->logger()->log( sprintf( '-- Failed to set view privacy %s for %s. Error: (%s)', $privacy, $uri, $e->getMessage() ), $logtag );
				}
			} else {
				$this->plugin->system()->logger()->log( sprintf( '-- Failed to set view privacy %s for %s. Unsupported on %s plan', $privacy, $uri, $this->plugin->system()->vimeo()->get_plan( true ) ), $logtag );
			}
		}
	}

	/**
	 * Set view privacy
	 *
	 * @param $uri
	 * @param $content_rating
	 * @param $logtag
	 *
	 * @since 2.0.0
	 */
	protected function set_content_rating( $uri, $content_rating, $logtag ) {

		$classes = array_keys( $this->plugin->system()->vimeo()->get_available_content_ratings() );

		if ( in_array( $content_rating, $classes ) ) {
			if ( $this->plugin->system()->vimeo()->can_edit() ) {
				try {
					if ( null === $this->plugin->system()->vimeo()->set_content_rating( $uri, $content_rating ) ) {
						$this->plugin->system()->logger()->log( sprintf( '-- Unable to set content rating: %s', $content_rating ), $logtag );
					} else {
						$this->plugin->system()->logger()->log( sprintf( '-- Content rating class set to %s for %s', $content_rating, $uri ), $logtag );
					}
				} catch ( \Exception $e ) {
					$this->plugin->system()->logger()->log( sprintf( '-- Failed to set content rating class %s for %s. Error: (%s)', $content_rating, $uri, $e->getMessage() ), $logtag );
				}
			} else {
				$this->plugin->system()->logger()->log( sprintf( '-- Failed to set content rating class %s for %s. Unsupported on %s plan', $content_rating, $uri, $this->plugin->system()->vimeo()->get_plan( true ) ), $logtag );
			}
		} else {
			$this->plugin->system()->logger()->log( sprintf( '-- Content rating class %s is not valid. Skipping.', $content_rating ), $logtag );
		}
	}


	/**
	 * Set category
	 *
	 * @param $post_id
	 * @param $category
	 * @param $logtag
	 *
	 * @return void
	 */
	protected function set_category( $post_id, $category, $logtag ) {
		if ( ! empty( $category ) && is_numeric( $category ) && (int) $category > 0 ) {
			$category = get_term( $category, Database::TAX_CATEGORY );
			if ( ! empty( $category ) ) {
				wp_set_object_terms( $post_id, (int) $category->term_id, Database::TAX_CATEGORY, true );
				$this->plugin->system()->logger()->log( sprintf( '-- Local video category set: "%s"', $category->name ), $logtag );
			} else {
				$this->plugin->system()->logger()->log( '-- Local video category not set. Category not found.', $logtag );
			}
		} else {
			$this->plugin->system()->logger()->log( '-- Local video category not set. Category ID not set or empty.', $logtag );
		}
	}

}