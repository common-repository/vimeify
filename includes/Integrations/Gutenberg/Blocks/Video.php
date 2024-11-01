<?php

namespace Vimeify\Core\Integrations\Gutenberg\Blocks;

use Vimeify\Core\Abstracts\BaseBlock;
use Vimeify\Core\Abstracts\Interfaces\CacheInterface;
use Vimeify\Core\Frontend\Views\Video as VideoView;
use Vimeify\Core\Utilities\FileSystem;
use Vimeify\Core\Utilities\Formatters\VimeoFormatter;

class Video extends BaseBlock {

	/**
	 * Registers block editor assets
	 * @return void
	 */
	public function register_block() {



		$block_path = $this->plugin->path() . 'blocks/dist/video/';
		if ( ! FileSystem::file_exists( $block_path . 'index.asset.php' ) ) {
			return;
		}
		$asset_file = include $block_path . 'index.asset.php';
		wp_register_script(
			'vimeify-video-block',
			$this->plugin->url() . 'blocks/dist/video/index.js',
			[ 'vimeify-uploader' ] + $asset_file['dependencies'],
			$asset_file['version']
		);

		register_block_type( $block_path, array(
			'api_version'     => 3,
			'editor_script'   => 'vimeify-video-block',
			'render_callback' => [ $this, 'render_block' ],
		) );

	}

	/**
	 * Registers block editor assets
	 * @return void
	 */
	public function register_block_editor_assets() {
		wp_register_style(
			'vimeify-video-block',
			$this->plugin->url() . 'blocks/dist/video/index.css',
			array(),
			filemtime( $this->plugin->path() . 'blocks/dist/video/index.css' )
		);
	}

	/**
	 * Dynamic render for the upload block
	 *
	 * @param $block_attributes
	 * @param $content
	 *
	 * @return false|string
	 */
	public function render_block( $block_attributes, $content ) {
		if ( ! isset( $block_attributes['currentValue'] ) ) {
			return sprintf( '<p>%s</p>', __( 'No Vimeo.com video selected. Please edit this post and find the corresponding Vimeify Upload block to set video.', 'vimeify' ) );
		}

		$frm      = new VimeoFormatter();
		$uri      = $block_attributes['currentValue'];
		$video_id = $frm->uri_to_id( $uri );
		$post_id  = $this->plugin->system()->database()->get_post_id( $video_id );

		$view = apply_filters( 'vimeify_frontend_view_video', null, $this->plugin );
		if ( is_null( $view ) ) {
			$view = new VideoView( $this->plugin );
		}
		$view->enqueue();

		if ( $post_id ) {
			$view_args = [ 'post_id' => $post_id ];
		} else {
			$view_args = [ 'id' => $video_id ];
		}

		return wp_kses( $view->output( $view_args ), wp_kses_allowed_html( 'vimeify' ) );
	}
}