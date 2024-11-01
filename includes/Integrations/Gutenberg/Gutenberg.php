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

namespace Vimeify\Core\Integrations\Gutenberg;

use Vimeify\Core\Abstracts\BaseBlock;
use Vimeify\Core\Abstracts\BaseProvider;
use Vimeify\Core\Abstracts\Interfaces\CacheInterface;
use Vimeify\Core\Abstracts\Interfaces\IntegrationInterface;
use Vimeify\Core\Integrations\Gutenberg\Blocks\Video;
use Vimeify\Core\Integrations\Gutenberg\Blocks\VideosTable;

class Gutenberg extends BaseProvider implements IntegrationInterface {

	/**
	 * The list of available blocks
	 * @var BaseBlock[]
	 */
	protected $blocks = [];

	/**
	 * Register the blocks
	 * @return void
	 */
	public function register_blocks() {
		foreach ( $this->blocks as $block ) {
			$block->register_block();
		}
	}

	/**
	 * Register the block editor assets
	 * @return void
	 */
	public function register_block_editor_assets() {
		foreach ( $this->blocks as $block ) {
			$block->register_block_editor_assets();
		}
	}

	/**
	 * Check if the integration can be activated.
	 * @return bool
	 */
	public function can_activate() {
		return $this->is_gutenberg_enabled();
	}

	/**
	 * Activates the integration
	 * @return bool
	 */
	public function activate() {
		$blocks = [
			new Video( $this->plugin ),
			new VideosTable( $this->plugin ),
		];

		$this->blocks = apply_filters( 'vimeify_registered_blocks', $blocks, $this->plugin );

		add_action( 'init', [ $this, 'register_blocks' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'register_block_editor_assets' ] );
	}

	/**
	 * Registers specific piece of functionality
	 * @return void
	 */
	public function register() {
		$this->activate();
	}

	/**
	 * Check if Gutenberg is enabled.
	 * Must be used not earlier than plugins_loaded action fired.
	 *
	 * @return bool
	 */
	public function is_gutenberg_enabled() {

		$gutenberg    = false;
		$block_editor = false;

		if ( has_filter( 'replace_editor', 'gutenberg_init' ) ) {
			// Gutenberg is installed and activated.
			$gutenberg = true;
		}

		if ( version_compare( $GLOBALS['wp_version'], '5.0-beta', '>' ) ) {
			// Block editor.
			$block_editor = true;
		}

		if ( ! $gutenberg && ! $block_editor ) {
			return false;
		}

		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		if ( ! is_plugin_active( 'classic-editor/classic-editor.php' ) ) {
			return true;
		}

		$use_block_editor = ( get_option( 'classic-editor-replace' ) === 'no-replace' );

		return $use_block_editor;
	}
}