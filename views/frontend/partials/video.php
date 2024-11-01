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

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/* @var string $vimeo_id */
/* @var string $embed_url */
/* @var string $vimeo_uri */
/* @var string $thumbnail */

if ( empty( $embed_url ) ) {
	if ( isset( $vimeo_id ) ) {
		$embed_url = sprintf( 'https://player.vimeo.com/video/%s', $vimeo_id );
	} else if ( isset( $vimeo_uri ) ) {
		$embed_url = sprintf( 'https://player.vimeo.com/%s', str_replace( '/videos/', 'video/', $vimeo_uri ) );
	} else {
		$embed_url = '';
	}
}

$player_params = apply_filters( 'vimeify_embed_player_args', [
	'loop'        => false,
	'byline'      => false,
	'portrait'    => false,
	'title'       => false,
	'speed'       => true,
	'transparent' => 0,
	'gesture'     => 'media',
] );

if ( ! empty( $player_params ) ) {
	$embed_url = add_query_arg( $player_params, $embed_url );
}
?>

<div class="vimeify-embed-modern">
    <div allowfullscreen="" allow="autoplay" data-iframe-src="<?php echo esc_url( $embed_url ); ?>" class="vimeify-embed-modern-video-preview-image" style="background-image: url(<?php echo esc_url( $thumbnail ); ?>);"></div>
    <div class="vimeify-embed-modern-video-overlay"></div>
    <span class="vimeify-embed-modern-video-overlay-icon vimeify-play"></span>
</div>